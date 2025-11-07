<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="../css/main.css">
    </head>
    <body>
        <?php
        session_start();
        if(isset($_SESSION['id'])){
            echo "<script>
            alert('이미 로그인 되어있습니다.');
            location.href = '../main/main.php';
            </script>";
        }else{?>
            <div id="login-wrap">
                <div>
                    <form action="login_proc.php" method="post" name="loginform" id="loginform">
                    <p><input type="text" name="id" id="id" placeholder="아이디"></p>
                    <p><input type="password" name="pw" id="pw" placeholder="비밀번호"></p>
                    <p><input type="submit" value="로그인"></p>
                    <p><a href="./register.php">회원가입</a></p>
                    </form>
                </div>
            </div>
        </body>
        </html>
        <?php 
    } 
    ?>
