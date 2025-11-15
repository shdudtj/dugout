<?php
session_start();

// 로그인 상태 확인
$isLoggedIn = isset($_SESSION['id']);
$userNickname = '';

// 로그인된 경우 사용자 정보 가져오기
if ($isLoggedIn) {
    include './db/db_conn.php';
    $userId = $_SESSION['id'];
    $sql = "SELECT user_name FROM user_info WHERE id = '$userId'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        $userNickname = $row['user_name'];
    }
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8" />
  <title>DUGOUT</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
  <link rel="stylesheet" href="/main/css/fonts.css"/>
  <link rel="stylesheet" href="/main/css/main.css" />
  <script src="/main/main.js" defer></script>
</head>
<body>
  <div class="container">
    <!-- 사이드바 -->
    <aside class="sidebar">
      <div class="logo" onclick="loadPageWithScript('./pages/board/board.php')" style="cursor: pointer;" title="메인으로">DUGOUT</div>

      <div class="profile">
        <div class="profile-img"></div>
        <div class="nickname"><?= $isLoggedIn ? htmlspecialchars($userNickname) : '닉네임' ?></div>

        <div class="auth-buttons">
          <?php if ($isLoggedIn): ?>
            <button onclick="openPopup('./pages/myinfo/myinfo.php')">내 정보</button>
            <button onclick="logout()">로그아웃</button>
          <?php else: ?>
            <button onclick="openPopup('./pages/signup/signup.php')">회원가입</button>
            <button onclick="openPopup('./pages/login/login.php')">로그인</button>
          <?php endif; ?>
        </div>
      </div>

      <nav class="menu">
        <a href="#" data-page="./pages/board/board.php" class="menu-link active">게시판</a>

        <?php if ($isLoggedIn): ?>
          <a href="#" data-page="./pages/myactivity/myactivity.php" class="menu-link">나의 활동</a>
        <?php else: ?>
          <div class="tooltip-container">
            <a href="#" class="menu-link disabled">나의 활동</a>
            <div class="tooltip">로그인이 필요합니다</div>
          </div>
        <?php endif; ?>

        <a href="#" data-page="./pages/schedule/schedule.php" class="menu-link">경기 일정</a>
        <a href="#" data-page="./pages/ranking/ranking.php" class="menu-link">구단 순위</a>
        <a href="#" data-page="./pages/team/team.php" class="menu-link">팀 정보</a>
        <a href="#" data-page="./pages/player/player.php" class="menu-link">선수 정보</a>
      </nav>
    </aside>

    <!-- 메인 영역 -->
    <main class="main-content">
      <!-- 상단 검색 + 글쓰기 버튼 (남색 원형 연필) -->
      <div class="board-toolbar">
        <div class="search">
          <span class="search-icon" onclick="handleSearch()" style="cursor: pointer;">🔍</span>
          <input type="text" id="search-input" placeholder="게시글 검색" onkeypress="if(event.key==='Enter') handleSearch()" />
        </div>

        <button class="write-fab" title="글쓰기" onclick="loadPage('./pages/write/write.php')">
          ✏️
        </button>
      </div>

      <!-- fetch로 들어오는 자리 -->
      <section id="main-view" class="view"></section>

    </main>
  </div>

  <script>
    
    // 사이드바 메뉴 클릭 -> 메인에 HTML 조각 로드
    document.addEventListener('click', (e) => {
      const link = e.target.closest('[data-page]');
      if (!link) {
        return;
      }
      e.preventDefault();
      const page = link.getAttribute('data-page');
      setActive(link);
      
      // 모든 페이지에서 스크립트 실행
      loadPageWithScript(page);
    });

    function setActive(linkEl) {
      document.querySelectorAll('.menu-link').forEach(a => a.classList.remove('active'));
      linkEl.classList.add('active');
    }

    function loadPageWithScript(page) {
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
          console.error('Failed to load page:', err);
          document.getElementById('main-view').innerHTML = '<p>콘텐츠를 불러오지 못했습니다.</p>'; 
        });
    }

    // 팝업(새창) – 로그인/회원가입 전용
    function openPopup(url) {
      window.open(url, '_blank', 'width=420,height=720,menubar=no,toolbar=no,location=no,status=no');
    }

    // 검색 기능
    function handleSearch() {
      const searchInput = document.getElementById('search-input');
      if (!searchInput) return;
      
      const query = searchInput.value.trim();
      
      if (query) {
        loadPageWithScript('./pages/board/board.php?search=' + encodeURIComponent(query));
      } else {
        loadPageWithScript('./pages/board/board.php');
      }
    }

    // 첫 로드 시 게시판 표시
  document.addEventListener('DOMContentLoaded', () => loadPageWithScript('./pages/board/board.php'));
  </script>
</body>
</html>
