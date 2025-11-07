<?php
// 데이터베이스 연결
include '../../db/db_conn.php';

// 현재 페이지 번호 가져오기 (기본값: 1)
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$postsPerPage = 10;
$offset = ($currentPage - 1) * $postsPerPage;

// 총 게시글 수 조회
$countSql = "SELECT COUNT(*) as total FROM notice_board";
$countResult = mysqli_query($conn, $countSql);
$totalPosts = 0;
if ($countResult) {
    $countRow = mysqli_fetch_array($countResult);
    $totalPosts = $countRow['total'];
}

// 게시글 목록 조회 (사용자명 포함, 댓글 수 집계)
$sql = "
    SELECT 
        nb.number_id,
        nb.title,
        nb.views,
        nb.like,
        nb.date_time,
        ui.user_name,
        COUNT(c.comment_id) as comment_count
    FROM notice_board nb
    LEFT JOIN user_info ui ON nb.user_info_id = ui.id
    LEFT JOIN comment c ON nb.number_id = c.notice_board_number_id
    GROUP BY nb.number_id
    ORDER BY nb.date_time DESC
    LIMIT $offset, $postsPerPage
";

$result = mysqli_query($conn, $sql);
$posts = [];
if ($result) {
    while ($row = mysqli_fetch_array($result)) {
        $posts[] = $row;
    }
}
?>

<!-- 게시글 목록 -->
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>board</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="/main/css/fonts.css">
</head>
<body>

<?php if (empty($posts)): ?>
  <div class="no-posts">
    <p>게시글이 없습니다.</p>
  </div>
<?php else: ?>
  <?php foreach ($posts as $post): ?>
  <div class="post-card">
    <div class="avatar"></div>
    <div>
      <div class="post-title"><?= htmlspecialchars($post['title']) ?></div>
      <div class="post-meta"><?= htmlspecialchars($post['user_name'] ?? '알 수 없음') ?> · <?= date('Y-m-d', strtotime($post['date_time'])) ?></div>
    </div>
    <div class="post-right">
      <div class="badges">
        <!-- TODO: 이미지 첨부 여부에 따라 표시 -->
        <div class="badge-photo" style="display: none;"><span class="material-icons">image</span></div>
      </div>
      <div class="counters" style="margin-top:6px;">
        <span class="material-icons">visibility</span> <?= intval($post['views']) ?>
        <span class="material-icons">favorite</span> <?= intval($post['like']) ?>
        <span class="material-icons">chat_bubble</span> <?= intval($post['comment_count']) ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>

  <!-- 게시글 페이지네이션 -->
  <?php 
    include '../../library/pagination.php';
    
    // 총 페이지 수 계산
    $totalPages = ceil($totalPosts / $postsPerPage);
    
    // 페이지네이션 표시 (게시글이 있을 때만)
    if ($totalPosts > 0) {
      echo renderPagination($currentPage, $totalPages, '', [
        'show_info' => true,
        'prev_text' => '&lsaquo;',
        'next_text' => '&rsaquo;',
        'class_prefix' => 'pagination'
      ]);
    }
  ?>

  <script>
    // 페이지 변경 함수 (AJAX로 게시글 목록 새로고침)
    function changePage(page) {
      console.log('페이지 변경:', page);
      
      // 게시판 페이지 다시 로드 (main.js의 loadPage 함수 활용)
      if (typeof loadPage === 'function') {
        loadPage('./pages/board/board.php?page=' + page);
      } else {
        // fallback: 페이지 새로고침
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('page', page);
        window.location.href = currentUrl.toString();
      }
    }
  </script>

<?php
// 데이터베이스 연결 종료
mysqli_close($conn);
?>
</body>
</html>