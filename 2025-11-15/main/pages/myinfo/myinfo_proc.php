<?php
session_start();

// 로그인 체크
if (!isset($_SESSION['id'])) {
    echo "<script>
    alert('로그인이 필요한 서비스입니다.');
    window.close();
    </script>";
    exit;
}

// POST 요청 검증
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>
    alert('잘못된 접근입니다.');
    history.back();
    </script>";
    exit;
}

// DB 연결
include '../../db/db_conn.php';

// POST 데이터 가져오기
$user_id = $_SESSION['id'];
$password = trim($_POST['password'] ?? '');
$phone_number = trim($_POST['phone_number'] ?? '');
$email = trim($_POST['email'] ?? '');

// 필수 필드 검증 (비밀번호는 선택)
if (empty($phone_number)) {
    echo "<script>
    alert('핸드폰 번호를 입력해주세요.');
    history.back();
    </script>";
    exit;
}

if (empty($email)) {
    echo "<script>
    alert('이메일을 입력해주세요.');
    history.back();
    </script>";
    exit;
}

// 비밀번호 형식 검증 (입력한 경우에만)
if (!empty($password)) {
    $hasLowerCase = preg_match('/[a-z]/', $password);
    $hasUpperCase = preg_match('/[A-Z]/', $password);
    $hasNumber = preg_match('/\d/', $password);
    $hasSpecialChar = preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'\"\\|,.<>\/?]/', $password);
    $isValidLength = strlen($password) >= 7;
    
    if (!($hasLowerCase && $hasUpperCase && $hasNumber && $hasSpecialChar && $isValidLength)) {
        echo "<script>
        alert('비밀번호는 영문 대소문자, 숫자, 특수문자를 모두 포함하여 7자 이상이어야 합니다.');
        history.back();
        </script>";
        exit;
    }
}

// DB 업데이트
if (!empty($password)) {
    // 비밀번호도 함께 수정
    $sql = "UPDATE user_info SET password = ?, phone_number = ?, email = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $password, $phone_number, $email, $user_id);
} else {
    // 비밀번호 제외하고 수정
    $sql = "UPDATE user_info SET phone_number = ?, email = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $phone_number, $email, $user_id);
}

if (mysqli_stmt_execute($stmt)) {
    echo "<script>
    alert('정보가 성공적으로 수정되었습니다.');
    window.close();
    if (window.opener) {
        window.opener.location.reload();
    }
    </script>";
} else {
    echo "<script>
    alert('정보 수정에 실패했습니다. 다시 시도해주세요.');
    history.back();
    </script>";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
