-- ============================================
-- Database Schema for Dakhila System
-- ============================================

CREATE DATABASE IF NOT EXISTS `dakhila_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `dakhila_db`;

-- ============================================
-- Table: users
-- ============================================
DROP TABLE IF EXISTS `balance_transactions`;
DROP TABLE IF EXISTS `dakhila_dags`;
DROP TABLE IF EXISTS `dakhila_owners`;
DROP TABLE IF EXISTS `dakhila`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(255)    NOT NULL,
    `email`         VARCHAR(255)    NOT NULL,
    `password`      VARCHAR(255)    NOT NULL COMMENT 'bcrypt hash',
    `balance`       DECIMAL(16,4)   NOT NULL DEFAULT 0.0000 COMMENT 'বর্তমান ব্যালেন্স',
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ব্যবহারকারী';

-- ============================================
-- Table: balance_transactions
-- ============================================
CREATE TABLE `balance_transactions` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`       BIGINT UNSIGNED NOT NULL,
    `amount`        DECIMAL(16,4)   NOT NULL,
    `type`          ENUM('credit','debit') NOT NULL COMMENT 'credit=add, debit=deduct',
    `description`   VARCHAR(500)    NULL,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_created_at` (`created_at`),
    CONSTRAINT `fk_trans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ব্যালেন্স লেনদেন';

-- ============================================
-- Table: dakhila (main records)
-- ============================================
CREATE TABLE `dakhila` (
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`               BIGINT UNSIGNED NOT NULL COMMENT 'সাবমিটকারী ইউজার',
    `verify_id`             VARCHAR(50)     NULL     COMMENT 'যাচাই করার ইউনিক আইডি',
    `registry_no`           VARCHAR(100)    NOT NULL COMMENT 'ক্রমিক নং',
    `challan_no`            VARCHAR(100)    NULL     COMMENT 'চালান নম্বর',
    `office_name`           VARCHAR(255)    NOT NULL COMMENT 'সিটি কর্পোরেশন/পৌর/ইউনিয়ন ভূমি অফিসের নাম',
    `upazila`               VARCHAR(100)    NOT NULL COMMENT 'উপজেলা/থানা',
    `district`              VARCHAR(100)    NOT NULL COMMENT 'জেলা',
    `holding_no`            VARCHAR(100)    NOT NULL COMMENT 'হোল্ডিং নম্বর',
    `mouja_jl`              VARCHAR(255)    NOT NULL COMMENT 'মৌজা ও জে.এল. নম্বর',
    `khatian_no`            VARCHAR(100)    NOT NULL COMMENT 'খতিয়ান নম্বর',
    `payment_year_bn`       VARCHAR(20)     NOT NULL COMMENT 'পরিশোধের সাল (বাংলা)',
    `payment_year_en`       VARCHAR(30)     NOT NULL COMMENT 'পরিশোধের সাল (ইংরেজি)',
    `payment_day`           TINYINT         NOT NULL COMMENT 'দিন',
    `payment_month`         TINYINT         NOT NULL COMMENT 'মাস (১-১২)',
    `payment_year`          SMALLINT        NOT NULL COMMENT 'বছর (বঙ্গাব্দ)',
    `three_years_plus_due`  DECIMAL(16,4)   NOT NULL DEFAULT 0.0000 COMMENT 'তিন বৎসরের ঊর্ধ্বের বকেয়া',
    `last_three_years_due`  DECIMAL(16,4)   NOT NULL DEFAULT 0.0000 COMMENT 'গত তিন বৎসরের বকেয়া',
    `due_interest`          DECIMAL(16,4)   NOT NULL DEFAULT 0.0000 COMMENT 'বকেয়ার জরিমানা ও ক্ষতিপূরণ',
    `current_demand`        DECIMAL(16,4)   NOT NULL DEFAULT 0.0000 COMMENT 'হাল দাবি',
    `total_demand`          DECIMAL(16,4)   NOT NULL DEFAULT 0.0000 COMMENT 'মোট দাবি',
    `total_collection`      DECIMAL(16,4)   NOT NULL DEFAULT 0.0000 COMMENT 'মোট আদায়',
    `total_due`             DECIMAL(16,4)   NOT NULL DEFAULT 0.0000 COMMENT 'মোট বকেয়া',
    `comments`              TEXT            NULL     COMMENT 'মন্তব্য',
    `total_in_words`        VARCHAR(500)    NOT NULL COMMENT 'সর্বমোট (কথায়)',
    `issue_date`            DATE            NOT NULL COMMENT 'দাখিলা ইস্যুর ইংরেজি তারিখ (YYYY-MM-DD)',
    `created_at`            TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `uq_verify_id` (`verify_id`),
    INDEX `idx_user_id`     (`user_id`),
    INDEX `idx_registry_no` (`registry_no`),
    INDEX `idx_holding_no`  (`holding_no`),
    INDEX `idx_khatian_no`  (`khatian_no`),
    INDEX `idx_created_at`  (`created_at`),
    CONSTRAINT `fk_dakhila_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ভূমি উন্নয়ন কর দাখিলা রেকর্ড';


-- ============================================
-- Table: dakhila_owners
-- ============================================
CREATE TABLE `dakhila_owners` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `dakhila_id`    BIGINT UNSIGNED NOT NULL,
    `name`          VARCHAR(255)    NOT NULL COMMENT 'মালিকের নাম',
    `share`         DECIMAL(16,6)   NOT NULL DEFAULT 0.000000 COMMENT 'মালিকের অংশ',
    `sort_order`    TINYINT         NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    INDEX `idx_dakhila_id` (`dakhila_id`),
    CONSTRAINT `fk_owners_dakhila` FOREIGN KEY (`dakhila_id`) REFERENCES `dakhila` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='মালিকের বিবরণ';


-- ============================================
-- Table: dakhila_dags
-- ============================================
CREATE TABLE `dakhila_dags` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `dakhila_id`    BIGINT UNSIGNED NOT NULL,
    `dag_no`        VARCHAR(50)     NOT NULL COMMENT 'দাগ নম্বর',
    `type`          VARCHAR(255)    NOT NULL COMMENT 'জমির শ্রেণি / খতিয়ান শ্রেণি',
    `amount`        DECIMAL(16,6)   NOT NULL DEFAULT 0.000000 COMMENT 'জমির পরিমাণ',
    `sort_order`    TINYINT         NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    INDEX `idx_dakhila_id` (`dakhila_id`),
    CONSTRAINT `fk_dags_dakhila` FOREIGN KEY (`dakhila_id`) REFERENCES `dakhila` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='দাগের বিবরণ';

