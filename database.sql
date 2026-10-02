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
('admin', '$2y$10$u/xaHlNv1qCf8FI8dmoSwOryqxP3F9Uay0PiTyscIlziR3xvT.Xbe', 'System Administrator', 'admin@school.com', 'admin');

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

-- Seed Classes
INSERT INTO `classes` (`class_name`, `section`, `teacher_id`, `room_number`, `academic_session`) VALUES
('Class 8', 'A', 1, 'Room 101', '2026-2027'),
('Class 9', 'A', 2, 'Room 102', '2026-2027'),
('Class 9', 'B', 3, 'Room 103', '2026-2027'),
('Class 10', 'A', 4, 'Room 201', '2026-2027'),
('Class 10', 'B', 5, 'Room 202', '2026-2027');

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

-- Seed Students (Password for all sample students: student123)
INSERT INTO `students` (`admission_no`, `password`, `first_name`, `last_name`, `father_name`, `mother_name`, `dob`, `gender`, `class_id`, `section`, `mobile`, `email`, `address`, `admission_date`, `photo`, `status`) VALUES
('STU-2026-001', '$2y$10$wHzMqLXVGhb7OB4HAVm97eRtgFo0VDvWGwY8Go//10VGlMug3.eW2', 'Aarav', 'Mehta', 'Sanjay Mehta', 'Ritu Mehta', '2011-05-12', 'Male', 1, 'A', '9123456780', 'aarav.m@gmail.com', 'Flat 12, Sunrise Apts, New Delhi', '2026-04-01', NULL, 'Active'),
('STU-2026-002', '$2y$10$wHzMqLXVGhb7OB4HAVm97eRtgFo0VDvWGwY8Go//10VGlMug3.eW2', 'Ananya', 'Sharma', 'Rakesh Sharma', 'Pooja Sharma', '2011-08-20', 'Female', 1, 'A', '9123456781', 'ananya.s@gmail.com', 'H.No 45, Sector 14, Gurgaon', '2026-04-01', NULL, 'Active'),
('STU-2026-003', '$2y$10$wHzMqLXVGhb7OB4HAVm97eRtgFo0VDvWGwY8Go//10VGlMug3.eW2', 'Rohan', 'Gupta', 'Deepak Gupta', 'Sunita Gupta', '2010-02-14', 'Male', 2, 'A', '9123456782', 'rohan.g@gmail.com', 'Plot 8, Dwarka Sec 6, Delhi', '2026-04-02', NULL, 'Active'),
('STU-2026-004', '$2y$10$wHzMqLXVGhb7OB4HAVm97eRtgFo0VDvWGwY8Go//10VGlMug3.eW2', 'Priya', 'Singh', 'Mahesh Singh', 'Sarita Singh', '2010-11-05', 'Female', 2, 'A', '9123456783', 'priya.s@gmail.com', '56 Saket, New Delhi', '2026-04-02', NULL, 'Active'),
('STU-2026-005', '$2y$10$wHzMqLXVGhb7OB4HAVm97eRtgFo0VDvWGwY8Go//10VGlMug3.eW2', 'Dev', 'Joshi', 'Alok Joshi', 'Manju Joshi', '2010-09-18', 'Male', 3, 'B', '9123456784', 'dev.j@gmail.com', '19 Mayur Vihar Ph 1, Delhi', '2026-04-03', NULL, 'Active'),
('STU-2026-006', '$2y$10$wHzMqLXVGhb7OB4HAVm97eRtgFo0VDvWGwY8Go//10VGlMug3.eW2', 'Isha', 'Patel', 'Ketan Patel', 'Bhavna Patel', '2010-04-30', 'Female', 3, 'B', '9123456785', 'isha.p@gmail.com', '77 Rohini Sec 9, Delhi', '2026-04-03', NULL, 'Active'),
('STU-2026-007', '$2y$10$wHzMqLXVGhb7OB4HAVm97eRtgFo0VDvWGwY8Go//10VGlMug3.eW2', 'Kabir', 'Khanna', 'Vivek Khanna', 'Neha Khanna', '2009-07-22', 'Male', 4, 'A', '9123456786', 'kabir.k@gmail.com', '104 Greater Kailash, Delhi', '2026-04-04', NULL, 'Active'),
('STU-2026-008', '$2y$10$wHzMqLXVGhb7OB4HAVm97eRtgFo0VDvWGwY8Go//10VGlMug3.eW2', 'Diya', 'Verma', 'Suresh Verma', 'Kavita Verma', '2009-12-10', 'Female', 4, 'A', '9123456787', 'diya.v@gmail.com', '88 Preet Vihar, Delhi', '2026-04-04', NULL, 'Active'),
('STU-2026-009', '$2y$10$wHzMqLXVGhb7OB4HAVm97eRtgFo0VDvWGwY8Go//10VGlMug3.eW2', 'Vihaan', 'Reddy', 'Prakash Reddy', 'Latha Reddy', '2009-03-15', 'Male', 5, 'B', '9123456788', 'vihaan.r@gmail.com', '21 Hauz Khas, Delhi', '2026-04-05', NULL, 'Active'),
('STU-2026-010', '$2y$10$wHzMqLXVGhb7OB4HAVm97eRtgFo0VDvWGwY8Go//10VGlMug3.eW2', 'Saniya', 'Malhotra', 'Anil Malhotra', 'Seema Malhotra', '2009-10-08', 'Female', 5, 'B', '9123456789', 'saniya.m@gmail.com', '43 Defence Colony, Delhi', '2026-04-05', NULL, 'Active');

-- --------------------------------------------------------
-- Table structure for `fees`
-- --------------------------------------------------------
CREATE TABLE `fees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `class_id` INT NOT NULL,
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

-- Seed Fees Records
INSERT INTO `fees` (`student_id`, `class_id`, `total_amount`, `paid_amount`, `remaining_amount`, `paid_months`, `payment_date`, `payment_method`, `payment_status`, `remarks`) VALUES
(1, 1, 15000.00, 15000.00, 0.00, 'April, May, June', '2026-04-10', 'UPI', 'Paid', 'First Quarter Fee Paid'),
(2, 1, 15000.00, 10000.00, 5000.00, 'April, May', '2026-04-12', 'Cash', 'Partial', 'Remaining due by next month'),
(3, 2, 18000.00, 18000.00, 0.00, 'April, May, June, July', '2026-04-15', 'Card', 'Paid', 'Full Payment'),
(4, 2, 18000.00, 0.00, 18000.00, 'May, June', '2026-04-16', 'Cash', 'Pending', 'Payment Pending Notice Sent'),
(5, 3, 18000.00, 18000.00, 0.00, 'April, May, June', '2026-04-18', 'Bank Transfer', 'Paid', 'Full Payment Received'),
(6, 3, 18000.00, 9000.00, 9000.00, 'May', '2026-04-20', 'UPI', 'Partial', '50% Paid'),
(7, 4, 22000.00, 22000.00, 0.00, 'April, May, June, July, August', '2026-04-22', 'Card', 'Paid', 'Term 1 Fee Paid'),
(8, 4, 22000.00, 12000.00, 10000.00, 'May, June', '2026-04-25', 'Cash', 'Partial', 'Partial payment'),
(9, 5, 22000.00, 22000.00, 0.00, 'April, May, June', '2026-04-26', 'UPI', 'Paid', 'Cleared all dues'),
(10, 5, 22000.00, 0.00, 22000.00, 'June, July', '2026-04-27', 'Cash', 'Pending', 'Awaiting DD submission');

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

-- Seed Attendance Records
INSERT INTO `attendance` (`student_id`, `class_id`, `attendance_date`, `status`, `remarks`) VALUES
(1, 1, CURDATE(), 'Present', 'On time'),
(2, 1, CURDATE(), 'Present', 'On time'),
(3, 2, CURDATE(), 'Present', 'On time'),
(4, 2, CURDATE(), 'Absent', 'Sick Leave'),
(5, 3, CURDATE(), 'Present', 'On time'),
(6, 3, CURDATE(), 'Present', 'On time'),
(7, 4, CURDATE(), 'Present', 'On time'),
(8, 4, CURDATE(), 'Absent', 'Uninformed'),
(9, 5, CURDATE(), 'Present', 'On time'),
(10, 5, CURDATE(), 'Present', 'On time');

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

-- Seed Exam Marks
INSERT INTO `exam_marks` (`exam_id`, `student_id`, `subject_id`, `marks_obtained`, `max_marks`, `grade`, `remarks`) VALUES
(1, 1, 1, 92.50, 100.00, 'A+', 'Excellent performance'),
(1, 1, 2, 88.00, 100.00, 'A', 'Very good'),
(1, 1, 3, 95.00, 100.00, 'A+', 'Outstanding'),
(1, 2, 1, 78.00, 100.00, 'B+', 'Good effort'),
(1, 2, 2, 85.00, 100.00, 'A', 'Well written'),
(1, 2, 3, 82.50, 100.00, 'A', 'Good understanding');

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

-- Seed Homework Tasks
INSERT INTO `homework` (`class_id`, `subject_id`, `teacher_id`, `title`, `description`, `assigned_date`, `due_date`) VALUES
(1, 1, 1, 'Algebra Practice Chapter 4', 'Solve exercises 4.1 to 4.5 from NCERT Mathematics textbook in homework notebook.', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY)),
(1, 2, 2, 'English Essay Writing', 'Write a 300-word essay on "The Role of Technology in Modern Education".', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 4 DAY)),
(2, 4, 1, 'Quadratic Equations Worksheet', 'Complete all 15 problems provided on the PDF worksheet.', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 DAY));

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

-- Seed Notices
INSERT INTO `notices` (`title`, `content`, `target_role`, `is_important`) VALUES
('Annual Sports Meet Registration', 'All students interested in track and field events must submit their names to physical education department by Friday.', 'All', 1),
('Parent-Teacher Meeting (PTM)', 'Quarterly PTM is scheduled for coming Saturday from 9:00 AM to 1:00 PM.', 'All', 1),
('Faculty Meeting - Mid Term Review', 'All senior teachers are requested to attend the review meeting in Conference Room B at 3:00 PM.', 'Teacher', 0);

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
('school_name', 'RDMA Kids • School Management System'),
('tagline', 'Nurturing Future Leaders with Digital Excellence'),
('address', '123 Education Boulevard, Knowledge City, New Delhi - 110001'),
('phone', '+91 11 2345 6789'),
('email', 'contact@rdmakids.edu'),
('academic_session', '2026-2027');
