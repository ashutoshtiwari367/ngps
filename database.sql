-- MySQL Database Schema for RDMA Kids • School Management System
-- Generated for XAMPP Localhost deployment

CREATE DATABASE IF NOT EXISTS `school_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `school_management`;

-- Drop tables in reverse FK dependency order
DROP TABLE IF EXISTS `exam_marks`;
DROP TABLE IF EXISTS `examinations`;
DROP TABLE IF EXISTS `timetables`;
DROP TABLE IF EXISTS `homework`;
DROP TABLE IF EXISTS `notices`;
DROP TABLE IF EXISTS `school_settings`;
DROP TABLE IF EXISTS `subjects`;
DROP TABLE IF EXISTS `attendance`;
DROP TABLE IF EXISTS `fees`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `classes`;
DROP TABLE IF EXISTS `teachers`;
DROP TABLE IF EXISTS `users`;

-- --------------------------------------------------------
-- Table structure for `users` (Admin Authentication)
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `role` VARCHAR(20) DEFAULT 'admin',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Admin User (Username: admin | Password: admin123)
INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `role`) VALUES
('admin', '$2y$10$u/xaHlNv1qCf8FI8dmoSwOryqxP3F9Uay0PiTyscIlziR3xvT.Xbe', 'System Administrator', 'admin@nextgenps.edu.in', 'admin'),
('accountant', '$2y$10$wHzMqLXVGhb7OB4HAVm97eRtgFo0VDvWGwY8Go//10VGlMug3.eW2', 'Chief Accountant', 'accounts@nextgenps.edu.in', 'accountant');

-- --------------------------------------------------------
-- Table structure for `teachers`
-- --------------------------------------------------------
CREATE TABLE `teachers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `teacher_id` VARCHAR(30) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(100) NOT NULL,
  `qualification` VARCHAR(100) NOT NULL,
  `mobile` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `address` TEXT,
  `joining_date` DATE NOT NULL,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `username` VARCHAR(50) DEFAULT NULL UNIQUE,
  `password` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Teachers with Login Credentials (Password for all sample teachers: teacher123)
INSERT INTO `teachers` (`teacher_id`, `name`, `subject`, `qualification`, `mobile`, `email`, `address`, `joining_date`, `status`, `username`, `password`) VALUES
('TCH-101', 'Dr. Rajesh Sharma', 'Mathematics', 'M.Sc., Ph.D.', '9876543210', 'rajesh.sharma@school.com', '12 Park Street, New Delhi', '2020-06-15', 'Active', 'rajesh', '$2y$10$wHzMqLXVGhb7OB4HAVm97eRtgFo0VDvWGwY8Go//10VGlMug3.eW2'),
('TCH-102', 'Sunita Verma', 'English', 'M.A. English, B.Ed.', '9876543211', 'sunita.verma@school.com', '45 Model Town, Delhi', '2021-04-10', 'Active', 'sunita', '$2y$10$wHzMqLXVGhb7OB4HAVm97eRtgFo0VDvWGwY8Go//10VGlMug3.eW2'),
('TCH-103', 'Vikram Singh', 'Science & Physics', 'M.Sc. Physics', '9876543212', 'vikram.singh@school.com', '88 Green Park, New Delhi', '2019-08-01', 'Active', 'vikram', '$2y$10$wHzMqLXVGhb7OB4HAVm97eRtgFo0VDvWGwY8Go//10VGlMug3.eW2'),
('TCH-104', 'Ananya Roy', 'Computer Science', 'M.Tech CSE', '9876543213', 'ananya.roy@school.com', '102 Vasant Kunj, New Delhi', '2022-01-15', 'Active', 'ananya', '$2y$10$wHzMqLXVGhb7OB4HAVm97eRtgFo0VDvWGwY8Go//10VGlMug3.eW2'),
('TCH-105', 'Amitabh Kumar', 'Social Studies', 'M.A. History', '9876543214', 'amitabh.kumar@school.com', '34 Connaught Place, New Delhi', '2018-11-20', 'Active', 'amitabh', '$2y$10$wHzMqLXVGhb7OB4HAVm97eRtgFo0VDvWGwY8Go//10VGlMug3.eW2');

-- --------------------------------------------------------
-- Table structure for `classes`
-- --------------------------------------------------------
CREATE TABLE `classes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `class_name` VARCHAR(50) NOT NULL,
  `section` VARCHAR(20) NOT NULL,
  `teacher_id` INT DEFAULT NULL,
  `room_number` VARCHAR(30) NOT NULL,
  `academic_session` VARCHAR(30) NOT NULL DEFAULT '2026-2027',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Classes (PG to 12th)
INSERT INTO `classes` (`class_name`, `section`, `teacher_id`, `room_number`, `academic_session`) VALUES
('PG', 'A', NULL, 'Room PG-1', '2026-2027'),
('Nursery', 'A', NULL, 'Room NUR-1', '2026-2027'),
('LKG', 'A', NULL, 'Room LKG-1', '2026-2027'),
('UKG', 'A', NULL, 'Room UKG-1', '2026-2027'),
('Class 1', 'A', NULL, 'Room 101', '2026-2027'),
('Class 2', 'A', NULL, 'Room 102', '2026-2027'),
('Class 3', 'A', NULL, 'Room 103', '2026-2027'),
('Class 4', 'A', NULL, 'Room 104', '2026-2027'),
('Class 5', 'A', NULL, 'Room 105', '2026-2027'),
('Class 6', 'A', NULL, 'Room 201', '2026-2027'),
('Class 7', 'A', NULL, 'Room 202', '2026-2027'),
('Class 8', 'A', 1, 'Room 203', '2026-2027'),
('Class 8', 'B', NULL, 'Room 204', '2026-2027'),
('Class 9', 'A', 2, 'Room 301', '2026-2027'),
('Class 9', 'B', 3, 'Room 302', '2026-2027'),
('Class 10', 'A', 4, 'Room 303', '2026-2027'),
('Class 10', 'B', 5, 'Room 304', '2026-2027'),
('Class 11', 'A', NULL, 'Room 401', '2026-2027'),
('Class 11', 'B', NULL, 'Room 402', '2026-2027'),
('Class 12', 'A', NULL, 'Room 403', '2026-2027'),
('Class 12', 'B', NULL, 'Room 404', '2026-2027');

-- --------------------------------------------------------
-- Table structure for `students`
-- --------------------------------------------------------
CREATE TABLE `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admission_no` VARCHAR(30) NOT NULL UNIQUE,
  `password` VARCHAR(255) DEFAULT NULL,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `father_name` VARCHAR(100) NOT NULL,
  `mother_name` VARCHAR(100) NOT NULL,
  `dob` DATE NOT NULL,
  `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
  `class_id` INT NOT NULL,
  `section` VARCHAR(20) NOT NULL,
  `mobile` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT,
  `admission_date` DATE NOT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Active', 'Inactive', 'Graduated') DEFAULT 'Active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- (No dummy students - Fresh zero state)

-- --------------------------------------------------------
-- Table structure for `fees`
-- --------------------------------------------------------
CREATE TABLE `fees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `class_id` INT NOT NULL,
  `registration_fee` DECIMAL(10,2) DEFAULT 0.00,
  `admission_fee` DECIMAL(10,2) DEFAULT 0.00,
  `annual_fee` DECIMAL(10,2) DEFAULT 0.00,
  `tuition_fee` DECIMAL(10,2) DEFAULT 0.00,
  `library_fee` DECIMAL(10,2) DEFAULT 0.00,
  `computer_fee` DECIMAL(10,2) DEFAULT 0.00,
  `activity_fee` DECIMAL(10,2) DEFAULT 0.00,
  `magazine_fee` DECIMAL(10,2) DEFAULT 0.00,
  `exam_fee` DECIMAL(10,2) DEFAULT 0.00,
  `penalty_fee` DECIMAL(10,2) DEFAULT 0.00,
  `other_fee` DECIMAL(10,2) DEFAULT 0.00,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `fee_type` VARCHAR(50) DEFAULT 'Admission & Term',
  `total_amount` DECIMAL(10,2) NOT NULL,
  `paid_amount` DECIMAL(10,2) NOT NULL,
  `remaining_amount` DECIMAL(10,2) NOT NULL,
  `paid_months` VARCHAR(255) DEFAULT 'April, May',
  `payment_date` DATE NOT NULL,
  `payment_method` ENUM('Cash', 'UPI', 'Card', 'Bank Transfer', 'Cheque') DEFAULT 'Cash',
  `payment_status` ENUM('Paid', 'Partial', 'Pending') NOT NULL,
  `remarks` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- (No dummy fees - Fresh zero state)

-- --------------------------------------------------------
-- Table structure for `attendance`
-- --------------------------------------------------------
CREATE TABLE `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `class_id` INT NOT NULL,
  `attendance_date` DATE NOT NULL,
  `status` ENUM('Present', 'Absent', 'Late') NOT NULL,
  `remarks` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_student_date` (`student_id`, `attendance_date`),
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- (No dummy attendance - Fresh zero state)

-- --------------------------------------------------------
-- Table structure for `subjects`
-- --------------------------------------------------------
CREATE TABLE `subjects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `subject_name` VARCHAR(100) NOT NULL,
  `subject_code` VARCHAR(30) NOT NULL UNIQUE,
  `class_id` INT DEFAULT NULL,
  `teacher_id` INT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Subjects
INSERT INTO `subjects` (`subject_name`, `subject_code`, `class_id`, `teacher_id`) VALUES
('Mathematics', 'MATH-8', 1, 1),
('English Literature', 'ENG-8', 1, 2),
('General Science', 'SCI-8', 1, 3),
('Mathematics', 'MATH-9', 2, 1),
('English Grammar', 'ENG-9', 2, 2),
('Physics & Chemistry', 'SCI-9', 2, 3),
('Computer Applications', 'CS-9', 2, 4),
('Mathematics', 'MATH-10', 4, 1),
('Science & Physics', 'SCI-10', 4, 3),
('Computer Science', 'CS-10', 4, 4);

-- --------------------------------------------------------
-- Table structure for `examinations`
-- --------------------------------------------------------
CREATE TABLE `examinations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `exam_name` VARCHAR(100) NOT NULL,
  `session` VARCHAR(30) NOT NULL DEFAULT '2026-2027',
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` ENUM('Upcoming', 'Ongoing', 'Completed') DEFAULT 'Upcoming',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Examinations
INSERT INTO `examinations` (`exam_name`, `session`, `start_date`, `end_date`, `status`) VALUES
('First Mid-Term Examination', '2026-2027', '2026-05-10', '2026-05-20', 'Completed'),
('Half-Yearly Examination', '2026-2027', '2026-09-15', '2026-09-30', 'Ongoing'),
('Final Annual Examination', '2026-2027', '2027-03-01', '2027-03-15', 'Upcoming');

-- --------------------------------------------------------
-- Table structure for `exam_marks`
-- --------------------------------------------------------
CREATE TABLE `exam_marks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `exam_id` INT NOT NULL,
  `student_id` INT NOT NULL,
  `subject_id` INT NOT NULL,
  `marks_obtained` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `max_marks` DECIMAL(5,2) NOT NULL DEFAULT 100.00,
  `grade` VARCHAR(10) DEFAULT 'A',
  `remarks` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_exam_student_subject` (`exam_id`, `student_id`, `subject_id`),
  FOREIGN KEY (`exam_id`) REFERENCES `examinations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- (No dummy exam marks - Fresh zero state)

-- --------------------------------------------------------
-- Table structure for `timetables`
-- --------------------------------------------------------
CREATE TABLE `timetables` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `class_id` INT NOT NULL,
  `subject_id` INT NOT NULL,
  `teacher_id` INT DEFAULT NULL,
  `day_of_week` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `room_number` VARCHAR(30) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Timetable Slots
INSERT INTO `timetables` (`class_id`, `subject_id`, `teacher_id`, `day_of_week`, `start_time`, `end_time`, `room_number`) VALUES
(1, 1, 1, 'Monday', '08:30:00', '09:30:00', 'Room 101'),
(1, 2, 2, 'Monday', '09:30:00', '10:30:00', 'Room 101'),
(1, 3, 3, 'Monday', '10:45:00', '11:45:00', 'Room 101'),
(1, 1, 1, 'Tuesday', '08:30:00', '09:30:00', 'Room 101'),
(1, 2, 2, 'Wednesday', '09:30:00', '10:30:00', 'Room 101');

-- --------------------------------------------------------
-- Table structure for `homework`
-- --------------------------------------------------------
CREATE TABLE `homework` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `class_id` INT NOT NULL,
  `subject_id` INT NOT NULL,
  `teacher_id` INT DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `assigned_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- (No dummy homework - Fresh zero state)

-- --------------------------------------------------------
-- Table structure for `notices`
-- --------------------------------------------------------
CREATE TABLE `notices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `target_role` ENUM('All', 'Teacher', 'Student') DEFAULT 'All',
  `is_important` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- (No dummy notices - Fresh zero state)

-- --------------------------------------------------------
-- Table structure for `school_settings`
-- --------------------------------------------------------
CREATE TABLE `school_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Settings
INSERT INTO `school_settings` (`setting_key`, `setting_value`) VALUES
('school_name', 'Next Generation Public School'),
('school_tagline', 'विद्या ददाति विनयं'),
('school_logo', 'assets/images/logo_circle.png'),
('school_address', '123 Education Boulevard, Knowledge City, New Delhi - 110001'),
('school_phone', '+91 98765 43210'),
('school_email', 'contact@nextgenps.edu.in'),
('academic_session', '2026-2027'),
('currency_symbol', '₹');
