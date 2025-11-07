// main.js
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

// 로그아웃 함수
function logout() {
  if (confirm('로그아웃 하시겠습니까?')) {
    window.location.href = './pages/login/logout.php';
  }
}

// main.js
function loadPage(page) {
  fetch(page)
    .then(r => r.ok ? r.text() : Promise.reject(r.status))
    .then(html => { 
      const mainView = document.getElementById('main-view');
      
      // 임시 div를 만들어서 HTML 파싱
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = html;
      
      // 스크립트를 먼저 추출
      const scripts = tempDiv.querySelectorAll('script');
      
      // 스크립트 제거 후 HTML 삽입
      scripts.forEach(script => script.remove());
      mainView.innerHTML = tempDiv.innerHTML;
      
      // 스크립트를 하나씩 실행
      scripts.forEach((script, index) => {
        try {
          const newScript = document.createElement('script');
          newScript.type = 'text/javascript';
          
          if (script.src) {
            newScript.src = script.src;
          } else {
            newScript.textContent = script.textContent;
          }
          
          // body에 추가해서 실행
          document.body.appendChild(newScript);
          
        } catch (e) {
          console.error('Script execution error:', e);
        }
      });
    })
    .catch(err => {
      console.error('페이지 로딩 실패:', err);
      document.getElementById('main-view').innerHTML = '<p>콘텐츠를 불러오지 못했습니다.</p>';
    });
}
  