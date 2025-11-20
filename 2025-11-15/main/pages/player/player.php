<?php
// 데이터베이스 연결
include '../../db/db_conn.php';

// GET 파라미터 수신
$team_code = isset($_GET['team_code']) ? $_GET['team_code'] : '';
$position = isset($_GET['position']) ? $_GET['position'] : '전체';
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';

// 팀명 매핑 함수
function getTeamName($team_code) {
    $teams = [
        'LG' => 'LG 트윈스',
        'WO' => '키움 히어로즈',
        'LT' => '롯데 자이언츠',
        'SS' => '삼성 라이온즈',
        'KT' => 'KT 위즈',
        'SK' => 'SSG 랜더스',
        'HT' => 'KIA 타이거즈',
        'NC' => 'NC 다이노스',
        'OB' => '두산 베어스',
        'HH' => '한화 이글스'
    ];
    return isset($teams[$team_code]) ? $teams[$team_code] : '';
}

// 선수 데이터 조회
$players = [];
if ($team_code) {
    // SQL 쿼리 동적 생성
    $sql = "SELECT * FROM players_data WHERE team_code = ?";
    $params = [$team_code];
    $types = "s";
    
    // 포지션 필터 추가
    if ($position && $position != '전체') {
        $sql .= " AND position = ?";
        $params[] = $position;
        $types .= "s";
    }
    
    // 이름 검색 추가
    if ($search_name) {
        $sql .= " AND name LIKE ?";
        $params[] = "%{$search_name}%";
        $types .= "s";
    }
    
    // 이름 가나다순 정렬
    $sql .= " ORDER BY name ASC";
    
    // Prepared Statement 사용
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        // 파라미터 바인딩
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result) {
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                $players[] = $row;
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="player-info-container">
    <!-- 구단 선택 박스 -->
    <div class="team-selector">
      <div class="team-list">
        <button class="team-btn" data-team-code="LG">LG</button>
        <button class="team-btn" data-team-code="WO">키움</button>
        <button class="team-btn" data-team-code="LT">롯데</button>
        <button class="team-btn" data-team-code="SS">삼성</button>
        <button class="team-btn" data-team-code="KT">KT</button>
        <button class="team-btn" data-team-code="SK">SSG</button>
        <button class="team-btn" data-team-code="HT">KIA</button>
        <button class="team-btn" data-team-code="NC">NC</button>
        <button class="team-btn" data-team-code="OB">두산</button>
        <button class="team-btn" data-team-code="HH">한화</button>
      </div>
    </div>

    <?php if ($team_code): ?>
    <!-- 필터링 UI -->
    <div class="filter-container" style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px;">
        <form id="player-search-form" method="GET" action="" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="team_code" value="<?php echo htmlspecialchars($team_code); ?>">
            
            <label style="font-weight: bold;">포지션:</label>
            <select name="position" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="전체" <?php echo $position == '전체' ? 'selected' : ''; ?>>전체</option>
                <option value="투수" <?php echo $position == '투수' ? 'selected' : ''; ?>>투수</option>
                <option value="내야수" <?php echo $position == '내야수' ? 'selected' : ''; ?>>내야수</option>
                <option value="외야수" <?php echo $position == '외야수' ? 'selected' : ''; ?>>외야수</option>
                <option value="포수" <?php echo $position == '포수' ? 'selected' : ''; ?>>포수</option>
            </select>
            
            <label style="font-weight: bold;">선수명:</label>
            <input type="text" name="search_name" value="<?php echo htmlspecialchars($search_name); ?>" 
                   placeholder="선수 이름 입력" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; width: 200px;">
            
            <button type="submit" style="padding: 8px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                검색
            </button>
            
            <?php if ($position != '전체' || $search_name): ?>
            <a href="#" class="reset-filter" data-team-code="<?php echo htmlspecialchars($team_code); ?>"
               style="padding: 8px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">
                필터 초기화
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- 선수 테이블 -->
    <h2 class="player-info-title">❖ <?php echo htmlspecialchars(getTeamName($team_code)); ?> 선수 정보</h2>
    <p style="font-size: 12px; color: #666; margin: -10px 0 10px 0;">
        * 이름 클릭 시 KBO 공식 홈페이지 기록실로 이동됩니다.
    </p>
  
    <table class="player-info-table">
      <thead>
        <tr>
          <th>등번호</th>
          <th>이름</th>
          <th>포지션</th>
          <th>생년월일</th>
          <th>신장/체중</th>
          <th>입단연도</th>
          <th>연봉</th>
          <th>출신학교</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($players) > 0): ?>
            <?php foreach ($players as $player): ?>
            <tr>
                <td><?php echo htmlspecialchars($player['number'] ? $player['number'] : '-'); ?></td>
                <td>
                    <a href="<?php echo htmlspecialchars($player['url']); ?>" target="_blank" style="color: #007bff; text-decoration: none; font-weight: bold;">
                        <?php echo htmlspecialchars($player['name']); ?>
                    </a>
                </td>
                <td>
                    <?php 
                    $pos_display = htmlspecialchars($player['position']);
                    if ($player['bat_throw']) {
                        $pos_display .= ' (' . htmlspecialchars($player['bat_throw']) . ')';
                    }
                    echo $pos_display;
                    ?>
                </td>
                <td><?php echo htmlspecialchars($player['birth_date']); ?></td>
                <td><?php echo htmlspecialchars($player['height']) . '/' . htmlspecialchars($player['weight']); ?></td>
                <td><?php echo htmlspecialchars($player['debut_year']); ?></td>
                <td><?php echo htmlspecialchars($player['salary']); ?></td>
                <td><?php echo htmlspecialchars($player['school']); ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                    검색 결과가 없습니다.
                </td>
            </tr>
        <?php endif; ?>
      </tbody>
    </table>
    
    <div style="margin-top: 15px; text-align: right; color: #666; font-size: 14px;">
        총 <strong><?php echo count($players); ?></strong>명의 선수
    </div>
    <?php else: ?>
    <!-- 팀 선택 전 안내 메시지 -->
    <div style="text-align: center; padding: 60px 20px; color: #999;">
        <h3 style="font-size: 24px; margin-bottom: 15px;">팀을 선택해주세요</h3>
        <p style="font-size: 16px;">위의 팀 로고를 클릭하여 선수 정보를 확인하세요.</p>
    </div>
    <?php endif; ?>
</div>

<script>
// 팀 버튼 클릭 이벤트 및 검색 폼 처리
(function() {
    // 팀 버튼 클릭 이벤트
    const teamButtons = document.querySelectorAll('.team-btn');
    
    teamButtons.forEach(button => {
        button.addEventListener('click', function() {
            const teamCode = this.getAttribute('data-team-code');
            if (teamCode) {
                // SPA 구조에서 페이지 이동
                const currentPage = './pages/player/player.php?team_code=' + teamCode;
                
                // main.php의 loadPageWithScript 함수 사용
                if (typeof loadPageWithScript === 'function') {
                    loadPageWithScript(currentPage);
                } else {
                    // fallback: 전체 페이지 새로고침
                    window.location.href = '/main/main.php';
                }
            }
        });
    });
    
    // 검색 폼 제출 이벤트
    const searchForm = document.getElementById('player-search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            const currentPage = './pages/player/player.php?' + params.toString();
            
            if (typeof loadPageWithScript === 'function') {
                loadPageWithScript(currentPage);
            } else {
                window.location.href = '/main/main.php';
            }
        });
    }
    
    // 필터 초기화 버튼
    const resetFilter = document.querySelector('.reset-filter');
    if (resetFilter) {
        resetFilter.addEventListener('click', function(e) {
            e.preventDefault();
            
            const teamCode = this.getAttribute('data-team-code');
            const currentPage = './pages/player/player.php?team_code=' + teamCode;
            
            if (typeof loadPageWithScript === 'function') {
                loadPageWithScript(currentPage);
            } else {
                window.location.href = '/main/main.php';
            }
        });
    }
})();
</script>