/**
 * 나의 활동 페이지 JavaScript
 * - 탭 전환
 * - 게시글/댓글 클릭 이벤트
 * - 페이지네이션
 */

(function() {
  // 탭 클릭 이벤트
  const activityTabs = document.querySelectorAll('.activity-tab');
  activityTabs.forEach(btn => {
    btn.addEventListener('click', function() {
      // 탭 활성화
      activityTabs.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      
      // 타입 가져오기
      const type = this.getAttribute('data-type');
      
      // 페이지 다시 로드 (SPA)
      if (typeof loadPage === 'function') {
        loadPage('./pages/myactivity/myactivity.php?type=' + type + '&page=1');
      } else {
        window.location.href = './myactivity.php?type=' + type + '&page=1';
      }
    });
  });
  
  // 페이지 변경 함수 (전역)
  window.changePage = function(page) {
    // 현재 타입 유지
    const urlParams = new URLSearchParams(window.location.search);
    const type = urlParams.get('type') || 'posts';
    
    if (typeof loadPage === 'function') {
      loadPage('./pages/myactivity/myactivity.php?type=' + type + '&page=' + page);
    } else {
      window.location.href = './myactivity.php?type=' + type + '&page=' + page;
    }
  };
  
  // 게시글/댓글 클릭 이벤트 (게시글 상세로 이동)
  const activityItems = document.querySelectorAll('.activity-item');
  activityItems.forEach(item => {
    item.style.cursor = 'pointer';
    item.addEventListener('click', function() {
      const postId = this.getAttribute('data-post-id');
      if (postId) {
        if (typeof loadPage === 'function') {
          loadPage('./pages/board/board_detail.php?id=' + postId);
        } else {
          window.location.href = './pages/board/board_detail.php?id=' + postId;
        }
      }
    });
  });
})();