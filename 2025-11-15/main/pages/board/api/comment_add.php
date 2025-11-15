<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 로그인 체크
if (!isset($_SESSION['id'])) {
    echo json_encode([
        'success' => false,
        'message' => '로그인이 필요합니다.'
    ]);
    exit;
}

// POST 요청 체크
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => '잘못된 요청입니다.'
    ]);
    exit;
}

// DB 연결
include '../../../db/db_conn.php';

// 파라미터 받기
$postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$userId = $_SESSION['id'];

// 유효성 검증
if ($postId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => '잘못된 게시글 ID입니다.'
    ]);
    exit;
}

if (empty($content)) {
    echo json_encode([
        'success' => false,
        'message' => '댓글 내용을 입력하세요.'
    ]);
    exit;
}

if (strlen($content) > 255) {
    echo json_encode([
        'success' => false,
        'message' => '댓글은 255자 이내로 작성해주세요.'
    ]);
    exit;
}

// 댓글 INSERT
$sql = "INSERT INTO comment (comment_content, notice_board_number_id, user_info_id) 
        VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sis", $content, $postId, $userId);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'success' => true,
        'message' => '댓글이 작성되었습니다.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => '댓글 작성에 실패했습니다.'
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
