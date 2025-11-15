<?php
session_start();

// 로그인 체크
if (!isset($_SESSION['id'])) {
    echo '<div style="text-align: center; padding: 60px 20px;">';
    echo '<span class="material-icons" style="font-size: 64px; color: #ccc;">lock</span>';
    echo '<p style="font-size: 18px; color: #666; margin-top: 20px;">로그인이 필요한 서비스입니다.</p>';
    echo '</div>';
    exit;
}

$userId = $_SESSION['id'];
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
$type = isset($_GET['type']) ? $_GET['type'] : 'posts'; // posts, comments, liked-posts
$itemsPerPage = 10;

include '../../db/db_conn.php';
include '../../library/pagination.php';

// 타입별 쿼리 및 제목 설정
$title = '';
$countSql = '';
$dataSql = '';

switch ($type) {
    case 'posts':
        $title = '게시글';
        $countSql = "SELECT COUNT(*) as total FROM notice_board WHERE user_info_id = ?";
        $dataSql = "SELECT number_id, title, content, date_time, views, `like` 
                    FROM notice_board 
                    WHERE user_info_id = ? 
                    ORDER BY date_time DESC 
                    LIMIT ?, ?";
        break;
    
    case 'comments':
        $title = '댓글';
        $countSql = "SELECT COUNT(*) as total FROM comment WHERE user_info_id = ?";
        $dataSql = "SELECT c.comment_id, c.comment_content, c.comment_time, 
                           nb.number_id, nb.title
                    FROM comment c
                    LEFT JOIN notice_board nb ON c.notice_board_number_id = nb.number_id
                    WHERE c.user_info_id = ?
                    ORDER BY c.comment_time DESC 
                    LIMIT ?, ?";
        break;
    
    case 'liked-posts':
        $title = '좋아요 누른 게시글';
        $countSql = "SELECT COUNT(*) as total FROM post_likes WHERE user_id = ?";
        $dataSql = "SELECT nb.number_id, nb.title, nb.content, nb.date_time, nb.views, nb.`like`,
                           ui.user_name
                    FROM notice_board nb
                    INNER JOIN post_likes pl ON nb.number_id = pl.post_id
                    LEFT JOIN user_info ui ON nb.user_info_id = ui.id
                    WHERE pl.user_id = ?
                    ORDER BY pl.created_at DESC 
                    LIMIT ?, ?";
        break;
}

// 총 개수 조회
$stmt = mysqli_prepare($conn, $countSql);
mysqli_stmt_bind_param($stmt, "s", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$totalItems = mysqli_fetch_array($result)['total'];
$totalPages = $totalItems > 0 ? ceil($totalItems / $itemsPerPage) : 1;
mysqli_stmt_close($stmt);

// 데이터 조회
$items = [];
if ($totalItems > 0) {
    $offset = ($currentPage - 1) * $itemsPerPage;
    $stmt = mysqli_prepare($conn, $dataSql);
    mysqli_stmt_bind_param($stmt, "sii", $userId, $offset, $itemsPerPage);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_array($result)) {
        $items[] = $row;
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!-- 나의 활동 페이지 -->
<div class="activity-container">
    <div class="activity-header">
      <button class="activity-tab <?= $type === 'posts' ? 'active' : '' ?>" data-type="posts">게시글</button>
      <button class="activity-tab <?= $type === 'comments' ? 'active' : '' ?>" data-type="comments">댓글</button>
      <button class="activity-tab <?= $type === 'liked-posts' ? 'active' : '' ?>" data-type="liked-posts">좋아요 누른 게시글</button>
    </div>
  
    <p class="activity-note">* 항목을 클릭하면 해당 게시글로 이동합니다.</p>
  
    <div class="activity-content">
      <div class="activity-box" id="activity-box">
        <?php if (empty($items)): ?>
          <div class="activity-empty">
            <span class="material-icons" style="font-size: 48px; color: #ccc;">inbox</span>
            <p><?= $title ?> 내역이 없습니다.</p>
          </div>
        <?php else: ?>
          <?php foreach ($items as $item): ?>
            <?php if ($type === 'posts' || $type === 'liked-posts'): ?>
              <!-- 게시글 카드 -->
              <div class="activity-item post-card" data-post-id="<?= $item['number_id'] ?>">
                <div class="avatar"></div>
                <div>
                  <div class="post-title"><?= htmlspecialchars($item['title']) ?></div>
                  <div class="post-meta">
                    <?php if ($type === 'liked-posts'): ?>
                      <?= htmlspecialchars($item['user_name'] ?? '알 수 없음') ?> · 
                    <?php endif; ?>
                    <?= date('Y-m-d', strtotime($item['date_time'])) ?>
                  </div>
                </div>
                <div class="post-right">
                  <div class="counters">
                    <span class="material-icons">visibility</span> <?= intval($item['views']) ?>
                    <span class="material-icons">favorite</span> <?= intval($item['like']) ?>
                  </div>
                </div>
              </div>
            <?php elseif ($type === 'comments'): ?>
              <!-- 댓글 카드 -->
              <div class="activity-item comment-card" data-post-id="<?= $item['number_id'] ?>">
                <div class="comment-icon-box">
                  <span class="material-icons">chat_bubble</span>
                </div>
                <div class="comment-detail">
                  <div class="comment-post-title"><?= htmlspecialchars($item['title']) ?></div>
                  <div class="comment-content"><?= htmlspecialchars($item['comment_content']) ?></div>
                  <div class="comment-time"><?= date('Y-m-d H:i', strtotime($item['comment_time'])) ?></div>
                </div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  
    <!-- 페이지네이션 -->
    <?= renderPagination($currentPage, $totalPages) ?>
  </div>
  
  <!-- JS 파일 연결 -->
  <script src="/main/pages/myactivity/myactivity.js" defer></script>