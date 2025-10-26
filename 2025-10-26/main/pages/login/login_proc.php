<?php
    session_start();

    include '../../db/db_conn.php';
    $id = $_POST['id'];
    $pw = $_POST['pw'];

    // 아이디 존재 여부 검사 (구조에 맞게 수정 필요)
    $sql = "SELECT * FROM user_info WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    $num = mysqli_num_rows($result);

    if (!$num) {
        echo "<script>
        alert('아이디가 존재하지 않습니다.');
        history.back();
        </script>";
        exit;
    }else {
        $row = mysqli_fetch_array($result);
        $db_pw = $row['password'];

        if ($db_pw != $pw) { // 비밀번호 불일치
            echo "<script>
            alert('비밀번호가 일치하지 않습니다.');
            history.back();
            </script>";
            exit;
        }else { // 비밀번호 일치
            // 로그인 성공 - 세션 발급
            $_SESSION['id'] = $id;  // 세션에 아이디 저장
            
            echo "<script>
            window.close();
            if (window.opener) {
                window.opener.location.reload();
            }
            </script>";
        }
    }

mysqli_close($conn);
?>