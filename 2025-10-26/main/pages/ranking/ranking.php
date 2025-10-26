<?php
// 크롤링 함수 - 실시간 KBO 순위 가져오기
function getRankingData() {
    include_once('../../library/simple_html_dom.php');
    
    try {
        $html = file_get_html("https://www.koreabaseball.com/Record/TeamRank/TeamRankDaily.aspx");
        
        if (!$html) {
            return null;
        }
        
        $ret = $html->find('table[summary="순위, 팀명,승,패,무,승률,승차,최근10경기,연속,홈,방문"]');
        
        if (empty($ret)) {
            return null;
        }
        
        $data = [];
        $tbody = $ret[0]->find('tbody', 0);
        
        if ($tbody) {
            foreach ($tbody->find('tr') as $row) {
                $rowData = [];
                foreach ($row->find('td') as $cell) {
                    $rowData[] = trim($cell->plaintext);
                }
                if (!empty($rowData) && count($rowData) >= 8) {
                    $data[] = $rowData;
                }
            }
        }
        
        return $data;
    } catch (Exception $e) {
        return null;
    }
}

$rankingData = getRankingData();
?>

<div class="rank-container">
    <h2 class="rank-title">KBO 구단 순위 (실시간)</h2>
  
    <table class="rank-table">
      <thead>
        <tr>
          <th>순위</th>
          <th>팀명</th>
          <th>경기</th>
          <th>승</th>
          <th>패</th>
          <th>무</th>
          <th>승률</th>
          <th>게임차</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($rankingData && !empty($rankingData)): ?>
          <?php foreach ($rankingData as $team): ?>
            <tr>
              <td><?= htmlspecialchars($team[0]) ?></td>
              <td><?= htmlspecialchars($team[1]) ?></td>
              <td><?= htmlspecialchars($team[2]) ?></td>
              <td><?= htmlspecialchars($team[3]) ?></td>
              <td><?= htmlspecialchars($team[4]) ?></td>
              <td><?= htmlspecialchars($team[5]) ?></td>
              <td><?= htmlspecialchars($team[6]) ?></td>
              <td><?= htmlspecialchars($team[7]) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <!-- 크롤링 실패 시 기본 데이터 -->
          <tr><td colspan="8" style="text-align: center;">순위 정보를 불러올 수 없습니다.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    
    <p style="font-size: 12px; color: #666; margin-top: 10px;">
      * 데이터 출처: KBO 공식 웹사이트 (실시간 업데이트)
    </p>
  </div>