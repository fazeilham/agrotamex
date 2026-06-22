-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 18, 2026 at 03:14 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_produktivitas`
--

-- --------------------------------------------------------

--
-- Table structure for table `aktivitas`
--

CREATE TABLE `aktivitas` (
  `id_aktivitas` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jenis_pekerjaan` varchar(100) NOT NULL,
  `hasil_kerja` int(11) NOT NULL,
  `target_kerja` int(11) NOT NULL,
  `satuan` varchar(20) DEFAULT NULL,
  `foto_bukti` varchar(255) NOT NULL,
  `status_verifikasi` enum('Pending','Disetujui','Ditolak') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aktivitas`
--

INSERT INTO `aktivitas` (`id_aktivitas`, `id_user`, `tanggal`, `jenis_pekerjaan`, `hasil_kerja`, `target_kerja`, `satuan`, `foto_bukti`, `status_verifikasi`) VALUES
(1, 1, '2026-06-04', 'Pemanenan Kelapa Sawit', 8, 35, NULL, '1780586675_WhatsApp Image 2026-06-04 at 21.20.04.jpeg', 'Disetujui'),
(2, 1, '2026-06-05', 'Pemupukan Blok Lahan', 2, 2, 'Hektar', '1780666123_bukti_lapangan_1780666100887.jpg', 'Disetujui'),
(3, 1, '2026-06-05', 'Pemupukan Blok Lahan', 2, 2, 'Hektar', '1780666231_bukti_lapangan_1780666227483.jpg', 'Disetujui'),
(4, 1, '2026-06-05', 'Pemupukan Blok Lahan', 1, 2, 'Hektar', '1780666267_bukti_lapangan_1780666259342.jpg', 'Ditolak');

-- --------------------------------------------------------

--
-- Table structure for table `target_harian`
--

CREATE TABLE `target_harian` (
  `id_target` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `target_ton` decimal(10,2) NOT NULL DEFAULT 35.00,
  `jenis_pekerjaan` varchar(100) NOT NULL,
  `satuan` varchar(20) NOT NULL DEFAULT 'Ton',
  `diupdate_oleh` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `target_harian`
--

INSERT INTO `target_harian` (`id_target`, `tanggal`, `target_ton`, `jenis_pekerjaan`, `satuan`, `diupdate_oleh`, `updated_at`) VALUES
(1, '2026-06-05', 2.00, 'Pemupukan Blok Lahan', 'Hektar', 2, '2026-06-05 13:19:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('manajer','karyawan') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(1, 'budi_karyawan', '123456', 'Budi Santoso', 'karyawan'),
(2, 'andi_manajer', '123456', 'Andi Wijaya', 'manajer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aktivitas`
--
ALTER TABLE `aktivitas`
  ADD PRIMARY KEY (`id_aktivitas`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `target_harian`
--
ALTER TABLE `target_harian`
  ADD PRIMARY KEY (`id_target`),
  ADD UNIQUE KEY `tanggal` (`tanggal`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aktivitas`
--
ALTER TABLE `aktivitas`
  MODIFY `id_aktivitas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `target_harian`
--
ALTER TABLE `target_harian`
  MODIFY `id_target` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aktivitas`
--
ALTER TABLE `aktivitas`
  ADD CONSTRAINT `aktivitas_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
