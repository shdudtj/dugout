<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/main/css/main.css">
    <title>KBO 경기 일정</title>
</head>
<body>
<?php

// 파라미터 설정 (현재 날짜 기준)
$leId = '1'; // 고정값
$seasonId = isset($_GET['seasonId']) ? $_GET['seasonId'] : date('Y');
$gameMonth = isset($_GET['gameMonth']) ? $_GET['gameMonth'] : date('n');


// KBO 일정 데이터를 가져오는 함수
function getKBOScheduleData($leId, $srIdList, $seasonId, $gameMonth) {
    $url = "https://www.koreabaseball.com/ws/Schedule.asmx/GetScheduleList";
    
    $postData = array(
        'leId' => $leId,
        'srIdList' => $srIdList,
        'seasonId' => $seasonId,
        'gameMonth' => $gameMonth,
        'teamId' => '',
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json, text/plain, */*',
        'X-Requested-With: XMLHttpRequest',
        'Referer: https://www.koreabaseball.com/Schedule/Schedule.aspx'
    ));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200 && !empty($response)) {
        $jsonData = json_decode($response, true);
        if (json_last_error() == JSON_ERROR_NONE) {
            return $jsonData;
        }
    }
    return null;
}

// 게임 데이터 파싱 함수
function parseGameData($rowData, $gameType, $lastDate) {
    // 날짜 확인 (class="day"인 경우)
    $date = '';
    $timeIndex = 0;
    $playIndex = 1;
    $venueIndex = 6;
    $remarksIndex = 7;
    
    // 첫 번째 항목이 날짜인지 확인 (class="day" 또는 날짜 형식)
    $firstText = strip_tags($rowData[0]['Text']);
    if (isset($rowData[0]['Class']) && $rowData[0]['Class'] === 'day' || 
        preg_match('/\d{2}\.\d{2}\([가-힣]\)/', $firstText)) {
        // 날짜가 있는 경우
        $date = $firstText;
        $timeIndex = 1;
        $playIndex = 2;
        $venueIndex = 7;
        $remarksIndex = 8;
    } else {
        // 날짜가 없는 경우 - 마지막 날짜 사용
        $date = $lastDate;
        $timeIndex = 0;
        $playIndex = 1;
        $venueIndex = 6;
        $remarksIndex = 7;
    }
    
    // 시간
    $time = strip_tags($rowData[$timeIndex]['Text']);
    
    // 팀 정보와 스코어
    $playText = $rowData[$playIndex]['Text'];
    $venue = strip_tags($rowData[$venueIndex]['Text']);
    
    // 비고 (경기 취소 사유 등)
    $remarks = isset($rowData[$remarksIndex]) ? strip_tags($rowData[$remarksIndex]['Text']) : '';
    
    // 빈 데이터나 이동일은 건너뛰기
    if (empty($playText) || $remarks === '이동일') {
        return null;
    }
    
    // 팀 정보 파싱
    $teamInfo = parseTeamInfo($playText);
    
    // 경기 취소나 연기인 경우 처리
    if (!empty($remarks) && (strpos($remarks, '취소') !== false || strpos($remarks, '연기') !== false)) {
        $teamInfo['status'] = $remarks;
    }
    
    return [
        'type' => $gameType,
        'date' => $date,
        'time' => $time,
        'teams' => $teamInfo,
        'venue' => $venue,
        'remarks' => $remarks
    ];
}

// 팀 정보 파싱 함수
function parseTeamInfo($playText) {
    $result = ['team1' => '', 'team2' => '', 'score1' => '', 'score2' => '', 'status' => ''];
    
    // HTML 태그 제거하고 순수 텍스트 추출
    $cleanText = strip_tags($playText);
    $cleanText = preg_replace('/\s+/', ' ', $cleanText); // 공백, 줄바꿈을 하나의 공백으로 변환
    $cleanText = trim($cleanText); // 앞뒤 공백 제거
    
    // 빈 텍스트 처리
    if (empty($cleanText)) {
        $result['status'] = '미정';
        return $result;
    }
    
    // vs 키워드로 팀 분리
    if (strpos($cleanText, 'vs') !== false) {
        $parts = explode('vs', $cleanText);
        
        if (count($parts) >= 2) {
            $team1Part = trim($parts[0]);
            $team2Part = trim($parts[1]);
            
            // 팀명에서 숫자(스코어) 분리 - 예: "NC7" -> team="NC", score="7"
            if (preg_match('/^(.+?)(\d+)$/', $team1Part, $matches)) {
                $result['team1'] = $matches[1];
                $result['score1'] = $matches[2];
            } else {
                $result['team1'] = $team1Part;
            }
            
            // 두 번째 팀에서 숫자(스코어) 분리 - 예: "3LG" -> score="3", team="LG"
            if (preg_match('/^(\d+)(.+)$/', $team2Part, $matches)) {
                $result['score2'] = $matches[1];
                $result['team2'] = $matches[2];
            } else {
                $result['team2'] = $team2Part;
            }
            
            // 팀명 정리: 이미 공백이 있으므로 추가 처리 불필요
            $result['team1'] = trim($result['team1']);
            $result['team2'] = trim($result['team2']);
        }
    } else {
        // vs가 없는 경우 전체를 상태로 처리
        $result['status'] = $cleanText;
        return $result;
    }
    
    // 스코어 유효성 검사 (0도 유효한 스코어로 처리)
    if ($result['score1'] !== '' && $result['score2'] !== '' && 
        is_numeric($result['score1']) && is_numeric($result['score2'])) {
        // 스코어가 있는 경기 완료 (0점도 포함)
        $result['status'] = '완료';
    } else {
        // 스코어가 없으면 예정
        $result['score1'] = '';
        $result['score2'] = '';
        if (!empty($result['team1']) && !empty($result['team2'])) {
            $result['status'] = '예정';
        }
    }
    
    // 팀명이 비어있는 경우
    if (empty($result['team1']) || empty($result['team2'])) {
        $result['status'] = '미정';
    }
    
    return $result;
}

// 경기 종류별 데이터 가져오기
$scheduleTypes = [
    'exhibition' => ['srIdList' => '1', 'label' => '시범'],
    'regular' => ['srIdList' => '0,9,6', 'label' => '정규'],
    'postseason' => ['srIdList' => '3,4,5,7', 'label' => '포스트']
];

$allGames = [];
$lastDate = ''; // 마지막 날짜 저장용

foreach ($scheduleTypes as $type => $config) {
    $data = getKBOScheduleData($leId, $config['srIdList'], $seasonId, $gameMonth);
    if ($data && isset($data['rows'])) {
        foreach ($data['rows'] as $row) {
            if (isset($row['row']) && !empty($row['row'][0]['Text'])) {
                $gameInfo = parseGameData($row['row'], $config['label'], $lastDate);
                if ($gameInfo) {
                    // 유효한 날짜가 있으면 저장
                    if (!empty($gameInfo['date'])) {
                        $lastDate = $gameInfo['date'];
                    }
                    $allGames[] = $gameInfo;
                }
            }
        }
    }
}

// 날짜순으로 정렬
usort($allGames, function($a, $b) {
    return strcmp($a['date'], $b['date']);
});
?>

<div class="schedule-container">
  <h2 class="schedule-title">
    KBO 포스트시즌 경기 일정
  </h2>

  <header class="schedule-header">
        <button class="nav-btn" onclick="if(window.schedulePrevMonth) window.schedulePrevMonth(); else alert('schedulePrevMonth not defined');">◀</button>
        <h3 class="month-title"><?php echo $seasonId; ?>년 <?php echo $gameMonth; ?>월</h3>
        <button class="nav-btn" onclick="if(window.scheduleNextMonth) window.scheduleNextMonth(); else alert('scheduleNextMonth not defined');">▶</button>
  </header>

  <table class="schedule-table">
    <thead>
      <tr>
        <th>종류</th>
        <th>날짜</th>
        <th>시간</th>
        <th>대진</th>
        <th>구장</th>
        <th>비고</th>
      </tr>
    </thead>
    <tbody id="schedule-body">
      <?php if (!empty($allGames)): ?>
        <?php foreach ($allGames as $index => $game): ?>
          <tr>
            <td><?php echo $game['type']; ?></td>
            <td><?php echo !empty($game['date']) ? $game['date'] : '-'; ?></td>
            <td><?php echo !empty($game['time']) ? $game['time'] : '-'; ?></td>
            <td>
              <?php if (!empty($game['teams']['status']) && $game['teams']['status'] === '미정'): ?>
                <?php echo $game['teams']['status']; ?>
              <?php elseif (!empty($game['teams']['team1']) && !empty($game['teams']['team2'])): ?>
                <?php echo $game['teams']['team1']; ?> vs <?php echo $game['teams']['team2']; ?>
                <?php if ($game['teams']['status'] === '완료' && $game['teams']['score1'] !== '' && $game['teams']['score2'] !== ''): ?>
                  (<?php echo $game['teams']['score1']; ?>:<?php echo $game['teams']['score2']; ?>)
                <?php endif; ?>
              <?php else: ?>
                경기 정보 확인 중
              <?php endif; ?>
            </td>
            <td><?php echo !empty($game['venue']) ? $game['venue'] : '-'; ?></td>
            <td>
              <?php 
                if (!empty($game['remarks']) && strpos($game['remarks'], '취소') !== false) {
                  echo $game['remarks'];
                } elseif (!empty($game['teams']['status']) && $game['teams']['status'] !== '예정' && $game['teams']['status'] !== '완료') {
                  echo $game['teams']['status'];
                } else {
                  echo '-';
                }
              ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6">해당 월의 경기 일정이 없습니다.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script>
    // main.php 환경에서만 실행되도록 함수를 전역에 등록
    window.scheduleNavigation = window.scheduleNavigation || {};
    
    window.scheduleNavigation.currentYear = <?php echo intval($seasonId); ?>;
    window.scheduleNavigation.currentMonth = <?php echo intval($gameMonth); ?>;
    
    // 전역 함수로 정의 (고유한 이름 사용)
    window.schedulePrevMonth = function() {
        var newMonth = window.scheduleNavigation.currentMonth - 1;
        var newYear = window.scheduleNavigation.currentYear;
        
        if (newMonth < 1) {
            newMonth = 12;
            newYear--;
        }
        
        // 페이지 새로고침 없이 새로운 데이터 fetch
        loadScheduleMonth(newYear, newMonth);
    };
    
    window.scheduleNextMonth = function() {
        var newMonth = window.scheduleNavigation.currentMonth + 1;
        var newYear = window.scheduleNavigation.currentYear;
        
        if (newMonth > 12) {
            newMonth = 1;
            newYear++;
        }
        
        // 페이지 새로고침 없이 새로운 데이터 fetch
        loadScheduleMonth(newYear, newMonth);
    };
    
    // 새로운 월 데이터를 fetch로 가져오는 함수
    function loadScheduleMonth(year, month) {
        var url = './pages/schedule/schedule.php?seasonId=' + year + '&gameMonth=' + month;
        
        fetch(url)
            .then(r => r.ok ? r.text() : Promise.reject(r.status))
            .then(html => {
                // 현재 schedule-container를 새로운 내용으로 교체
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                var newContainer = tempDiv.querySelector('.schedule-container');
                var currentContainer = document.querySelector('.schedule-container');
                
                if (newContainer && currentContainer) {
                    currentContainer.parentNode.replaceChild(newContainer, currentContainer);
                    
                    // 전역 변수 업데이트
                    window.scheduleNavigation.currentYear = year;
                    window.scheduleNavigation.currentMonth = month;
                    
                    // 새로운 버튼들에 이벤트 다시 연결
                    attachScheduleEvents();
                }
            })
            .catch(err => {
                alert('일정을 불러오는데 실패했습니다.');
            });
    }
    
    // 여러 방식으로 이벤트 연결 시도
    function attachScheduleEvents() {
        var prevBtn = document.getElementById('prevBtn');
        var nextBtn = document.getElementById('nextBtn');
        
        if (prevBtn && !prevBtn.onclick) {
            prevBtn.onclick = window.schedulePrevMonth;
        }
        
        if (nextBtn && !nextBtn.onclick) {
            nextBtn.onclick = window.scheduleNextMonth;
        }
    }
    
    // 즉시 시도
    attachScheduleEvents();
    
    // DOM 로드 후 시도
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachScheduleEvents);
    } else {
        setTimeout(attachScheduleEvents, 100);
    }
    
    // 추가 시도 (main.php 로딩이 늦을 수 있음)
    setTimeout(attachScheduleEvents, 1000);
    setTimeout(attachScheduleEvents, 2000);
</script>
</body>
</html>
  