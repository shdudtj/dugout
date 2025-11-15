# -*- coding: utf-8 -*-
"""
KBO 선수 정보 크롤링 모듈
"""

from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import Select
from webdriver_manager.chrome import ChromeDriverManager
from bs4 import BeautifulSoup
import requests
import time
import json
from urllib.parse import urljoin
import pymysql

# 팀 코드 매핑
TEAMS = {
    'LG': 'LG',
    '키움': 'WO',
    '롯데': 'LT',
    '삼성': 'SS',
    'KT': 'KT',
    'SSG': 'SK',
    'KIA': 'HT',
    'NC': 'NC',
    '두산': 'OB',
    '한화': 'HH'
}

# 기본 URL
BASE_URL = 'https://www.koreabaseball.com'
SEARCH_URL = 'https://www.koreabaseball.com/Player/Search.aspx'

# 헤더 설정
HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'Accept-Language': 'ko-KR,ko;q=0.9,en-US;q=0.8,en;q=0.7'
}


def get_player_list_with_selenium(driver, team_code, max_retries=3):
    """
    Selenium을 사용하여 특정 팀의 선수 목록 가져오기 (모든 페이지)
    
    Args:
        driver: Selenium WebDriver 객체
        team_code: 팀 코드
        max_retries: 최대 재시도 횟수
    
    Returns:
        list: 선수 정보 리스트
    """
    all_players = []
    
    for attempt in range(max_retries):
        try:
            print(f"  팀 선택 중... (시도 {attempt + 1}/{max_retries})")
            
            # 팀 선택 드롭다운 찾기
            team_select = WebDriverWait(driver, 10).until(
                EC.presence_of_element_located((By.ID, "cphContents_cphContents_cphContents_ddlTeam"))
            )
            
            # Select 객체로 팀 선택
            select = Select(team_select)
            select.select_by_value(team_code)
            
            # 페이지 로딩 대기
            time.sleep(3)
            
            # 페이지네이션 처리
            current_page = 1
            while True:
                print(f"    페이지 {current_page} 수집 중...")
                
                # 현재 페이지의 HTML 파싱
                soup = BeautifulSoup(driver.page_source, 'html.parser')
                
                # class='tEx' 테이블 찾기
                player_table = soup.find('table', class_='tEx')
                
                page_players = []
                if player_table:
                    tbody = player_table.find('tbody')
                    if tbody:
                        rows = tbody.find_all('tr')
                        for row in rows:
                            cells = row.find_all('td')
                            if len(cells) >= 2:
                                # 두 번째 셀에 선수 이름과 링크
                                name_cell = cells[1]
                                link = name_cell.find('a')
                                
                                if link and link.get('href'):
                                    player_name = link.get_text(strip=True)
                                    player_url = urljoin(BASE_URL, link['href'])
                                    
                                    page_players.append({
                                        'name': player_name,
                                        'url': player_url
                                    })
                
                print(f"      {len(page_players)}명 발견")
                all_players.extend(page_players)
                
                # 다음 페이지 버튼 찾기
                next_page = current_page + 1
                next_button_id = f"cphContents_cphContents_cphContents_ucPager_btnNo{next_page}"
                
                try:
                    # 다음 페이지 버튼이 있는지 확인
                    next_button = driver.find_element(By.ID, next_button_id)
                    
                    # 버튼이 비활성화되어 있는지 확인 (class에 'on'이 없으면 활성화)
                    if 'on' not in next_button.get_attribute('class'):
                        # 다음 페이지로 이동
                        next_button.click()
                        time.sleep(3)  # 페이지 로딩 대기
                        current_page += 1
                    else:
                        # 현재 페이지가 마지막 페이지
                        break
                except:
                    # 다음 페이지 버튼이 없으면 종료
                    print(f"    마지막 페이지 도달")
                    break
            
            print(f"  전체 {len(all_players)}명 발견")
            return all_players
            
        except Exception as e:
            print(f"  오류 발생 (시도 {attempt + 1}/{max_retries}): {e}")
            if attempt < max_retries - 1:
                time.sleep(2)
            else:
                return []
    
    return []


def get_player_detail(session, player_url, max_retries=3):
    """
    선수 상세 정보 크롤링
    
    Args:
        session: requests.Session 객체
        player_url: 선수 상세 페이지 URL
        max_retries: 최대 재시도 횟수
    
    Returns:
        dict: 선수 상세 정보
    """
    for attempt in range(max_retries):
        try:
            response = session.get(player_url, headers=HEADERS, timeout=10)
            response.raise_for_status()
            response.encoding = 'utf-8'
            
            soup = BeautifulSoup(response.text, 'html.parser')
            
            # 선수 정보 초기화
            player_info = {
                'number': '',
                'name': '',
                'position': '',
                'birth_date': '',
                'height': '',
                'weight': '',
                'debut_year': '',
                'salary': '',
                'school': ''
            }
            
            # player_basic div 내의 li 태그들에서 정보 추출
            player_basic = soup.find('div', class_='player_basic')
            if player_basic:
                lis = player_basic.find_all('li')
                
                for li in lis:
                    strong = li.find('strong')
                    span = li.find('span')
                    
                    if strong and span:
                        field_name = strong.get_text(strip=True)
                        value = span.get_text(strip=True)
                        
                        # 필드명에 따라 매핑
                        if '선수명' in field_name or '이름' in field_name:
                            player_info['name'] = value
                        elif '등번호' in field_name:
                            # "116" 또는 "No. 116" 형태에서 숫자만 추출
                            player_info['number'] = value.replace('No.', '').strip()
                        elif '생년월일' in field_name:
                            player_info['birth_date'] = value
                        elif '포지션' in field_name:
                            player_info['position'] = value
                        elif '신장/체중' in field_name or '신장' in field_name:
                            # '180cm/90kg' 또는 '180cm / 90kg' 형태
                            if '/' in value:
                                parts = value.split('/')
                                if len(parts) >= 2:
                                    player_info['height'] = parts[0].strip()
                                    player_info['weight'] = parts[1].strip()
                            else:
                                player_info['height'] = value
                        elif '입단년도' in field_name or '입단' in field_name:
                            player_info['debut_year'] = value
                        elif '연봉' in field_name:
                            player_info['salary'] = value
                        elif '경력' in field_name or '출신' in field_name:
                            player_info['school'] = value
            
            return player_info
            
        except requests.exceptions.RequestException as e:
            if attempt < max_retries - 1:
                time.sleep(1)
            else:
                return None
        except Exception as e:
            return None
    
    return None


def crawl_all_players():
    """
    Selenium을 사용하여 전체 팀의 선수 정보 크롤링
    
    Returns:
        list: 전체 선수 정보 리스트
    """
    all_players = []
    
    # Chrome 옵션 설정
    options = webdriver.ChromeOptions()
    options.add_argument('--headless')  # 헤드리스 모드 (백그라운드 실행)
    options.add_argument('--no-sandbox')
    options.add_argument('--disable-dev-shm-usage')
    options.add_argument('--disable-gpu')
    options.add_argument('user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
    
    print("=" * 60)
    print("KBO 선수 정보 크롤링 시작 (Selenium 사용)")
    print("=" * 60)
    
    try:
        # WebDriver 시작
        print("\n브라우저 초기화 중...")
        service = Service(ChromeDriverManager().install())
        driver = webdriver.Chrome(service=service, options=options)
        driver.get(SEARCH_URL)
        time.sleep(2)
        print("브라우저 초기화 완료")
        
        for team_name, team_code in TEAMS.items():
            print(f"\n[{team_name}] 팀 크롤링 시작 (코드: {team_code})")
            
            # 선수 목록 가져오기
            player_list = get_player_list_with_selenium(driver, team_code)
            
            if not player_list:
                print(f"  {team_name} 팀의 선수 목록을 가져오지 못했습니다.")
                continue
            
            print(f"  총 {len(player_list)}명의 선수 발견")
            
            # 선수 목록만 저장 (상세 정보는 나중에 수집)
            for player in player_list:
                all_players.append({
                    'team': team_name,
                    'team_code': team_code,
                    'name': player['name'],
                    'url': player['url']
                })
            
            print(f"[{team_name}] 팀 크롤링 완료")
            time.sleep(1)
        
        print("\n=" * 60)
        print(f"전체 크롤링 완료: 총 {len(all_players)}명의 선수 정보 수집")
        print("=" * 60)
        
    except Exception as e:
        print(f"\n크롤링 중 오류 발생: {e}")
    finally:
        # 브라우저 종료
        if 'driver' in locals():
            driver.quit()
            print("\n브라우저 종료")
    
    return all_players


def collect_player_details(players_data):
    """
    수집된 선수 URL 목록에서 상세 정보를 추가로 수집
    
    Args:
        players_data: URL이 포함된 선수 목록
    
    Returns:
        list: 상세 정보가 추가된 선수 목록
    """
    print("\n" + "=" * 60)
    print("선수 상세 정보 수집 시작")
    print(f"총 {len(players_data)}명의 선수 정보 수집")
    print("=" * 60)
    
    session = requests.Session()
    updated_players = []
    
    for idx, player in enumerate(players_data, 1):
        print(f"\n[{idx}/{len(players_data)}] {player['name']} ({player['team']}) 정보 수집 중...")
        
        # 상세 정보 수집
        detail = get_player_detail(session, player['url'])
        
        if detail:
            # 기존 정보와 상세 정보 병합
            updated_player = {
                'team': player['team'],
                'team_code': player['team_code'],
                'name': detail.get('name') or player['name'],
                'number': detail.get('number', ''),
                'position': detail.get('position', ''),
                'birth_date': detail.get('birth_date', ''),
                'height': detail.get('height', ''),
                'weight': detail.get('weight', ''),
                'debut_year': detail.get('debut_year', ''),
                'salary': detail.get('salary', ''),
                'school': detail.get('school', ''),
                'url': player['url']
            }
            updated_players.append(updated_player)
            print(f"  수집 완료: No.{updated_player['number']} {updated_player['name']} ({updated_player['position']})")
        else:
            # 상세 정보 수집 실패 시 기본 정보만 저장
            updated_players.append(player)
            print(f"  상세 정보 수집 실패 (기본 정보만 저장)")
        
        # 서버 부하 방지
        time.sleep(0.5)
    
    print("\n" + "=" * 60)
    print(f"상세 정보 수집 완료: {len(updated_players)}명")
    print("=" * 60)
    
    return updated_players


def save_to_json(data, filename='players_data.json'):
    """
    수집한 데이터를 JSON 파일로 저장
    
    Args:
        data: 저장할 데이터
        filename: 저장할 파일명
    """
    try:
        with open(filename, 'w', encoding='utf-8') as f:
            json.dump(data, f, ensure_ascii=False, indent=2)
        print(f"\n데이터가 {filename}에 저장되었습니다.")
        
        # 샘플 데이터 출력
        if data and len(data) > 0:
            print("\n[샘플 데이터]")
            for player in data[:3]:
                print(f"팀: {player.get('team', 'N/A')}, 번호: {player.get('number', 'None')}, 이름: {player.get('name', 'N/A')}, 포지션: {player.get('position', 'None')}")
    except Exception as e:
        print(f"\n데이터 저장 실패: {e}")


def load_from_json(filename='players_data.json'):
    """
    JSON 파일에서 데이터 로드
    
    Args:
        filename: 로드할 파일명
    
    Returns:
        list: 선수 데이터
    """
    try:
        with open(filename, 'r', encoding='utf-8') as f:
            return json.load(f)
    except Exception as e:
        print(f"JSON 파일 로드 실패: {e}")
        return None


def main(use_existing_json=False):
    """
    메인 실행 함수
    
    Args:
        use_existing_json: True면 기존 JSON에서 URL을 읽어서 상세 정보만 수집
    """
    if use_existing_json:
        # 기존 JSON 파일에서 URL 읽기
        print("\n기존 JSON 파일에서 URL 로드 중...")
        players_data = load_from_json()
        
        if not players_data:
            print("JSON 파일을 찾을 수 없습니다. 전체 크롤링을 시작합니다.")
            players_data = crawl_all_players()
    else:
        # 전체 선수 URL 크롤링
        players_data = crawl_all_players()
    
    # 선수 상세 정보 수집
    if players_data:
        players_data = collect_player_details(players_data)
        
        # JSON 파일로 저장
        save_to_json(players_data)
    else:
        print("\n수집된 데이터가 없습니다.")


def parse_position(position_str):
    """
    포지션 문자열을 수비포지션과 투타로 분리
    
    Args:
        position_str: "투수(우투우타)" 형식의 문자열
    
    Returns:
        tuple: (수비포지션, 투타)
    """
    if not position_str:
        return '', ''
    
    if '(' in position_str and ')' in position_str:
        # "투수(우투우타)" → position="투수", bat_throw="우투우타"
        position = position_str.split('(')[0].strip()
        bat_throw = position_str.split('(')[1].split(')')[0].strip()
        return position, bat_throw
    else:
        return position_str.strip(), ''


def save_to_mysql(json_file='players_data.json', 
                  host='localhost', 
                  user='root', 
                  password='', 
                  database='dugout'):
    """
    JSON 파일의 선수 데이터를 MySQL DB에 저장
    
    Args:
        json_file: JSON 파일 경로
        host: MySQL 호스트
        user: MySQL 사용자명
        password: MySQL 비밀번호
        database: 데이터베이스명
    """
    print("\n" + "=" * 60)
    print("MySQL DB에 데이터 저장 시작")
    print("=" * 60)
    
    try:
        # MySQL 연결
        print(f"MySQL 연결 중... (host={host}, database={database})")
        connection = pymysql.connect(
            host=host,
            user=user,
            password=password,
            database=database,
            charset='utf8mb4',
            cursorclass=pymysql.cursors.DictCursor
        )
        
        cursor = connection.cursor()
        print("MySQL 연결 성공!")
        
        # 테이블 존재 여부 확인
        cursor.execute("SHOW TABLES LIKE 'players_data'")
        table_exists = cursor.fetchone()
        
        if table_exists:
            print("\n기존 players_data 테이블 발견 - 테이블 삭제 중...")
            cursor.execute("DROP TABLE players_data")
            print("기존 테이블 삭제 완료!")
        
        print("\nplayers_data 테이블 생성 중...")
        create_table_sql = """
        CREATE TABLE `players_data` (
            `player_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `team_code` VARCHAR(10) NOT NULL,
            `name` VARCHAR(50) NOT NULL,
            `number` VARCHAR(10),
            `position` VARCHAR(20),
            `bat_throw` VARCHAR(20),
            `birth_date` VARCHAR(50),
            `height` VARCHAR(10),
            `weight` VARCHAR(10),
            `debut_year` VARCHAR(20),
            `salary` VARCHAR(50),
            `school` TEXT,
            `url` VARCHAR(255),
            INDEX `idx_team_code` (`team_code`),
            INDEX `idx_position` (`position`),
            INDEX `idx_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        """
        cursor.execute(create_table_sql)
        print("테이블 생성 완료!")
        
        # JSON 파일 로드
        print(f"\nJSON 파일 로드 중... ({json_file})")
        with open(json_file, 'r', encoding='utf-8') as f:
            players = json.load(f)
        
        print(f"총 {len(players)}명의 선수 데이터 로드 완료")
        
        # 데이터 삽입
        print("\n데이터 삽입 중...")
        insert_sql = """
        INSERT INTO players_data 
        (team_code, name, number, position, bat_throw, 
         birth_date, height, weight, debut_year, salary, school, url)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        
        success_count = 0
        error_count = 0
        
        for idx, player in enumerate(players, 1):
            try:
                # 포지션 분리
                position, bat_throw = parse_position(player.get('position', ''))
                
                cursor.execute(insert_sql, (
                    player.get('team_code', ''),
                    player.get('name', ''),
                    player.get('number', ''),
                    position,
                    bat_throw,
                    player.get('birth_date', ''),
                    player.get('height', ''),
                    player.get('weight', ''),
                    player.get('debut_year', ''),
                    player.get('salary', ''),
                    player.get('school', ''),
                    player.get('url', '')
                ))
                success_count += 1
                
                if idx % 100 == 0:
                    print(f"  진행 중... {idx}/{len(players)} ({success_count}명 성공)")
                    
            except Exception as e:
                error_count += 1
                print(f"  [오류] {player.get('name', '알 수 없음')} 삽입 실패: {e}")
        
        # 커밋
        connection.commit()
        
        print("\n" + "=" * 60)
        print(f"DB 저장 완료!")
        print(f"  - 성공: {success_count}명")
        print(f"  - 실패: {error_count}명")
        print("=" * 60)
        
        cursor.close()
        connection.close()
        
        return True
        
    except pymysql.Error as e:
        print(f"\n[MySQL 오류] {e}")
        return False
    except FileNotFoundError:
        print(f"\n[오류] JSON 파일을 찾을 수 없습니다: {json_file}")
        return False
    except Exception as e:
        print(f"\n[오류] 예상치 못한 오류 발생: {e}")
        return False


if __name__ == '__main__':
    import sys
    
    # 명령줄 인자로 모드 선택
    # python player_crawling.py --use-existing
    use_existing = '--use-existing' in sys.argv
    
    main(use_existing_json=use_existing)
    
    # 크롤링 완료 후 DB에 저장
    print("\n크롤링 완료! DB 저장을 시작합니다...")
    save_to_mysql(json_file='players_data.json')
