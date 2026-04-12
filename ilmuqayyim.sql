-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2026 at 12:55 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ilmuqayyim`
--

-- --------------------------------------------------------

--
-- Table structure for table `chapters`
--

CREATE TABLE `chapters` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `order_num` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chapters`
--

INSERT INTO `chapters` (`id`, `subject_id`, `title`, `video_url`, `order_num`, `created_at`) VALUES
(1, 1, 'Eksponen', 'https://www.youtube.com/embed/AlrOq3W7IZ4', 1, '2026-03-21 03:57:55'),
(2, 1, 'Sistem Persamaan Linear', 'https://www.youtube.com/embed/AlrOq3W7IZ4', 2, '2026-03-21 03:57:55'),
(3, 1, 'Barisan dan Deret', 'https://www.youtube.com/embed/AlrOq3W7IZ4', 3, '2026-03-21 03:57:55'),
(4, 2, 'Teks Anekdot', 'https://www.youtube.com/embed/QgpSSZBvTjE', 1, '2026-03-21 03:57:55'),
(5, 2, 'Teks Eksposisi', 'https://www.youtube.com/embed/QgpSSZBvTjE', 2, '2026-03-21 03:57:55');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_options`
--

CREATE TABLE `quiz_options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` varchar(500) NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_options`
--

INSERT INTO `quiz_options` (`id`, `question_id`, `option_text`, `is_correct`) VALUES
(1, 1, '6', 0),
(2, 1, '8', 1),
(3, 1, '9', 0),
(4, 1, '12', 0),
(5, 2, '4', 0),
(6, 2, '5', 0),
(7, 2, '6', 1),
(8, 2, '8', 0),
(9, 3, '12', 0),
(10, 3, '14', 1),
(11, 3, '16', 0),
(12, 3, '18', 0),
(13, 4, 'Bensin', 0),
(14, 4, 'Batu bara', 0),
(15, 4, 'Energi Surya', 1),
(16, 4, 'Minyak bumi', 0),
(17, 5, 'Ruralisasi', 0),
(18, 5, 'Urbanisasi', 1),
(19, 5, 'Imigrasi', 0),
(20, 5, 'Transmigrasi', 0),
(25, 7, 'gtw', 0),
(26, 7, 'R', 1),
(27, 7, 'hehe', 0),
(28, 7, 'adasdadwoq', 0);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`id`, `subject_id`, `question`, `created_at`) VALUES
(1, 1, 'Berapakah hasil dari 2³?', '2026-03-21 03:57:55'),
(2, 1, 'Jika x + y = 10 dan x - y = 2, berapakah nilai x?', '2026-03-21 03:57:55'),
(3, 1, 'Suku ke-5 dari barisan aritmatika 2, 5, 8, 11, ... adalah?', '2026-03-21 03:57:55'),
(4, 4, 'Contoh energi terbarukan yang ramah lingkungan adalah...', '2026-03-21 03:57:55'),
(5, 4, 'Proses migrasi dari desa ke kota disebut...', '2026-03-21 03:57:55'),
(7, 9, 'Siapa aku?', '2026-03-29 23:47:52');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_results`
--

CREATE TABLE `quiz_results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `total` int(11) NOT NULL DEFAULT 0,
  `taken_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_results`
--

INSERT INTO `quiz_results` (`id`, `user_id`, `subject_id`, `score`, `total`, `taken_at`) VALUES
(1, 7, 9, 0, 1, '2026-03-21 08:04:22'),
(2, 7, 9, 1, 1, '2026-03-21 08:04:26'),
(3, 7, 9, 0, 1, '2026-03-29 23:48:07'),
(4, 7, 9, 1, 1, '2026-03-29 23:48:11');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `slug`, `image`, `description`, `created_at`) VALUES
(1, 'Matematika', 'matematika', 'assets/images/subjects/math.png', 'Pelajari konsep dasar hingga lanjutan matematika.', '2026-03-21 03:57:55'),
(2, 'Bahasa Indonesia', 'bahasa-indonesia', 'assets/images/subjects/indonesia.jpg', 'Tata bahasa, sastra, dan keterampilan menulis.', '2026-03-21 03:57:55'),
(3, 'Bahasa Inggris', 'bahasa-inggris', 'assets/images/subjects/english.png', 'Membaca, menulis, mendengar, dan berbicara.', '2026-03-21 03:57:55'),
(4, 'IPAS', 'ipas', 'assets/images/subjects/ipas.png', 'Ilmu alam dan sosial untuk memahami kehidupan.', '2026-03-21 03:57:55'),
(5, 'Pendidikan Pancasila', 'ppkn', 'assets/images/subjects/ppkn.png', 'Nilai Pancasila, hak dan kewajiban warga negara.', '2026-03-21 03:57:55'),
(6, 'Sejarah', 'sejarah', 'assets/images/subjects/sejarah.jpg', 'Perjalanan sejarah dunia dan Indonesia.', '2026-03-21 03:57:55'),
(7, 'PJOK', 'pjok', 'assets/images/subjects/pjok.png', 'Aktivitas fisik, olahraga, dan gaya hidup sehat.', '2026-03-21 03:57:55'),
(8, 'PAI', 'pai', 'assets/images/subjects/pai.webp', 'Akidah, ibadah, akhlak, dan sejarah Islam.', '2026-03-21 03:57:55'),
(9, 'Bahasa Arab', 'bahasa-arab', 'assets/images/subjects/arabic.jpg', 'Tata bahasa, kosakata, dan komunikasi Arab.', '2026-03-21 03:57:55'),
(10, 'PPLG', 'pplg', 'assets/images/subjects/pplg.jpg', 'Pemrograman, pengembangan aplikasi, dan gim.', '2026-03-21 03:57:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('siswa','guru','admin') NOT NULL DEFAULT 'siswa',
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `avatar`, `created_at`) VALUES
(7, 'admin', 'admin@iqis.sch.id', '$2y$10$XLuVmmFGiaLuCRAlXdfkauIyBGqU65qe9uJQXOq6EbV90MROqmfWq', 'admin', 'avatar_7_1774756765.jpg', '2026-03-21 05:38:28'),
(8, 'siswa', 'siswa@iqis.sch.id', '$2y$10$vCAU23iM2ke2vOE.4htrquHIfVtLLBs4m7/F6yaquaezIxQP4cHxW', 'siswa', 'avatar_8_1774756872.jpg', '2026-03-21 05:38:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chapters`
--
ALTER TABLE `chapters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `quiz_options`
--
ALTER TABLE `quiz_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chapters`
--
ALTER TABLE `chapters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `quiz_options`
--
ALTER TABLE `quiz_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chapters`
--
ALTER TABLE `chapters`
  ADD CONSTRAINT `chapters_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_options`
--
ALTER TABLE `quiz_options`
  ADD CONSTRAINT `quiz_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD CONSTRAINT `quiz_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_results_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
