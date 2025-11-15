<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 로그인 체크
if (!isset($_SESSION['id'])) {
    echo json_encode([
        'success' => false,
        'message' => '로그인이 필요한 서비스입니다.'
    ]);
    exit;
}

// POST 데이터 확인
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => '잘못된 접근입니다.'
    ]);
    exit;
}

// DB 연결
include '../../db/db_conn.php';

// POST 데이터 받기
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$user_id = $_SESSION['id'];

// 유효성 검증
if (empty($title)) {
    echo json_encode([
        'success' => false,
        'message' => '제목을 입력해주세요.'
    ]);
    mysqli_close($conn);
    exit;
}

if (empty($content)) {
    echo json_encode([
        'success' => false,
        'message' => '내용을 입력해주세요.'
    ]);
    mysqli_close($conn);
    exit;
}

// Prepared Statement로 게시글 등록
$sql = "INSERT INTO notice_board (title, content, user_info_id, date_time, `like`, views) 
        VALUES (?, ?, ?, NOW(), 0, 0)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => '게시글 등록에 실패했습니다. (준비 실패)'
    ]);
    mysqli_close($conn);
    exit;
}

// 파라미터 바인딩
mysqli_stmt_bind_param($stmt, "sss", $title, $content, $user_id);

// 쿼리 실행
if (mysqli_stmt_execute($stmt)) {
    // 성공
    echo json_encode([
        'success' => true,
        'message' => '게시글이 등록되었습니다.'
    ]);
} else {
    // 실패
    echo json_encode([
        'success' => false,
        'message' => '게시글 등록에 실패했습니다. (실행 실패)'
    ]);
}

// 리소스 정리
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
