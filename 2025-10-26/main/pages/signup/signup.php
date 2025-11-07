<?php
session_start();

if(isset($_SESSION['id'])){
    echo "<script>
    alert('이미 로그인 되어있습니다.');
    window.close();
    </script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>회원가입</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="/main/css/fonts.css" />
  <link rel="stylesheet" href="/main/css/signup.css" />
  <style>
    .submit-btn:disabled {
      opacity: 0.5 !important;
      cursor: not-allowed !important;
      background-color: #cccccc !important;
    }
    
    .input-readonly {
      background-color: #f0f0f0 !important;
      cursor: not-allowed;
    }
    
    .btn-confirmed {
      background-color: #28a745 !important;
      color: white !important;
    }
    
    .check-btn {
      transition: all 0.3s ease;
    }
    
    .check-btn:disabled {
      cursor: not-allowed;
    }
    
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
    document.addEventListener('DOMContentLoaded', function () {
      const loginTab = document.querySelector('.tab.login');
      const signupTab = document.querySelector('.tab.signup');

      loginTab.addEventListener('click', function () {
        window.location.href = '../login/login.php';
      });

      signupTab.addEventListener('click', function () {
        signupTab.classList.add('active');
        loginTab.classList.remove('active');
      });

      // 중복확인 로직
      let nicknameChecked = false;
      let idChecked = false;
      
      // 회원가입 버튼 초기 비활성화
      const submitBtn = document.querySelector('.submit-btn');
      submitBtn.disabled = true;
      submitBtn.style.opacity = '0.5';
      submitBtn.style.backgroundColor = '#cccccc';
      
      // 닉네임 중복확인 버튼
      const nicknameInput = document.getElementById('user_name');
      const nicknameBtn = nicknameInput.nextElementSibling;
      nicknameBtn.addEventListener('click', function() {
        const userName = nicknameInput.value.trim();
        
        if (!userName) {
          alert('닉네임을 입력해주세요.');
          return;
        }
        
        // AJAX로 중복확인 요청
        fetch('check_duplicate.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'type=nickname&value=' + encodeURIComponent(userName)
        })
        .then(response => response.json())
        .then(data => {
          if (data.exists) {
            alert(`${userName}는 이미 있는 닉네임입니다.`);
            nicknameChecked = false;
          } else {
            if (confirm(`${userName}는 사용 가능한 닉네임입니다. 사용하시겠습니까?`)) {
              // input 완전 잠금 및 값 고정
              nicknameInput.readOnly = true;
              nicknameInput.style.backgroundColor = '#f0f0f0';
              nicknameInput.style.cursor = 'not-allowed';
              nicknameInput.setAttribute('data-confirmed-value', userName);
              
              // 버튼 변경
              nicknameBtn.textContent = '확인완료';
              nicknameBtn.disabled = true;
              nicknameBtn.style.backgroundColor = '#28a745';
              nicknameBtn.style.color = 'white';
              
              nicknameChecked = true;
              checkSubmitButton();
            }
          }
        })
        .catch(error => {
          alert('중복확인 중 오류가 발생했습니다.');
        });
      });
      
      // 아이디 중복확인 버튼
      const idInput = document.getElementById('id');
      const idBtn = idInput.nextElementSibling;
      idBtn.addEventListener('click', function() {
        const userId = idInput.value.trim();
        
        if (!userId) {
          alert('아이디를 입력해주세요.');
          return;
        }
        
        fetch('check_duplicate.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'type=id&value=' + encodeURIComponent(userId)
        })
        .then(response => response.json())
        .then(data => {
          if (data.exists) {
            alert(`${userId}는 이미 있는 아이디입니다.`);
            idChecked = false;
          } else {
            if (confirm(`${userId}는 사용 가능한 아이디입니다. 사용하시겠습니까?`)) {
              // input 완전 잠금 및 값 고정
              idInput.readOnly = true;
              idInput.style.backgroundColor = '#f0f0f0';
              idInput.style.cursor = 'not-allowed';
              idInput.setAttribute('data-confirmed-value', userId);
              
              // 버튼 변경
              idBtn.textContent = '확인완료';
              idBtn.disabled = true;
              idBtn.style.backgroundColor = '#28a745';
              idBtn.style.color = 'white';
              
              idChecked = true;
              checkSubmitButton();
            }
          }
        })
        .catch(error => {
          alert('중복확인 중 오류가 발생했습니다.');
        });
      });
      
      // 회원가입 버튼 활성화 체크 함수
      function checkSubmitButton() {
        if (nicknameChecked && idChecked) {
          submitBtn.disabled = false;
          submitBtn.style.opacity = '1';
          submitBtn.style.backgroundColor = '#007bff';
        }
      }
      
      // 비밀번호 가시성 토글 기능
      const passwordInput = document.getElementById('pw');
      const toggleIcon = document.querySelector('.icon');
      
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

      // submit 시 값 변조 검증 및 비밀번호 형식 검증
      document.querySelector('.signup-form').addEventListener('submit', function(e) {
        // 중복확인 완료 여부 체크
        if (!nicknameChecked || !idChecked) {
          e.preventDefault();
          alert('닉네임과 아이디 중복확인을 완료해주세요.');
          return false;
        }
        
        // 비밀번호 형식 검증
        const password = passwordInput.value;
        if (!validatePassword(password)) {
          e.preventDefault();
          alert('비밀번호는 영문 대소문자, 숫자, 특수문자를 모두 포함하여 7자 이상이어야 합니다.');
          return false;
        }
        
        // 값 변조 검증
        const nicknameConfirmed = nicknameInput.getAttribute('data-confirmed-value');
        const idConfirmed = idInput.getAttribute('data-confirmed-value');
        
        if (nicknameInput.value !== nicknameConfirmed) {
          e.preventDefault();
          alert('오류가 발생하였습니다. 다시 시도해주세요.');
          window.close();
          return false;
        }
        
        if (idInput.value !== idConfirmed) {
          e.preventDefault();
          alert('오류가 발생하였습니다. 다시 시도해주세요.');
          window.close();
          return false;
        }
      });
    });
  </script>
</head>
<body>
  <div class="signup-container">
    <h1 class="logo">DUGOUT</h1>

    <div class="tab-buttons">
      <button class="tab login">로그인</button>
      <button class="tab signup active">회원가입</button>
    </div>

    <form class="signup-form" action="signup_proc.php" method="post">
      <label>닉네임
        <div class="input-wrap">
          <input type="text" placeholder="추후 변경 불가" name="user_name" id="user_name" required/>
          <button type="button" class="check-btn">중복확인</button>
        </div>
      </label>

      <label>아이디
        <div class="input-wrap">
          <input type="text" placeholder="추후 변경 불가" name="id" id="id" required/>
          <button type="button" class="check-btn">중복확인</button>
        </div>
      </label>

      <label>비밀번호
        <div class="input-wrap">
          <input type="password" placeholder="영문 대소문자, 숫자 및 특수문자 포함 7자 이상" name="pw" id="pw" required/>
          <span class="icon">👁️‍🗨️</span>
        </div>
      </label>

      <label>이름
        <div class="input-wrap">
          <input type="text" placeholder="성" name="last_name" id="last_name" required/>
          <input type="text" placeholder="이름" name="first_name" id="first_name" required/>
        </div>
      </label>

      <label>핸드폰 번호
        <div class="input-wrap">
          <input type="text" placeholder="번호만 입력" name="phone_number" id="phone_number" required/>
        </div>
      </label>

      <label>이메일
        <div class="input-wrap">
          <input type="email" name="email" id="email" required/>
        </div>
      </label>

      <label>구단 선택
        <div class="input-wrap">
          <select name="team_choice" id="team_choice" required>
            <option disabled selected>추후 변경 불가</option>
            <option value="1">KIA 타이거즈</option>
            <option value="2">삼성 라이온즈</option>
            <option value="3">LG 트윈스</option>
            <option value="4">두산 베어스</option>
            <option value="5">KT 위즈</option>
            <option value="6">SSG 랜더스</option>
            <option value="7">롯데 자이언츠</option>
            <option value="8">한화 이글스</option>
            <option value="9">NC 다이노스</option>
            <option value="10">키움 히어로즈</option>
          </select>
        </div>
      </label>

      <div class="social-icons">
        <img src="https://img.icons8.com/ios-glyphs/30/instagram-new.png" alt="Instagram" />
        <img src="https://img.icons8.com/ios-filled/30/facebook-new.png" alt="Facebook" />
      </div>

      <button type="submit" class="submit-btn">회원가입</button>
    </form>
  </div>
</body>
</html>
