<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>로그인</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="/main/css/fonts.css">
  <link rel="stylesheet" href="/main/css/login.css" />
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const loginTab = document.querySelector('.tab.login');
      const signupTab = document.querySelector('.tab.signup');

      signupTab.addEventListener('click', function () {
        window.location.href = '../signup/signup.php';
      });

      loginTab.addEventListener('click', function () {
        loginTab.classList.add('active');
        signupTab.classList.remove('active');
      });
    });
  </script>
</head>
<body>
  <?php
    session_start();
    if(isset($_SESSION['id'])){
        echo "<script>
        alert('이미 로그인 되어있습니다.');
        window.close();
        </script>";
    }else{?>
  <div class="login-container">
    <h1 class="logo">DUGOUT</h1>

    <div class="tab-buttons">
      <button class="tab login active">로그인</button>
      <button class="tab signup">회원가입</button>
    </div>

    <form class="login-form" action="login_proc.php" method="post">
      <label>아이디
        <div class="input-wrap">
          <input type="text" name="id" id="id" placeholder="아이디" required/>
        </div>
      </label>

      <label>비밀번호
        <div class="input-wrap">
          <input type="password" name="pw" id="pw" placeholder="숫자 및 특수문자 포함 7자 이상" required/>
          <span class="icon">👁️‍🗨️</span>
        </div>
      </label>

      <div class="find-links">
        <a href="#">아이디 찾기</a> | <a href="#">비밀번호 찾기</a>
      </div>

      <div class="social-icons">
        <img src="https://img.icons8.com/ios-glyphs/30/instagram-new.png" alt="Instagram" />
        <img src="https://img.icons8.com/ios-filled/30/facebook-new.png" alt="Facebook" />
      </div>

      <button type="submit" class="submit-btn">로그인</button>
    </form>
  </div>
</body>
</html>
<?php }?>