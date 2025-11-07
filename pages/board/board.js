/*
[임시 샘플 데이터] — 정적 서버 전용
실제 서버 연동 시 반드시 제거!!
*/
if (typeof SAMPLE_USER === "undefined") {
    var SAMPLE_USER = { id: 1, nickname: "홍길동" };
  
    var SAMPLE_POSTS = [
      {
        id: 1,
        user_id: 1,
        nickname: "홍길동",
        title: "오늘 경기 진짜 대박이네요! ⚾️",
        content: "사진이 있는 게시글의 경우, 사진과 본문 레이아웃은 미정.",
        views: 34,
        likes: 15,
        comments: [
          { nickname: "김철수", text: "저도 봤어요! 대박이었죠 😂" },
          { nickname: "이영희", text: "진짜 레전드 경기였어요!!" },
        ],
        created_at: "2025-11-06 14:22",
      },
      {
        id: 2,
        user_id: 2,
        nickname: "김철수",
        title: "다음 주 일정 아시는 분?",
        content: "혹시 다음 주 경기 일정표 공유 가능하신가요?",
        views: 12,
        likes: 2,
        comments: [],
        created_at: "2025-11-05 09:15",
      },
    ];
  }
  
  /* 게시판 렌더링 */
  window.addEventListener("load", () => {
    showPostList();
  });
  
  /* 게시글 목록 표시 */
  function showPostList() {
    const list = document.getElementById("post-list");
    const detail = document.getElementById("post-detail");
    list.style.display = "block";
    detail.style.display = "none";
  
    list.innerHTML = SAMPLE_POSTS.map(post => `
      <div class="post-card" onclick="showPostDetail(${post.id})">
        <div>
          <div class="post-title">${post.title}</div>
          <div class="post-meta">${post.nickname} · ${post.created_at}</div>
        </div>
        <div class="post-stats">
          <span class="material-icons">visibility</span> ${post.views}
          <span class="material-icons">favorite</span> ${post.likes}
          <span class="material-icons">chat_bubble</span> ${post.comments.length}
        </div>
      </div>
    `).join("");
  }
  
  /* 게시글 상세보기 */
  function showPostDetail(postId) {
    const post = SAMPLE_POSTS.find(p => p.id === postId);
    if (!post) return;
  
    const list = document.getElementById("post-list");
    const detail = document.getElementById("post-detail");
    const content = document.getElementById("post-content");
    const commentList = document.getElementById("comment-list");
  
    list.style.display = "none";
    detail.style.display = "block";
  
    content.innerHTML = `
      <h2 class="detail-title">${post.title}</h2>
      <div class="detail-meta">
        <span class="nickname">${post.nickname}</span> · ${post.created_at}
      </div>
      <p class="detail-content">${post.content.replace(/\n/g, "<br>")}</p>
  
      <div class="detail-actions">
        <span class="material-icons">visibility</span> ${post.views}
        <span class="material-icons">favorite</span> ${post.likes}
        <span class="material-icons">chat_bubble</span> ${post.comments.length}
      </div>
    `;
  
    commentList.innerHTML = post.comments.length
      ? post.comments.map(c => `
          <div class="comment-item">
            <span class="comment-nick">${c.nickname}</span>
            <span class="comment-text">${c.text}</span>
          </div>
        `).join("")
      : `<div class="comment-empty">아직 댓글이 없습니다.</div>`;
  
    const submitBtn = document.getElementById("comment-submit");
    const input = document.getElementById("comment-input");
  
    submitBtn.onclick = () => {
      const text = input.value.trim();
      if (!text) return alert("댓글을 입력하세요.");
      post.comments.push({ nickname: SAMPLE_USER.nickname, text });
      input.value = "";
      showPostDetail(postId); // 다시 렌더링
    };
  }
  