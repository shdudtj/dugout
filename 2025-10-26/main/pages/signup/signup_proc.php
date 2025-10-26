<?php
session_start();

include '../../db/db_conn.php';

// POST로 받은 회원가입 데이터
$id = $_POST['id'];
$password = $_POST['pw'];
$user_name = $_POST['user_name'];
$phone_number = $_POST['phone_number'];
$email = $_POST['email'];
$team_choice = $_POST['team_choice'];
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];

// user_info 테이블에 데이터 삽입
$sql = "INSERT INTO user_info (id, password, user_name, phone_number, email, team_choice, first_name, last_name) 
        VALUES ('$id', '$password', '$user_name', '$phone_number', '$email', '$team_choice', '$first_name', '$last_name')";

$result = mysqli_query($conn, $sql);

if ($result) {
    // 회원가입 성공
    $_SESSION['id'] = $id;  // 세션에 아이디 저장
    
    echo "<script>
    alert('회원가입 성공');
    window.close();
    if (window.opener) {
        window.opener.location.reload();
    }
    </script>";
} else {
    // 회원가입 실패
    echo "<script>
    alert('회원가입에 실패했습니다. 다시 시도해주세요.');
    history.back();
    </script>";
}

mysqli_close($conn);
?>