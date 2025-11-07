/*
[임시 샘플 데이터] — 정적 서버 전용
실제 서버 연동 시 반드시 제거!!
*/
if (typeof SAMPLE_MY_ACTIVITY === "undefined") {
    const SAMPLE_MY_ACTIVITY = [
      { type: "게시글", text: "오늘 경기 진짜 대박이네요!", date: "2025-11-06" },
      { type: "댓글", text: "저도 그렇게 생각해요!", date: "2025-11-06" },
      { type: "게시글", text: "팀 순위 정리했습니다 🦁", date: "2025-11-05" },
    ];
  
    /* 나의 활동 렌더링 로직 */
    document.addEventListener("DOMContentLoaded", () => {
      const activityBox = document.querySelector("#activity-box");
      if (!activityBox) return;
  
      activityBox.innerHTML = SAMPLE_MY_ACTIVITY.map(act => `
        <div class="activity-item">
          <strong>[${act.type}]</strong> ${act.text}
          <small>${act.date}</small>
        </div>
      `).join("");
    });
  }
  
  /*
  탭 전환 로직 — 게시글/좋아요/좋아요 누른 게시글
  나중에 fetch() 기반으로 교체 예정
  */
  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".activity-tab").forEach(btn => {
      btn.addEventListener("click", () => {
        // 탭 활성화 처리
        document.querySelectorAll(".activity-tab").forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
  
        const type = btn.getAttribute("data-type");
        const box = document.getElementById("activity-box");
  
        // 서버에서 받아올 부분은 나중에 fetch로 대체
        let content = "";
        switch (type) {
          case "posts":
            content = SAMPLE_MY_ACTIVITY
              .filter(a => a.type === "게시글")
              .map(a => `<p><strong>[게시글]</strong> ${a.text} <small>${a.date}</small></p>`)
              .join("");
            break;
          case "likes":
            content = "<p>좋아요한 게시글 목록입니다.</p>";
            break;
          case "liked-posts":
            content = "<p>좋아요 누른 게시글 예시입니다.</p>";
            break;
        }
  
        box.innerHTML = content || "<p>표시할 항목이 없습니다.</p>";
      });
    });
  });
  