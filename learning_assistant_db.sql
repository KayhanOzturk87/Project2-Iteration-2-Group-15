-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 30, 2025 at 03:06 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `learning_assistant_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `CourseMaterials`
--

CREATE TABLE `CourseMaterials` (
  `material_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `uploader_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Courses`
--

CREATE TABLE `Courses` (
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `units_titles_json` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Courses`
--

INSERT INTO `Courses` (`course_id`, `title`, `description`, `category`, `image_url`, `instructor_id`, `units_titles_json`, `created_at`) VALUES
(5, 'Database', 'This course is an introduction to database concepts and the general skills for designing and using databases, with a focus on relational database concepts and techniques. Current industry developments of database systems such as NoSQL databases will be introduced at the end of the course.\r\n\r\nLearning Outcomes\r\nUpon successful completion, students will have the knowledge and skills to:\r\n\r\nInterpret and explain the basic concepts of the relational model and understand its mathematical foundation\r\nApply SQL language to define, query and manipulate a relational database\r\nApply conceptual database modelling methods such as entity-relationship model to design a relational database\r\nResearch, justify and apply database design methods on functional dependencies and normal forms to evaluate the quality of a relational database design\r\nInterpret and discuss query processing and optimisation, transaction and security management in a relational database management system\r\nReflect upon state of the art of database management systems and big data management challenges', 'information technology', '1748532038_Database.jpeg', 2, '[]', '2025-05-29 14:03:23'),
(6, 'Networking', 'This course studies the standard models for the layered approach to communication between autonomous machines in a network, and the main characteristics of data transmission across various physical link types. It considers how to design networks and protocols for diverse situations, analyses several application and support protocols from a distributed systems viewpoint, and identifies significant problem areas in networked communications.\r\n\r\n\r\n\r\nTopics include: communication network architectures, signalling and modulation across physical media, real-world local and wide-area networks, internet protocol fundamentals, performance and monitoring of networks, routing, network security, and application protocols for distributed systems including web, email, video, internet-of-everything and other contemporary network topics.\r\n\r\nLearning Outcomes\r\nUpon successful completion, students will have the knowledge and skills to:\r\n\r\nInterpret, articulate and critically review the layered protocol model.\r\nArticulate, analyse and appraise a number of datalink, network, and transport layer protocols.\r\nProgram network communication services for client/server and other application layouts.\r\nArticulate, analyse and appraise various related technical, administrative and social aspects of specific computer network protocols from standards documents and other primary materials found through research.\r\nDesign, analyse, and recommend networks and services for homes, data centres, IoT/IoE, LANs and WANs.', 'information technology', '1748531966_Networking.jpeg', 2, '[]', '2025-05-29 14:41:06'),
(7, 'Programming', 'This programming course teaches basic concepts in imperative and object-oriented programming and corresponding data structures.\r\n\r\n\r\n\r\nStudents will learn to use an industrial-strength object-oriented programming language and form basic mental models of how computer programs execute and interact with their environment. The course focuses on key aspects of solving programming problems: reasoning about a problem description to design appropriate data representations and function/method descriptions, to find examples, to write, test, debug, and otherwise evaluate the relevant code, and to present and defend their approach.\r\n\r\n\r\n\r\nStudents will learn to effectively use a large standard library and key standard data structures, including lists, trees, hash tables, and graphs. The course also introduces the basics of reasoning about the time and space complexity of algorithms, in particular as related to the above data structures.\r\n\r\n\r\nLearning Outcomes\r\nUpon successful completion, students will have the knowledge and skills to:\r\n\r\nApply fundamental programming concepts, using an object-oriented programming language, to solve practical programming problems\r\nImplement, debug, and evaluate algorithms for solving substantial problems; implement an abstract data type\r\nApply basic algorithmic analysis to simple algorithms; use appropriate algorithmic approaches to solve problems\r\nDesign, implement, and test data structures and code\r\nPresent, explain, evaluate, and defend choices in design and implementations of programs and algorithms', 'information technology', '', 2, '[]', '2025-05-29 14:42:35'),
(8, 'Business Communication', 'The primary aim of this course is to provide students with the skills and knowledge of communication in the business environment. These skills will contribute to professional graduate attributes and assist with the transition to, or back to, the workforce. There is a strong focus on the understanding the theory of communication in the business context and it\'s application to effective business writing at a high level, persuasive and appropriate verbal and non verbal communication, and interpersonal skills across teams and cultures.\r\n\r\nLearning Outcomes\r\nUpon successful completion, students will have the knowledge and skills to:\r\n\r\n define communication and identify the key aspects of fundamental communication theories\r\nidentify business communication issues and find solutions based on communication theory\r\nproduce effective professional documents; \r\napply communication theory in delivering an effective business presentation\r\nimplement basic critical thinking and analytical skills\r\ndemonstrate the ability to effectively communicate ideas and answer questions verbally in-person to a group.', 'business', '1748531943_BusinessCommunication.jpeg', 2, '[]', '2025-05-29 14:43:02'),
(9, 'Organisational Behaviour', 'This course aims to provide an evidence-based understanding of human behaviours and decision-making in organizations. Specifically, the topics of this class are designed to enhance students’ appreciation of theories and theory-informed practices on human performance, work satisfaction, work motivation, organizational teams, group decision-making, and leadership. This class equips students with the fundamental theoretical understanding of human behaviours in the workplace, which can be used to resolve the most common workplace issues and problems. \r\n\r\n \r\n\r\nLearning Outcomes\r\nUpon successful completion, students will have the knowledge and skills to:\r\n\r\nlist relevant theories, models, and methods for organizational behaviours (Remember + Ask)\r\naccurately define different theories, models, and methods for organizational behaviours (Remember + Ask) \r\ncritically discuss human behaviours in the workplace from a relevant theoretical standpoint (Acquire + Analyse) \r\nrecall the weaknesses and strengths of different theories relevant to organizational behaviour (Understand + Aggregate) \r\ngauge the impacts of their own experience from the relevant theoretical framework (Appraise + Analyse)  \r\napply theoretical models and concepts to current organizational practices, problems, and issues (Apply + Adapt) \r\ndevelop a research proposal in the area of organizational behaviour (Apply + Create) \r\ndevise evidence-based strategies and recommendations to address an organizational problem (Assess + Create).', 'business', '1748531915_OrganisationalBehaviour.jpeg', 2, '[]', '2025-05-29 14:43:39'),
(11, 'Introduction to Accounting', 'This course provides an introduction to the meaning and role of accounting in the larger context of a changing and interconnected world of people, organisations and society. It covers the identifications of accounting information, terminology, and techniques, the applications of key management accounting techniques, and a practical understanding and analysis of financial accounting information and reports. This unit is designed to develop students’ skills to apply the technical aspects of accounting and thereby recognise its significant influences on organisations and societies.', 'accounting', '1748531896_accounting.jpeg', 2, '[]', '2025-05-29 14:50:49'),
(12, 'Cybersecurity', 'The Cyber Security major provides principles, theories and practical skills required to analyse and manage current cybersecurity situations. Students will learn how to reverse-engineer a given system and to identify and test vulnerabilities. The addressed systems cover the complete range of architectures from individual controllers to the internet.\r\n\r\nLearning Outcomes\r\nUnderstand the principles, practice and issues associated with the field of cyber security\r\nApply a range of modelling, management, analytics and visualisation techniques to handle relevant defensive as well as offensive cyber security operations\r\nReverse-engineer systems based on minimal outside information\r\nCommunicate and present their knowledge of cyber security to diverse audiences', 'information technology', '1748531883_cyber.jpeg', 2, '[\"week1 \"]', '2025-05-29 15:12:49');

-- --------------------------------------------------------

--
-- Table structure for table `Enrollments`
--

CREATE TABLE `Enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `progress` int(11) DEFAULT 0,
  `quiz_score` int(11) DEFAULT NULL,
  `last_accessed` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Quizzes`
--

CREATE TABLE `Quizzes` (
  `quiz_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `questions_json` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Quizzes`
--

INSERT INTO `Quizzes` (`quiz_id`, `course_id`, `title`, `questions_json`, `created_at`) VALUES
(3, 11, 'Week  1', '[]', '2025-05-29 14:52:53');

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher') NOT NULL,
  `school_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`user_id`, `name`, `email`, `password`, `role`, `school_id`, `created_at`) VALUES
(1, 'kayhan ozturk', 'kayhanozturkk@yahoo.com', '$2y$10$IhuJ44lnvv8ro4p2DX2QAueSCo6LfyXyBMFAtYSv0y1eG1YEYu/D2', 'student', '123', '2025-05-19 10:28:10'),
(2, 'Dr. Kayhan OZTURK', 'ozturkkayhan@gmail.com', '$2y$10$W7j4BHf70m4Z2WGJFoYvbeimfoI4JiC3uwJW/Bk/lBRX.s8BXMWYO', 'teacher', NULL, '2025-05-19 10:30:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `CourseMaterials`
--
ALTER TABLE `CourseMaterials`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `uploader_id` (`uploader_id`);

--
-- Indexes for table `Courses`
--
ALTER TABLE `Courses`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indexes for table `Enrollments`
--
ALTER TABLE `Enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD UNIQUE KEY `user_course_enrollment` (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `Quizzes`
--
ALTER TABLE `Quizzes`
  ADD PRIMARY KEY (`quiz_id`),
  ADD UNIQUE KEY `course_id` (`course_id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `school_id_role` (`school_id`,`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `CourseMaterials`
--
ALTER TABLE `CourseMaterials`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `Courses`
--
ALTER TABLE `Courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `Enrollments`
--
ALTER TABLE `Enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Quizzes`
--
ALTER TABLE `Quizzes`
  MODIFY `quiz_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `CourseMaterials`
--
ALTER TABLE `CourseMaterials`
  ADD CONSTRAINT `coursematerials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `coursematerials_ibfk_2` FOREIGN KEY (`uploader_id`) REFERENCES `Users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `Courses`
--
ALTER TABLE `Courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `Users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `Enrollments`
--
ALTER TABLE `Enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Quizzes`
--
ALTER TABLE `Quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
