<?php
$host = "localhost";
$user = "root";        // root 사용자 (안정적)
$pw = "";              // XAMPP 기본 root 비밀번호
$dbname = "dugout";

$conn = mysqli_connect($host, $user, $pw, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// UTF8 설정
mysqli_set_charset($conn, "utf8mb4");
?>