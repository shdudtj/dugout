// 팀별 상세 정보 데이터 객체
// - key: 팀 식별자 (예: 'lg', 'doosan')
// - value: 각 팀의 이름(title)과 HTML 형식의 상세 정보(body)를 포함
// - body는 팝업에 innerHTML로 삽입되므로 HTML 태그 사용 가능
const teamData = {
    lg: {
      title: "LG 트윈스",
      body: `<p><strong>사무실:</strong> (우 05500)서울특별시 송파구 올림픽로 25 잠실야구장 내</p>
             <p><strong>홈페이지:</strong> <a href="https://www.lgtwins.com" target="_blank">www.lgtwins.com</a></p>
             <p><strong>구단주:</strong> 구광모</p>
             <p><strong>대표이사: 김인석</strong>`
    },
    doosan: {
      title: "두산 베어스",
      body: `<p><strong>사무실:</strong> (우 05500)서울특별시 송파구 올림픽로 25 잠실야구장 내</p>
             <p><strong>홈페이지:</strong> <a href="https://www.doosanbears.com" target="_blank">www.dosanbears.com</a></p>
             <p><strong>구단주:</strong> 박정원</p>
             <p><strong>대표이사:</strong> 고영섭</p>`
    },
    kiwoom: {
      title: "키움 히어로즈",
      body: `<p><strong>사무실:</strong> (우 08275)서울특별시 구로구 경인로 430 고척스카이돔 내</p>
             <p><strong>홈페이지:</strong> <a href="https://www.heroesbaseball.co.kr" target="_blank">www.heroesbaseball.co.kr</a></p>
             <p><strong>구단주:</strong> 위재민</p>
             <p><strong>대표이사:</strong> 위재민</p>`
    },
    kt: {
      title: "KT 위즈",
      body: `<p><strong>사무실:</strong> (우 16308)경기도 수원시 장안구 경수대로 893 수원 케이티위즈파크 내</p>
             <p><strong>홈페이지:</strong> <a href="https://www.ktwiz.co.kr" target="_blank">www.ktwiz.co.kr</a></p>
             <p><strong>구단주:</strong> 김영섭</p>
             <p><strong>대표이사:</strong> 이호식</p>`
    },
    ssg: {
      title: "SSG 랜더스",
      body: `<p><strong>사무실:</strong> (우 22234)인천광역시 미추홀구 매소홀로 618 인천SSG랜더스필드 내</p>
             <p><strong>홈페이지:</strong> <a href="https://www.ssglanders.com" target="_blank">www.ssglanders.com</a></p>
             <p><strong>구단주:</strong> 정용진</p>
             <p><strong>대표이사:</strong> 김재섭</p>`
    },
    lotte: {
      title: "롯데 자이언츠",
      body: `<p><strong>사무실:</strong> (우 47874)부산광역시 동래구 사직로 45 사직야구장 내</p>
             <p><strong>홈페이지:</strong> <a href="https://www.giantsclub.com" target="_blank">www.giantsclub.com</a></p>
             <p><strong>구단주:</strong> 신동빈</p>
             <p><strong>대표이사:</strong> 이강훈</p>`
    },
    kia: {
      title: "KIA 타이거즈",
      body: `<p><strong>사무실:</strong> (우 61255)광주광역시 북구 서림로 10 광주-기아 챔피언스 필드 내 2층</p>
             <p><strong>홈페이지:</strong> <a href="https://www.tigers.co.kr" target="_blank">www.tigers.co.kr</a></p>
             <p><strong>구단주:</strong> 손호성</p>
             <p><strong>대표이사:</strong> 최준영</p>`
    },
    samsung: {
      title: "삼성 라이온즈",
      body: `<p><strong>사무실:</strong> (우 42250)대구광역시 수성구 야구전설로 1 대구삼성라이온즈파크 내</p>
             <p><strong>홈페이지:</strong> <a href="https://www.samsunglions.com" target="_blank">www.samsunglions.com</a></p>
             <p><strong>구단주:</strong> 유정근</p>
             <p><strong>대표이사:</strong> 유정근</p>`
    },
    nc: {
      title: "NC 다이노스",
      body: `<p><strong>사무실:</strong> 분당 사무실 - (우 13494)경기도 성남시 분당구 대왕판교로 644번길 12 앤씨소프트 판교 R&D센터 C동 12층<br>
                                   창원 사무실 - (우 51323)경상남도 창원시 마산회원구 삼호로 63 창원NC파크 내</p>
             <p><strong>홈페이지:</strong> <a href="https://www.ncdinos.com" target="_blank">www.ncdinos.com</a></p>
             <p><strong>구단주:</strong> 김택진</p>
             <p><strong>대표이사:</strong> 이진만</p>`
    },
    hanwha: {
      title: "한화 이글스",
      body: `<p><strong>사무실:</strong> (우 35021)대전광역시 중구 대종로 373 한화이글스</p>
             <p><strong>홈페이지:</strong> <a href="https://www.hanwhaeagles.co.kr" target="_blank">www.hanwhaeales.co.kr</a></p>
             <p><strong>구단주:</strong> 김승연</p>
             <p><strong>대표이사:</strong> 박종태</p>`
    }
  };
  
  /**
   * showTeamPopup(teamId)
   * - 사용자가 테이블에서 팀명을 클릭하면 호출되는 함수
   * - teamId를 기반으로 teamData에서 해당 팀 정보를 가져옴
   * - 팝업의 제목과 본문을 업데이트하고, 팝업을 화면에 표시함
   * - 팝업은 display: none 상태에서 display: flex로 변경됨
   */
  function showTeamPopup(teamId) {
    const data = teamData[teamId];
    if (data) {
      document.getElementById("popup-title").innerText = data.title;     // 팝업 제목 설정
      document.getElementById("popup-body").innerHTML = data.body;       // 팝업 본문 HTML 삽입
      document.getElementById("popup").style.display = "flex";           // 팝업 표시
    }
  }
  
  /**
   * closePopup()
   * - 팝업 닫기 버튼 클릭 시 호출되는 함수
   * - 팝업을 다시 숨김 상태로 변경함
   */
  function closePopup() {
    document.getElementById("popup").style.display = "none";             // 팝업 숨김 처리
  }

  // 팝업 외부 클릭 시 팝업 닫기
  document.addEventListener('DOMContentLoaded', function() {
    const popup = document.getElementById("popup");
    const popupContent = document.querySelector(".popup-content");
    
    if (popup) {
      popup.addEventListener('click', function(e) {
        // 팝업 오버레이를 클릭했지만 팝업 콘텐츠를 클릭하지 않은 경우
        if (e.target === popup) {
          closePopup();
        }
      });
    }
  });