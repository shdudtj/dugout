// main.js

/**
 * 회원가입/로그인 버튼 클릭 시 새 창으로 해당 페이지를 띄우는 함수
 * - 사용 목적: 인증 관련 페이지는 별도 팝업으로 처리
 * - 서버 요청은 발생하지 않으며, 클라이언트에서 window.open으로 처리됨
 */
function openPopup(url) {
  const width = 500;
  const height = 600;
  const left = (window.screen.width / 2) - (width / 2);
  const top = (window.screen.height / 2) - (height / 2);

  window.open(
    url,
    "_blank",
    `width=${width},height=${height},left=${left},top=${top},resizable=no,scrollbars=yes`
  );
}

/**
 * SPA 구조에서 페이지 콘텐츠를 동적으로 로드하는 함수
 * - 사용 목적: 메뉴 클릭 시 전체 페이지 이동 없이 HTML 조각만 교체
 * - 서버 요청: fetch(page)로 해당 HTML 파일을 요청
 * - 삽입 위치: <section id="main-view"> 내부에 삽입
 */
function loadPage(page) {
  fetch(page)
    .then(r => r.ok ? r.text() : Promise.reject(r.status))
    .then(html => {
      document.getElementById('main-view').innerHTML = html;
    })
    .catch(err => {
      console.error('페이지 로딩 실패:', err);
      document.getElementById('main-view').innerHTML = '<p>콘텐츠를 불러오지 못했습니다.</p>';
    });
}


// team.js

/**
 * SPA 구조에서 팀 정보 페이지(team.html)를 로드할 때 실행되는 함수
 * - 사용 목적: 팀 정보 페이지를 <main-view>에 삽입하고, 관련 JS(team.js)를 동적으로 로드
 * - 주의: team.html은 fetch로 삽입되므로 <script src="team.js">는 자동 실행되지 않음
 * - 해결: team.js를 동적으로 생성하여 body에 삽입함
 */
function loadPage(page) {
  fetch(page)
    .then(r => r.ok ? r.text() : Promise.reject(r.status))
    .then(html => {
      document.getElementById('main-view').innerHTML = html;

      // team.html이 로드된 경우에만 team.js를 동적으로 삽입
      if (page.includes('team.html')) {
        const script = document.createElement('script');
        script.src = '/pages/team/team.js'; // 경로는 실제 위치에 따라 조정 필요
        script.defer = true;
        document.body.appendChild(script);
      }
    })
    .catch(err => {
      console.error('페이지 로딩 실패:', err);
      document.getElementById('main-view').innerHTML = '<p>콘텐츠를 불러오지 못했습니다.</p>';
    });
}
