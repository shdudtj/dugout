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

// DB 연결
include '../../db/db_conn.php';

// 사용자 정보 조회
$user_id = $_SESSION['id'];
$sql = "SELECT * FROM user_info WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo "<script>
    alert('사용자 정보를 찾을 수 없습니다.');
    window.close();
    </script>";
    exit;
}

// 팀 매핑
$teams = [
    1 => 'KIA 타이거즈',
    2 => '삼성 라이온즈',
    3 => 'LG 트윈스',
    4 => '두산 베어스',
    5 => 'KT 위즈',
    6 => 'SSG 랜더스',
    7 => '롯데 자이언츠',
    8 => '한화 이글스',
    9 => 'NC 다이노스',
    10 => '키움 히어로즈'
];

$team_name = $teams[$user['team_choice']] ?? '알 수 없음';

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>내 정보 수정</title>
  <link rel="stylesheet" href="/main/css/fonts.css" />
  <link rel="stylesheet" href="/main/css/signup.css" />
  <style>
    .icon {
      cursor: pointer;
      user-select: none;
      padding: 8px;
      border-radius: 4px;
      transition: background-color 0.2s ease;
    }
    
    .icon:hover {
      background-color: #f0f0f0;
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const passwordInput = document.getElementById('password');
      const toggleIcon = document.querySelector('.icon');
      
      // 비밀번호 가시성 토글
      toggleIcon.addEventListener('click', function() {
        if (passwordInput.type === 'password') {
          passwordInput.type = 'text';
          toggleIcon.textContent = '🙈';
        } else {
          passwordInput.type = 'password';
          toggleIcon.textContent = '👁️‍🗨️';
        }
      });
      
      // 비밀번호 형식 검증 함수
      function validatePassword(password) {
        if (password === '') return true; // 비밀번호 비어있으면 수정 안 함
        
        // 영문 대소문자, 숫자, 특수문자를 모두 포함해 7자 이상
        const hasLowerCase = /[a-z]/.test(password);
        const hasUpperCase = /[A-Z]/.test(password);
        const hasNumber = /\d/.test(password);
        const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
        const isValidLength = password.length >= 7;
        
        return hasLowerCase && hasUpperCase && hasNumber && hasSpecialChar && isValidLength;
      }
      
      // 비밀번호 입력 시 실시간 검증
      passwordInput.addEventListener('input', function() {
        const password = this.value;
        const isValid = validatePassword(password);
        
        if (password === '') {
          this.style.borderColor = '';
          return;
        }
        
        if (isValid) {
          this.style.borderColor = '#28a745';
        } else {
          this.style.borderColor = '#dc3545';
        }
      });
      
      // 폼 제출 시 검증
      document.querySelector('.signup-form').addEventListener('submit', function(e) {
        const password = passwordInput.value;
        
        // 비밀번호를 입력했다면 형식 검증
        if (password !== '' && !validatePassword(password)) {
          e.preventDefault();
          alert('비밀번호는 영문 대소문자, 숫자, 특수문자를 모두 포함하여 7자 이상이어야 합니다.');
          return false;
        }
      });
    });
  </script>
</head>
<body>
  <div class="signup-container">
    <h1 class="logo">DUGOUT</h1>

    <form class="signup-form" action="myinfo_proc.php" method="post">
      <label>닉네임 ✖ 변경 불가
        <div class="input-wrap">
          <input type="text" value="<?= htmlspecialchars($user['user_name']) ?>" disabled />
        </div>
      </label>

      <label>아이디 ✖ 변경 불가
        <div class="input-wrap">
          <input type="text" value="<?= htmlspecialchars($user['id']) ?>" disabled />
        </div>
      </label>

      <label>비밀번호
        <div class="input-wrap">
          <input type="password" name="password" id="password" placeholder="영문 대소문자, 숫자 및 특수문자 포함 7자 이상" />
          <span class="icon">👁️‍🗨️</span>
        </div>
      </label>

      <label>핸드폰 번호
        <div class="input-wrap">
          <input type="text" name="phone_number" id="phone_number" value="<?= htmlspecialchars($user['phone_number']) ?>" placeholder="번호만 입력" required />
        </div>
      </label>

      <label>이메일
        <div class="input-wrap">
          <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" placeholder="이메일 입력" required />
        </div>
      </label>

      <label>구단 선택 ✖ 변경 불가
        <div class="input-wrap">
          <input type="text" value="<?= htmlspecialchars($team_name) ?>" disabled />
        </div>
      </label>

      <button type="submit" class="submit-btn">정보변경</button>
    </form>
  </div>
</body>
</html>
