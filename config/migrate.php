<?php
/**
 * Dynamic Database Migration & Seeding Script
 * RDMA Kids • School Management System
 */

function run_migrations($pdo) {
    try {
        // 1. Ensure `students` has password column
        $cols = $pdo->query("SHOW COLUMNS FROM `students` LIKE 'password'")->fetchAll();
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE `students` ADD COLUMN `password` VARCHAR(255) DEFAULT NULL AFTER `email`");
        }

        // Set default password for any students missing password (password: student123)
        $default_student_pwd = password_hash('student123', PASSWORD_BCRYPT);
        $pdo->exec("UPDATE `students` SET `password` = '$default_student_pwd' WHERE `password` IS NULL OR `password` = ''");

        // 2. Table `subjects`
        $pdo->exec("CREATE TABLE IF NOT EXISTS `subjects` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `subject_name` VARCHAR(100) NOT NULL,
            `subject_code` VARCHAR(30) NOT NULL UNIQUE,
            `class_id` INT NOT NULL,
            `teacher_id` INT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Seed default subjects if empty
        $subject_count = (int)$pdo->query("SELECT COUNT(*) FROM `subjects`")->fetchColumn();
        if ($subject_count === 0) {
            $pdo->exec("INSERT INTO `subjects` (`subject_name`, `subject_code`, `class_id`, `teacher_id`) VALUES
                ('Mathematics', 'MATH-08', 1, 1),
                ('English Literature', 'ENG-08', 1, 2),
                ('Science', 'SCI-09A', 2, 3),
                ('Computer Science', 'CS-09B', 3, 4),
                ('Physics & Chemistry', 'PHY-10A', 4, 3),
                ('Social Studies', 'SST-10B', 5, 5)");
        }

        // 3. Table `examinations`
        $pdo->exec("CREATE TABLE IF NOT EXISTS `examinations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `exam_name` VARCHAR(100) NOT NULL,
            `academic_session` VARCHAR(30) NOT NULL DEFAULT '2026-2027',
            `start_date` DATE NOT NULL,
            `end_date` DATE NOT NULL,
            `status` ENUM('Scheduled', 'Ongoing', 'Completed', 'Published') DEFAULT 'Scheduled',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Seed default exams if empty
        $exam_count = (int)$pdo->query("SELECT COUNT(*) FROM `examinations`")->fetchColumn();
        if ($exam_count === 0) {
            $pdo->exec("INSERT INTO `examinations` (`exam_name`, `academic_session`, `start_date`, `end_date`, `status`) VALUES
                ('Mid-Term Examinations 2026', '2026-2027', '2026-10-05', '2026-10-15', 'Published'),
                ('Final Annual Examinations 2027', '2026-2027', '2027-03-10', '2027-03-25', 'Scheduled')");
        }

        // 4. Table `exam_marks`
        $pdo->exec("CREATE TABLE IF NOT EXISTS `exam_marks` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `exam_id` INT NOT NULL,
            `student_id` INT NOT NULL,
            `subject_id` INT NOT NULL,
            `marks_obtained` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            `max_marks` DECIMAL(5,2) NOT NULL DEFAULT 100.00,
            `grade` VARCHAR(10) DEFAULT NULL,
            `remarks` VARCHAR(255) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_exam_student_subject` (`exam_id`, `student_id`, `subject_id`),
            FOREIGN KEY (`exam_id`) REFERENCES `examinations`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Seed sample exam marks if empty
        $marks_count = (int)$pdo->query("SELECT COUNT(*) FROM `exam_marks`")->fetchColumn();
        if ($marks_count === 0) {
            $pdo->exec("INSERT INTO `exam_marks` (`exam_id`, `student_id`, `subject_id`, `marks_obtained`, `max_marks`, `grade`, `remarks`) VALUES
                (1, 1, 1, 92.50, 100.00, 'A+', 'Outstanding performance'),
                (1, 1, 2, 88.00, 100.00, 'A', 'Very good'),
                (1, 2, 1, 78.50, 100.00, 'B+', 'Good effort'),
                (1, 2, 2, 91.00, 100.00, 'A+', 'Excellent'),
                (1, 3, 3, 85.00, 100.00, 'A', 'Well done'),
                (1, 4, 3, 72.00, 100.00, 'B', 'Needs practice in numericals'),
                (1, 5, 4, 95.00, 100.00, 'A+', 'Top scorer'),
                (1, 7, 5, 89.50, 100.00, 'A', 'Strong analytical skills')");
        }

        // 5. Table `timetables`
        $pdo->exec("CREATE TABLE IF NOT EXISTS `timetables` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `class_id` INT NOT NULL,
            `subject_id` INT NOT NULL,
            `teacher_id` INT DEFAULT NULL,
            `day_of_week` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
            `period_name` VARCHAR(50) NOT NULL,
            `start_time` TIME NOT NULL,
            `end_time` TIME NOT NULL,
            `room_number` VARCHAR(30) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Seed timetable entries if empty
        $tt_count = (int)$pdo->query("SELECT COUNT(*) FROM `timetables`")->fetchColumn();
        if ($tt_count === 0) {
            $pdo->exec("INSERT INTO `timetables` (`class_id`, `subject_id`, `teacher_id`, `day_of_week`, `period_name`, `start_time`, `end_time`, `room_number`) VALUES
                (1, 1, 1, 'Monday', 'Period 1', '08:30:00', '09:15:00', 'Room 101'),
                (1, 2, 2, 'Monday', 'Period 2', '09:15:00', '10:00:00', 'Room 101'),
                (1, 1, 1, 'Tuesday', 'Period 1', '08:30:00', '09:15:00', 'Room 101'),
                (1, 2, 2, 'Wednesday', 'Period 3', '10:15:00', '11:00:00', 'Room 101'),
                (2, 3, 3, 'Monday', 'Period 1', '08:30:00', '09:15:00', 'Room 102'),
                (3, 4, 4, 'Monday', 'Period 2', '09:15:00', '10:00:00', 'Computer Lab 1')");
        }

        // 6. Table `homework`
        $pdo->exec("CREATE TABLE IF NOT EXISTS `homework` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `class_id` INT NOT NULL,
            `subject_id` INT NOT NULL,
            `teacher_id` INT NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NOT NULL,
            `issue_date` DATE NOT NULL,
            `due_date` DATE NOT NULL,
            `status` ENUM('Active', 'Completed', 'Archived') DEFAULT 'Active',
            `file_path` VARCHAR(255) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Seed homework if empty
        $hw_count = (int)$pdo->query("SELECT COUNT(*) FROM `homework`")->fetchColumn();
        if ($hw_count === 0) {
            $pdo->exec("INSERT INTO `homework` (`class_id`, `subject_id`, `teacher_id`, `title`, `description`, `issue_date`, `due_date`, `status`) VALUES
                (1, 1, 1, 'Algebra Practice Worksheet Ex 4.2', 'Solve questions 1 to 15 from Chapter 4 Linear Equations in notebook.', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'Active'),
                (1, 2, 2, 'Essay Writing: Environmental Conservation', 'Write a 300-word essay on sustainable practices in daily life.', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'Active'),
                (2, 3, 3, 'Physics Lab Report - Pendulum Motion', 'Complete graph calculations for simple pendulum experiment.', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 4 DAY), 'Active')");
        }

        // 7. Table `notices`
        $pdo->exec("CREATE TABLE IF NOT EXISTS `notices` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `content` TEXT NOT NULL,
            `target_role` ENUM('All', 'Admin', 'Teacher', 'Student') DEFAULT 'All',
            `author_name` VARCHAR(100) DEFAULT 'School Management',
            `is_important` TINYINT(1) DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Seed notices if empty
        $notice_count = (int)$pdo->query("SELECT COUNT(*) FROM `notices`")->fetchColumn();
        if ($notice_count === 0) {
            $pdo->exec("INSERT INTO `notices` (`title`, `content`, `target_role`, `author_name`, `is_important`) VALUES
                ('Annual Sports Meet 2026 Schedule', 'The Annual School Sports Meet will be held on October 25th. All interested students must register with their class physical education teacher by next Friday.', 'All', 'Principal Office', 1),
                ('Teacher Staff Meeting', 'Quarterly academic review meeting is scheduled for Friday at 3:30 PM in the conference hall.', 'Teacher', 'Academic Coordinator', 0),
                ('Parent-Teacher Conference (PTM)', 'PTM for First Quarter academic results will take place on Saturday from 9:00 AM to 1:00 PM. Parents are requested to attend.', 'Student', 'School Admin', 1)");
        }

        // 8. Table `school_settings`
        $pdo->exec("CREATE TABLE IF NOT EXISTS `school_settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `setting_key` VARCHAR(50) NOT NULL UNIQUE,
            `setting_value` TEXT DEFAULT NULL,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Seed settings if empty
        $setting_count = (int)$pdo->query("SELECT COUNT(*) FROM `school_settings`")->fetchColumn();
        if ($setting_count === 0) {
            $pdo->exec("INSERT INTO `school_settings` (`setting_key`, `setting_value`) VALUES
                ('school_name', 'RDMA Kids'),
                ('school_tagline', 'School Management System'),
                ('school_email', 'info@rdmakids.edu'),
                ('school_phone', '+91 98765 43210'),
                ('school_address', '123 Education Boulevard, Knowledge City, New Delhi - 110001'),
                ('academic_session', '2026-2027'),
                ('currency_symbol', '₹')");
        }

        // 9. Ensure `fees` table has itemized fee columns
        $fee_cols = $pdo->query("SHOW COLUMNS FROM `fees` LIKE 'admission_fee'")->fetchAll();
        if (empty($fee_cols)) {
            $pdo->exec("ALTER TABLE `fees` 
                ADD COLUMN `admission_fee` DECIMAL(10,2) DEFAULT 0.00 AFTER `class_id`,
                ADD COLUMN `tuition_fee` DECIMAL(10,2) DEFAULT 0.00 AFTER `admission_fee`,
                ADD COLUMN `development_fee` DECIMAL(10,2) DEFAULT 0.00 AFTER `tuition_fee`,
                ADD COLUMN `computer_fee` DECIMAL(10,2) DEFAULT 0.00 AFTER `development_fee`,
                ADD COLUMN `security_deposit` DECIMAL(10,2) DEFAULT 0.00 AFTER `computer_fee`,
                ADD COLUMN `transport_fee` DECIMAL(10,2) DEFAULT 0.00 AFTER `security_deposit`,
                ADD COLUMN `discount_amount` DECIMAL(10,2) DEFAULT 0.00 AFTER `transport_fee`,
                ADD COLUMN `fee_type` VARCHAR(50) DEFAULT 'Admission & Term' AFTER `discount_amount`");
        }

        // 10. Seed Accountant user in `users` table if missing (Username: accountant | Password: account123)
        $accountant_user = $pdo->query("SELECT * FROM `users` WHERE `username` = 'accountant'")->fetch();
        if (!$accountant_user) {
            $acc_pwd = password_hash('account123', PASSWORD_BCRYPT);
            $pdo->exec("INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `role`) VALUES
                ('accountant', '$acc_pwd', 'Chief Accountant / Fees Manager', 'accounts@rdmakids.edu', 'accountant')");
        }

    } catch (PDOException $e) {
        // Log error silently to avoid breaking execution flow if already migrated
        error_log("Migration notice: " . $e->getMessage());
    }
}
