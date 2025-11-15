<!-- 팀 정보 테이블 컨테이너 -->
<div class="team-info-container">
  <h2 class="team-info-title">팀 정보</h2>

  <!-- 팀 정보 테이블 -->
  <table class="team-info-table">
    <thead>
      <tr>
        <th>구단명</th>
        <th>창단년도</th>
        <th>연고지</th>
        <th>우승횟수</th>
      </tr>
    </thead>
    <tbody>
      <!-- 각 행은 하나의 팀을 나타냄 -->
      <!-- 클릭 시 showTeamPopup(teamId) 함수 호출 → 팝업에 상세 정보 표시 -->
      <tr onclick="showTeamPopup('lg')"><td class="team-name">LG 트윈스</td><td>1982</td><td>서울</td><td>2회</td></tr>
      <tr onclick="showTeamPopup('doosan')"><td class="team-name">두산 베어스</td><td>1982</td><td>서울</td><td>6회</td></tr>
      <tr onclick="showTeamPopup('kiwoom')"><td class="team-name">키움 히어로즈</td><td>2008</td><td>서울</td><td>없음</td></tr>
      <tr onclick="showTeamPopup('kt')"><td class="team-name">KT 위즈</td><td>2013</td><td>수원</td><td>1회</td></tr>
      <tr onclick="showTeamPopup('ssg')"><td class="team-name">SSG 랜더스</td><td>2000</td><td>인천</td><td>5회</td></tr>
      <tr onclick="showTeamPopup('lotte')"><td class="team-name">롯데 자이언츠</td><td>1982</td><td>부산</td><td>2회</td></tr>
      <tr onclick="showTeamPopup('kia')"><td class="team-name">KIA 타이거즈</td><td>1982</td><td>광주</td><td>11회</td></tr>
      <tr onclick="showTeamPopup('samsung')"><td class="team-name">삼성 라이온즈</td><td>1982</td><td>대구</td><td>8회</td></tr>
      <tr onclick="showTeamPopup('nc')"><td class="team-name">NC 다이노스</td><td>2011</td><td>창원</td><td>1회</td></tr>
      <tr onclick="showTeamPopup('hanwha')"><td class="team-name">한화 이글스</td><td>1986</td><td>대전</td><td>1회</td></tr>
    </tbody>
  </table>
</div>

<!-- 팝업 모달: 팀 상세 정보 표시용 -->
<div id="popup" class="popup-overlay" style="display:none;">
  <div class="popup-content">
    <!-- 팝업 닫기 버튼: 클릭 시 closePopup() 함수 호출 -->
    <span class="close-btn" onclick="closePopup()">×</span>

    <!-- 팝업 제목: 선택된 팀 이름 표시 -->
    <h3 id="popup-title">팀 상세 정보</h3>

    <!-- 팝업 본문: 선택된 팀의 상세 정보 HTML 삽입됨 -->
    <div id="popup-body"></div>
  </div>
</div>

<script src="./pages/team/team.js"></script>
