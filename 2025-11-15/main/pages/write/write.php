<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>write</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="/main/css/fonts.css" />
  <link rel="stylesheet" href="/main/css/write.css" />
</head>
<body>
  <div class="write-container">
    <h2>글 작성</h2>
    <?php if (!isset($_SESSION['id'])): ?>
      <!-- 로그인 안 된 경우 -->
      <div style="text-align: center; padding: 60px 20px;">
        <span class="material-icons" style="font-size: 64px; color: #ccc;">lock</span>
        <p style="font-size: 18px; color: #666; margin-top: 20px;">로그인이 필요한 서비스입니다.</p>
        <button class="cancel-btn" style="margin-top: 24px;" onclick="goBack()">돌아가기</button>
      </div>
    <?php else: ?>
      <!-- 로그인 된 경우 -->
      <form id="writeForm" method="post">
        <input type="text" name="title" id="title" placeholder="제목을 입력하세요" class="write-title" required />
        <textarea name="content" id="content" placeholder="내용을 입력하세요" class="write-body" required style="width: 100%; max-width: 100%;"></textarea>
        <div class="write-actions">
          <button type="button" class="cancel-btn">취소</button>
          <button type="submit" class="submit-btn">등록</button>
          <input type="file" class="upload-photo" disabled title="파일 첨부 기능은 추후 지원 예정입니다" />
        </div>
      </form>
    <?php endif; ?>
  </div>

  <script>
    // 로그인 안 된 경우 돌아가기
    function goBack() {
      if (typeof loadPage === 'function') {
        loadPage('./pages/board/board.php');
      } else {
        history.back();
      }
    }

    // 즉시 실행 함수로 감싸서 스코프 격리 및 중복 실행 방지
    (function() {
      const writeForm = document.getElementById('writeForm');
      if (!writeForm) return;  // 폼이 없으면 종료

      // 취소 버튼
      const cancelBtn = document.querySelector('.cancel-btn');
      if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
          if (confirm('작성을 취소하시겠습니까?')) {
            if (typeof loadPage === 'function') {
              loadPage('./pages/board/board.php');
            } else {
              history.back();
            }
          }
        });
      }

      // 폼 제출 처리 (AJAX)
      writeForm.addEventListener('submit', function(e) {
        e.preventDefault();  // 기본 폼 제출 차단

        const title = document.getElementById('title').value.trim();
        const content = document.getElementById('content').value.trim();

        // 유효성 검증
        if (!title) {
          alert('제목을 입력해주세요.');
          document.getElementById('title').focus();
          return;
        }

        if (!content) {
          alert('내용을 입력해주세요.');
          document.getElementById('content').focus();
          return;
        }

        // FormData 생성
        const formData = new FormData();
        formData.append('title', title);
        formData.append('content', content);

        // AJAX 요청
        fetch('./pages/write/write_proc.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(data.message);
            // 게시판으로 이동
            if (typeof loadPage === 'function') {
              loadPage('./pages/board/board.php');
            } else {
              window.location.href = '../../main.php';
            }
          } else {
            alert(data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('게시글 등록 중 오류가 발생했습니다.');
        });
      });
    })();
  </script>
</body>
</html>