-- ===============================================
-- XAMPP 환경용 MySQL 데이터베이스 생성 스크립트
-- phpMyAdmin에서 실행 가능
-- ===============================================

-- 데이터베이스 생성 (필요시 주석 해제)
-- CREATE DATABASE IF NOT EXISTS dugout CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE dugout;

-- 기존 테이블이 있다면 삭제 (순서 중요: 외래키 관계 고려)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `comment`;
DROP TABLE IF EXISTS `post_likes`;
DROP TABLE IF EXISTS `notice_board`;
DROP TABLE IF EXISTS `user_info`;
SET FOREIGN_KEY_CHECKS = 1;

-- ===============================================
-- 1. user_info 테이블 생성 (메인 사용자 테이블)
-- ===============================================
CREATE TABLE `user_info` (
    `id` VARCHAR(45) NOT NULL PRIMARY KEY,
    `password` VARCHAR(45) NOT NULL,
    `user_name` VARCHAR(45) NOT NULL,
    `phone_number` VARCHAR(45) NOT NULL,
    `email` VARCHAR(45) NOT NULL,
    `team_choice` INT NOT NULL,
    `first_name` VARCHAR(10) NOT NULL,
    `last_name` VARCHAR(10) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- 2. notice_board 테이블 생성 (게시판)
-- ===============================================
CREATE TABLE `notice_board` (
    `number_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(45) NOT NULL,
    `content` VARCHAR(255) NOT NULL,
    `like` INT DEFAULT 0,
    `views` INT DEFAULT 0,
    `date_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `user_info_id` VARCHAR(45) NOT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_info_id`) REFERENCES `user_info`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- 3. post_likes 테이블 생성 (게시글 좋아요)
-- ===============================================
CREATE TABLE `post_likes` (
    `user_id` VARCHAR(45) NOT NULL,
    `post_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `post_id`),
    FOREIGN KEY (`user_id`) REFERENCES `user_info`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`post_id`) REFERENCES `notice_board`(`number_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- 4. comment 테이블 생성 (댓글)
-- ===============================================
CREATE TABLE `comment` (
    `comment_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `comment_content` VARCHAR(255) NOT NULL,
    `notice_board_number_id` INT NOT NULL,
    `comment_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `user_info_id` VARCHAR(45) NOT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`notice_board_number_id`) REFERENCES `notice_board`(`number_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`user_info_id`) REFERENCES `user_info`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- 인덱스 생성 (성능 최적화)
-- ===============================================

-- user_info 테이블 인덱스
CREATE INDEX `idx_user_email` ON `user_info`(`email`);
CREATE INDEX `idx_user_team_choice` ON `user_info`(`team_choice`);

-- notice_board 테이블 인덱스
CREATE INDEX `idx_notice_board_user` ON `notice_board`(`user_info_id`);
CREATE INDEX `idx_notice_board_date` ON `notice_board`(`date_time`);
CREATE INDEX `idx_notice_board_views` ON `notice_board`(`views`);
CREATE INDEX `idx_notice_board_like` ON `notice_board`(`like`);

-- post_likes 테이블 인덱스는 PRIMARY KEY가 자동으로 생성하므로 불필요

-- comment 테이블 인덱스
CREATE INDEX `idx_comment_notice_board` ON `comment`(`notice_board_number_id`);
CREATE INDEX `idx_comment_user` ON `comment`(`user_info_id`);
CREATE INDEX `idx_comment_time` ON `comment`(`comment_time`);

-- ===============================================
-- 샘플 데이터 삽입 (테스트용 - 필요시 주석 해제)
-- ===============================================

/*
-- 샘플 사용자 데이터
INSERT INTO `user_info` (`id`, `password`, `user_name`, `phone_number`, `email`, `team_choice`, `first_name`, `last_name`) VALUES
('admin', 'admin123', '관리자', '01012345678', 'admin@example.com', 1, '관리', '자'),
('user1', 'user123', '사용자1', '01023456789', 'user1@example.com', 2, '사용', '자1'),
('user2', 'user456', '사용자2', '01034567890', 'user2@example.com', 1, '사용', '자2');

-- 샘플 게시글 데이터
INSERT INTO `notice_board` (`title`, `content`, `user_info_id`) VALUES
('공지사항 제목 1', '공지사항 내용입니다.', 'admin'),
('공지사항 제목 2', '두 번째 공지사항입니다.', 'admin');

-- 샘플 댓글 데이터
INSERT INTO `comment` (`comment_content`, `notice_board_number_id`, `user_info_id`) VALUES
('첫 번째 댓글입니다.', 1, 'user1'),
('두 번째 댓글입니다.', 1, 'user2');
*/

-- ===============================================
-- 설정 완료 메시지
-- ===============================================
SELECT 'Database setup completed successfully!' as Status;
