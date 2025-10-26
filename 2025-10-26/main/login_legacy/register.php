<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="../css/main.css">
    </head>
    <body>
        <?php
        session_start();
        if(isset($_SESSION['username'])){
            echo "<script>
            alert('이미 로그인 되어있습니다.');
            location.href = '../main/main.php';
            </script>";
        }else{?>
            <div id="register-wrap">
                <div>
                    <form action="login_proc.php" method="post" name="registerform" id="registerform">
                    <p><input type="text" name="name" id="username" placeholder="유저명(닉네임)"></p>
                    <p><input type="text" name="id" id="id" placeholder="아이디"></p>
                    <p><input type="password" name="pw" id="pw" placeholder="비밀번호"></p>
                    <p><input type="password" name="pw_check" id="pw_check" placeholder="비밀번호 확인"></p>
                    <p><input type="submit" value="회원가입"></p>
                    </form>
                </div>
            </div>
        </body>
        </html>
        <?php 
    } 
    ?>
        
        
