# 데이터베이스 설정 안내서

## 📋 개요
XAMPP 환경에서 phpMyAdmin을 통해 실행할 수 있는 MySQL 데이터베이스 생성 스크립트입니다.

## 🗂️ 테이블 구조

### 📊 생성되는 테이블 목록
1. **user_info** - 사용자 정보 (메인 테이블)
2. **my_board** - 사용자 게시판
3. **my_comment** - 사용자 댓글
4. **my_liked** - 사용자 좋아요
5. **notice_board** - 공지사항 게시판
6. **comment** - 댓글

### 🔗 테이블 관계도
```
user_info (사용자)
├── my_board (사용자 게시판)
│   ├── my_comment (게시판 댓글)
│   └── my_liked (게시판 좋아요)
├── notice_board (공지사항)
│   └── comment (공지사항 댓글)
```

## 🚀 설치 방법

### 1단계: XAMPP 시작
1. XAMPP Control Panel 실행
2. **Apache**와 **MySQL** 서비스 시작

### 2단계: phpMyAdmin 접속
1. 웹 브라우저에서 `http://localhost/phpmyadmin` 접속
2. 데이터베이스 생성 (선택사항):
   - 좌측 메뉴에서 "새로 만들기" 클릭
   - 데이터베이스 이름 입력 (예: `main_db`)
   - 정렬: `utf8mb4_unicode_ci` 선택

### 3단계: SQL 스크립트 실행
1. phpMyAdmin에서 생성한 데이터베이스 선택
2. 상단 메뉴에서 **"SQL"** 탭 클릭
3. `database_setup.sql` 파일 내용 복사 후 붙여넣기
4. **"실행"** 버튼 클릭

## 📝 테이블 상세 정보

### user_info (사용자 정보)
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| id | VARCHAR(45) | 사용자 ID (Primary Key) |
| password | VARCHAR(45) | 비밀번호 |
| user_name | VARCHAR(45) | 사용자명 |
| phone_number | VARCHAR(45) | 전화번호 |
| email | VARCHAR(45) | 이메일 |
| team_choice | INT | 팀 선택 |
| first_name | VARCHAR(10) | 이름 |
| last_name | VARCHAR(10) | 성 |

### my_board (사용자 게시판)
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| notice_board_number_id | INT | 게시글 ID (Primary Key, Auto Increment) |
| user_info_id | VARCHAR(45) | 작성자 ID (Foreign Key) |
| title | VARCHAR(255) | 제목 |
| content | TEXT | 내용 |

### notice_board (공지사항)
| 컬럼명 | 타입 | 설명 |
|--------|------|------|
| number_id | INT | 공지사항 ID (Primary Key, Auto Increment) |
| title | VARCHAR(45) | 제목 |
| content | VARCHAR(255) | 내용 |
| like | INT | 좋아요 수 |
| views | INT | 조회수 |
| date_time | DATETIME | 작성일시 |
| user_info_id | VARCHAR(45) | 작성자 ID (Foreign Key) |

## ⚙️ 주요 특징

### 🔒 데이터 무결성
- **외래키 제약조건**: 관련 테이블 간 데이터 일관성 보장
- **CASCADE 옵션**: 부모 레코드 삭제/수정 시 자동 연동

### 🚀 성능 최적화
- **인덱스 설정**: 자주 검색되는 컬럼에 인덱스 생성
- **UTF8MB4**: 한글 및 이모지 완벽 지원

### 📊 자동 필드
- **타임스탬프**: 생성/수정 시간 자동 기록
- **AUTO_INCREMENT**: 기본키 자동 증가

## 🧪 테스트 데이터
스크립트 하단의 주석된 샘플 데이터를 활용하여 테스트할 수 있습니다.

```sql
-- 주석 해제하여 샘플 데이터 삽입
/*
INSERT INTO `user_info` ...
*/
```

## 🔧 문제 해결

### 자주 발생하는 오류
1. **권한 오류**: MySQL 사용자 권한 확인
2. **인코딩 오류**: UTF8MB4 설정 확인
3. **외래키 오류**: 테이블 생성 순서 확인

### 초기화 방법
```sql
-- 모든 테이블 삭제 후 재생성
SET FOREIGN_KEY_CHECKS = 0;
-- DROP TABLE 문들...
SET FOREIGN_KEY_CHECKS = 1;
```

## 📞 지원
문제가 발생하면 다음을 확인하세요:
1. XAMPP 서비스 상태
2. MySQL 연결 상태
3. 데이터베이스 권한
4. 스크립트 실행 순서
