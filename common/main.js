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

  // main.js
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
  