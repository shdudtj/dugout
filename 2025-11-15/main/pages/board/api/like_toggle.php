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
$userId = $_SESSION['id'];

if ($postId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => '잘못된 게시글 ID입니다.'
    ]);
    exit;
}

// 좋아요 여부 확인
$checkSql = "SELECT COUNT(*) as count FROM post_likes WHERE user_id = ? AND post_id = ?";
$checkStmt = mysqli_prepare($conn, $checkSql);
mysqli_stmt_bind_param($checkStmt, "si", $userId, $postId);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);
$checkRow = mysqli_fetch_array($checkResult);
$isLiked = ($checkRow['count'] > 0);
mysqli_stmt_close($checkStmt);

$action = '';

if ($isLiked) {
    // 좋아요 취소
    $deleteSql = "DELETE FROM post_likes WHERE user_id = ? AND post_id = ?";
    $deleteStmt = mysqli_prepare($conn, $deleteSql);
    mysqli_stmt_bind_param($deleteStmt, "si", $userId, $postId);
    
    if (!mysqli_stmt_execute($deleteStmt)) {
        echo json_encode([
            'success' => false,
            'message' => '좋아요 취소에 실패했습니다.'
        ]);
        mysqli_stmt_close($deleteStmt);
        mysqli_close($conn);
        exit;
    }
    mysqli_stmt_close($deleteStmt);
    $action = 'unliked';
} else {
    // 좋아요 추가
    $insertSql = "INSERT INTO post_likes (user_id, post_id) VALUES (?, ?)";
    $insertStmt = mysqli_prepare($conn, $insertSql);
    mysqli_stmt_bind_param($insertStmt, "si", $userId, $postId);
    
    if (!mysqli_stmt_execute($insertStmt)) {
        echo json_encode([
            'success' => false,
            'message' => '좋아요 추가에 실패했습니다.'
        ]);
        mysqli_stmt_close($insertStmt);
        mysqli_close($conn);
        exit;
    }
    mysqli_stmt_close($insertStmt);
    $action = 'liked';
}

// notice_board.like 필드 동기화
$updateSql = "UPDATE notice_board SET `like` = (SELECT COUNT(*) FROM post_likes WHERE post_id = ?) WHERE number_id = ?";
$updateStmt = mysqli_prepare($conn, $updateSql);
mysqli_stmt_bind_param($updateStmt, "ii", $postId, $postId);
mysqli_stmt_execute($updateStmt);
mysqli_stmt_close($updateStmt);

// 현재 좋아요 수 조회
$countSql = "SELECT `like` FROM notice_board WHERE number_id = ?";
$countStmt = mysqli_prepare($conn, $countSql);
mysqli_stmt_bind_param($countStmt, "i", $postId);
mysqli_stmt_execute($countStmt);
$countResult = mysqli_stmt_get_result($countStmt);
$countRow = mysqli_fetch_array($countResult);
$likeCount = intval($countRow['like']);
mysqli_stmt_close($countStmt);

// 응답
echo json_encode([
    'success' => true,
    'action' => $action,
    'likeCount' => $likeCount
]);

mysqli_close($conn);
?>
