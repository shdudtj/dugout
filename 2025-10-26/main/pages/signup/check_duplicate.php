<?php
session_start();
include '../../db/db_conn.php';

// POST로 받은 데이터
$type = isset($_POST['type']) ? $_POST['type'] : '';
$value = isset($_POST['value']) ? trim($_POST['value']) : '';

// 입력값 검증
if (empty($type) || empty($value)) {
    echo json_encode(['error' => '잘못된 요청입니다.']);
    exit;
}

// SQL 쿼리 (prepared statement 사용)
if ($type === 'nickname') {
    $sql = "SELECT COUNT(*) FROM user_info WHERE user_name = ?";
} else if ($type === 'id') {
    $sql = "SELECT COUNT(*) FROM user_info WHERE id = ?";
} else {
    echo json_encode(['error' => '잘못된 타입입니다.']);
    exit;
}

// 중복 확인 실행
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    echo json_encode(['error' => 'SQL 준비 실패: ' . mysqli_error($conn)]);
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $value);
if (!mysqli_stmt_execute($stmt)) {
    echo json_encode(['error' => 'SQL 실행 실패: ' . mysqli_stmt_error($stmt)]);
    exit;
}

mysqli_stmt_bind_result($stmt, $count);
mysqli_stmt_fetch($stmt);

// JSON 응답 반환
if ($count > 0) {
    echo json_encode([
        'exists' => true,
        'message' => $value . '는 이미 있는 ' . ($type === 'nickname' ? '닉네임' : '아이디') . '입니다.'
    ]);
} else {
    echo json_encode([
        'exists' => false,
        'message' => $value . '는 사용 가능한 ' . ($type === 'nickname' ? '닉네임' : '아이디') . '입니다.'
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
