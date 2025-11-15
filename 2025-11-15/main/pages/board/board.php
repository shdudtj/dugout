<?php
// 데이터베이스 연결
include '../../db/db_conn.php';

// 현재 페이지 번호 가져오기 (기본값: 1)
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$postsPerPage = 10;
$offset = ($currentPage - 1) * $postsPerPage;

// 검색어 가져오기
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// 총 게시글 수 조회 (검색 조건 적용)
$totalPosts = 0;
if (!empty($searchQuery)) {
    $countSql = "SELECT COUNT(*) as total FROM notice_board WHERE title LIKE ?";
    $countStmt = mysqli_prepare($conn, $countSql);
    $searchParam = '%' . $searchQuery . '%';
    mysqli_stmt_bind_param($countStmt, "s", $searchParam);
    mysqli_stmt_execute($countStmt);
    $countResult = mysqli_stmt_get_result($countStmt);
    if ($countResult) {
        $countRow = mysqli_fetch_array($countResult);
        $totalPosts = $countRow['total'];
    }
    mysqli_stmt_close($countStmt);
} else {
    $countSql = "SELECT COUNT(*) as total FROM notice_board";
    $countResult = mysqli_query($conn, $countSql);
    if ($countResult) {
        $countRow = mysqli_fetch_array($countResult);
        $totalPosts = $countRow['total'];
    }
}

// 게시글 목록 조회 (사용자명 포함, 댓글 수 집계, 검색 조건 적용)
$posts = [];
if (!empty($searchQuery)) {
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
        WHERE nb.title LIKE ?
        GROUP BY nb.number_id
        ORDER BY nb.date_time DESC
        LIMIT ?, ?
    ";
    $stmt = mysqli_prepare($conn, $sql);
    $searchParam = '%' . $searchQuery . '%';
    mysqli_stmt_bind_param($stmt, "sii", $searchParam, $offset, $postsPerPage);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $posts[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
} else {
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
    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $posts[] = $row;
        }
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

<?php if (!empty($searchQuery)): ?>
  <div class="search-status">
    <span class="search-info">
      <strong>"<?= htmlspecialchars($searchQuery) ?>"</strong> 검색 결과 (총 <?= $totalPosts ?>개)
    </span>
    <button class="btn-reset-search" onclick="resetSearch()" style="margin-left: 12px; padding: 4px 12px; background: #f0f0f0; border: 1px solid #ccc; border-radius: 6px; cursor: pointer; font-size: 13px;">검색 초기화</button>
  </div>
<?php endif; ?>

<?php if (empty($posts)): ?>
  <div class="no-posts">
    <p>게시글이 없습니다.</p>
  </div>
<?php else: ?>
  <?php foreach ($posts as $post): ?>
  <div class="post-card" data-post-id="<?= $post['number_id'] ?>">
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
      // 검색어가 있으면 URL에 포함
      $paginationUrl = !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '';
      echo renderPagination($currentPage, $totalPages, $paginationUrl, [
        'show_info' => true,
        'prev_text' => '&lsaquo;',
        'next_text' => '&rsaquo;',
        'class_prefix' => 'pagination'
      ]);
    }
  ?>

  <script>
    // 즉시 실행 함수로 게시글 클릭 이벤트 추가
    (function() {
      const postCards = document.querySelectorAll('.post-card');
      postCards.forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', function() {
          const postId = this.getAttribute('data-post-id');
          if (postId && typeof loadPage === 'function') {
            loadPage('./pages/board/board_detail.php?id=' + postId);
          }
        });
      });
    })();

    // 페이지 변경 함수 (AJAX로 게시글 목록 새로고침)
    function changePage(page) {
      console.log('페이지 변경:', page);
      
      // 검색어 파라미터 유지
      const urlParams = new URLSearchParams(window.location.search);
      const searchQuery = urlParams.get('search');
      let url = './pages/board/board.php?page=' + page;
      
      if (searchQuery) {
        url += '&search=' + encodeURIComponent(searchQuery);
      }
      
      // 게시판 페이지 다시 로드 (main.js의 loadPage 함수 활용)
      if (typeof loadPage === 'function') {
        loadPage(url);
      } else {
        // fallback: 페이지 새로고침
        window.location.href = url;
      }
    }
    
    // 검색 초기화 함수
    function resetSearch() {
      if (typeof loadPage === 'function') {
        loadPage('./pages/board/board.php');
      } else {
        window.location.href = './pages/board/board.php';
      }
    }
  </script>

<?php
// 데이터베이스 연결 종료
mysqli_close($conn);
?>
</body>
</html>