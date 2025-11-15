<?php
session_start();

// 데이터베이스 연결
include '../../db/db_conn.php';

// 로그인 상태 확인
$isLoggedIn = isset($_SESSION['id']);
$userId = $isLoggedIn ? $_SESSION['id'] : null;

// 게시글 ID 가져오기
$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($postId <= 0) {
    echo '<div class="error-message">잘못된 요청입니다.</div>';
    exit;
}

// 게시글 정보 조회 (작성자명 포함)
$sql = "
    SELECT 
        nb.number_id,
        nb.title,
        nb.content,
        nb.views,
        nb.like,
        nb.date_time,
        nb.user_info_id,
        ui.user_name
    FROM notice_board nb
    LEFT JOIN user_info ui ON nb.user_info_id = ui.id
    WHERE nb.number_id = ?
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $postId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    echo '<div class="error-message">게시글을 찾을 수 없습니다.</div>';
    mysqli_close($conn);
    exit;
}

$post = mysqli_fetch_array($result);

// 조회수 증가 (세션 기반 중복 방지)
if (!isset($_SESSION['viewed_posts'])) {
    $_SESSION['viewed_posts'] = [];
}

if (!in_array($postId, $_SESSION['viewed_posts'])) {
    // 조회수 증가
    $updateViewsSql = "UPDATE notice_board SET views = views + 1 WHERE number_id = ?";
    $updateStmt = mysqli_prepare($conn, $updateViewsSql);
    mysqli_stmt_bind_param($updateStmt, "i", $postId);
    mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);
    
    // 세션에 기록
    $_SESSION['viewed_posts'][] = $postId;
    
    // 페이지에 표시할 조회수도 업데이트
    $post['views'] = intval($post['views']) + 1;
}

// 좋아요 여부 확인 (로그인한 경우만)
$isLiked = false;
if ($isLoggedIn) {
    $likeSql = "SELECT COUNT(*) as is_liked FROM post_likes WHERE user_id = ? AND post_id = ?";
    $likeStmt = mysqli_prepare($conn, $likeSql);
    mysqli_stmt_bind_param($likeStmt, "si", $userId, $postId);
    mysqli_stmt_execute($likeStmt);
    $likeResult = mysqli_stmt_get_result($likeStmt);
    $likeRow = mysqli_fetch_array($likeResult);
    $isLiked = ($likeRow['is_liked'] > 0);
    mysqli_stmt_close($likeStmt);
}

// 댓글 목록 조회
$commentSql = "
    SELECT 
        c.comment_id,
        c.comment_content,
        c.comment_time,
        ui.user_name
    FROM comment c
    LEFT JOIN user_info ui ON c.user_info_id = ui.id
    WHERE c.notice_board_number_id = ?
    ORDER BY c.comment_time ASC
";

$commentStmt = mysqli_prepare($conn, $commentSql);
mysqli_stmt_bind_param($commentStmt, "i", $postId);
mysqli_stmt_execute($commentStmt);
$commentResult = mysqli_stmt_get_result($commentStmt);

$comments = [];
if ($commentResult) {
    while ($row = mysqli_fetch_array($commentResult)) {
        $comments[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>게시글 상세보기</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="/main/css/fonts.css">
</head>
<body>

<div class="post-detail-container">
  <!-- 상단 목록으로 버튼 -->
  <div class="action-buttons top">
    <button class="btn-back" onclick="goBackToList()">목록으로</button>
  </div>

  <!-- 게시글 상세 내용 -->
  <div id="post-content">
    <h2 class="detail-title"><?= htmlspecialchars($post['title']) ?></h2>
    <div class="detail-meta">
      <span class="nickname"><?= htmlspecialchars($post['user_name'] ?? '알 수 없음') ?></span> · <?= date('Y-m-d H:i', strtotime($post['date_time'])) ?>
    </div>
    <p class="detail-content"><?= nl2br(htmlspecialchars($post['content'])) ?></p>

    <div class="detail-actions">
      <span class="material-icons">visibility</span> <?= intval($post['views']) ?>
      
      <!-- 좋아요 버튼 (클릭 가능) -->
      <?php if ($isLoggedIn): ?>
        <span class="like-btn <?= $isLiked ? 'liked' : '' ?>" 
              onclick="toggleLike(<?= $postId ?>)" 
              style="cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
          <span class="material-icons" id="like-icon"><?= $isLiked ? 'favorite' : 'favorite_border' ?></span>
          <span id="like-count"><?= intval($post['like']) ?></span>
        </span>
      <?php else: ?>
        <span style="display: inline-flex; align-items: center; gap: 4px;">
          <span class="material-icons">favorite_border</span>
          <?= intval($post['like']) ?>
        </span>
      <?php endif; ?>
      
      <span class="material-icons">chat_bubble</span> <?= count($comments) ?>
    </div>
  </div>

  <!-- 댓글 목록 -->
  <div class="comment-section">
    <h3 class="comment-title">댓글 <?= count($comments) ?>개</h3>
    
    <?php if ($isLoggedIn): ?>
      <!-- 로그인 상태: 댓글 작성 가능 -->
      <div class="comment-input-box">
        <textarea id="comment-input" 
                  placeholder="댓글을 입력하세요..." 
                  rows="3"></textarea>
        <button id="comment-submit" onclick="submitComment()">
          댓글 작성
        </button>
      </div>
    <?php else: ?>
      <!-- 비로그인: 안내 메시지 -->
      <div class="comment-login-notice">
        댓글을 작성하려면 로그인이 필요합니다.
      </div>
    <?php endif; ?>
    
    <div id="comment-list">
      <?php if (empty($comments)): ?>
        <div class="comment-empty">아직 댓글이 없습니다.</div>
      <?php else: ?>
        <?php foreach ($comments as $comment): ?>
          <div class="comment-item">
            <span class="comment-nick"><?= htmlspecialchars($comment['user_name'] ?? '알 수 없음') ?></span>
            <span class="comment-text"><?= htmlspecialchars($comment['comment_content']) ?></span>
            <span class="comment-time"><?= date('Y-m-d H:i', strtotime($comment['comment_time'])) ?></span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- 목록으로 버튼 -->
  <div class="action-buttons">
    <button class="btn-back" onclick="goBackToList()">목록으로</button>
  </div>
</div>

<script>
  // 목록으로 돌아가기
  function goBackToList() {
    if (typeof loadPage === 'function') {
      loadPage('./pages/board/board.php');
    } else {
      window.location.href = './board.php';
    }
  }
  
  // 좋아요 토글
  function toggleLike(postId) {
    <?php if (!$isLoggedIn): ?>
      alert('로그인이 필요합니다.');
      return;
    <?php endif; ?>
    
    fetch('/main/pages/board/api/like_toggle.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'post_id=' + postId
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // 좋아요 아이콘 토글
        const icon = document.getElementById('like-icon');
        const likeBtn = document.querySelector('.like-btn');
        const likeCount = document.getElementById('like-count');
        
        if (data.action === 'liked') {
          icon.textContent = 'favorite';
          likeBtn.classList.add('liked');
        } else {
          icon.textContent = 'favorite_border';
          likeBtn.classList.remove('liked');
        }
        
        // 좋아요 수 업데이트
        likeCount.textContent = data.likeCount;
      } else {
        alert(data.message || '좋아요 처리에 실패했습니다.');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('좋아요 처리 중 오류가 발생했습니다.');
    });
  }
  
  // 댓글 작성
  function submitComment() {
    const content = document.getElementById('comment-input').value.trim();
    
    if (content === '') {
      alert('댓글 내용을 입력하세요.');
      return;
    }
    
    if (content.length > 255) {
      alert('댓글은 255자 이내로 작성해주세요.');
      return;
    }
    
    fetch('/main/pages/board/api/comment_add.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'post_id=<?= $postId ?>&content=' + encodeURIComponent(content)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // 댓글 입력창 초기화
        document.getElementById('comment-input').value = '';
        
        // 페이지 새로고침 (댓글 목록 갱신)
        if (typeof loadPage === 'function') {
          loadPage('./pages/board/board_detail.php?id=<?= $postId ?>');
        } else {
          location.reload();
        }
      } else {
        alert(data.message || '댓글 작성에 실패했습니다.');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('댓글 작성 중 오류가 발생했습니다.');
    });
  }
</script>

<?php
// 데이터베이스 연결 종료
mysqli_close($conn);
?>
</body>
</html>
