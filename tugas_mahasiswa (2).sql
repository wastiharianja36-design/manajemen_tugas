-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 20 Nov 2026 pada 07.52
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tugas_mahasiswa`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `kantong_tugas`
--

CREATE TABLE `kantong_tugas` (
  `id` int(11) NOT NULL,
  `dosen_id` int(11) NOT NULL,
  `jenis_tugas` varchar(100) NOT NULL,
  `judul_kantong` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `deadline` date NOT NULL,
  `rubrik_nilai` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kantong_tugas`
--

INSERT INTO `kantong_tugas` (`id`, `dosen_id`, `jenis_tugas`, `judul_kantong`, `deskripsi`, `deadline`, `rubrik_nilai`, `created_at`) VALUES
(1, 8, 'Tugas Rutin', 'TR2', '', '2026-11-26', '[{\"kriteria\":\"Isi \\/ Substansi\",\"bobot\":60},{\"kriteria\":\"Kerapian & Format\",\"bobot\":40}]', '2026-11-20 06:41:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kritik_saran`
--

CREATE TABLE `kritik_saran` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `pesan` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kritik_saran`
--

INSERT INTO `kritik_saran` (`id`, `user_id`, `kategori`, `pesan`, `created_at`) VALUES
(1, 3, 'Kritik Tampilan', 'jelek nih', '2026-11-10 13:53:05'),
(2, 3, 'Kritik Tampilan', 'jelek nih', '2026-11-10 13:55:13'),
(3, 3, 'Kritik Tampilan', 'kdxaz', '2026-11-10 15:42:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sub_tugas`
--

CREATE TABLE `sub_tugas` (
  `id` int(11) NOT NULL,
  `tugas_id` int(11) NOT NULL,
  `judul_sub` varchar(255) NOT NULL,
  `is_completed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `sub_tugas`
--

INSERT INTO `sub_tugas` (`id`, `tugas_id`, `judul_sub`, `is_completed`) VALUES
(7, 6, 'Merancang WEB', 0),
(8, 6, 'Membuat data Excel', 0),
(9, 6, 'Menyusun Laporan', 0),
(10, 6, 'Presentasi', 0),
(11, 8, 'Merancang web', 0),
(12, 8, 'membuat web', 0),
(13, 8, 'membuat riset', 0),
(14, 8, 'revisi 1', 0),
(15, 8, 'revisi 2', 0),
(16, 8, 'revisi 3', 0),
(17, 8, 'laporan final', 0),
(18, 9, 'Membuat RPP', 1),
(19, 9, 'Membuat alur video', 1),
(20, 9, 'Membuat naskah video', 1),
(21, 9, 'Membuat Video', 1),
(22, 9, 'Revisi RPP', 0),
(23, 10, 'Menentukan judul', 0),
(24, 10, 'Memilih rumah jurnal', 0),
(25, 10, 'Membuat isi metode', 0),
(26, 10, 'laporan mingdep', 0),
(27, 10, 'revisi', 0),
(28, 10, 'final', 0),
(29, 11, 'Merancang pembuatan Sensor', 0),
(30, 11, 'Membuat produk', 0),
(31, 11, 'Merapikan produk biar lebih bagus', 0),
(32, 11, 'Membuat laporan dan ppt', 0),
(33, 11, 'Presentasi/demonstrasi', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tugas`
--

CREATE TABLE `tugas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dosen_id` int(11) DEFAULT NULL,
  `kantong_id` int(11) DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `matkul` varchar(100) NOT NULL,
  `deadline` date NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tingkat_kesulitan` varchar(20) DEFAULT 'Sedang',
  `url_link` varchar(255) DEFAULT NULL,
  `file_tugas` varchar(255) DEFAULT NULL,
  `is_pinned` tinyint(1) DEFAULT 0,
  `status` varchar(50) DEFAULT 'Belum',
  `status_review` enum('belum_direview','revisi','sudah_direvisi','acc') NOT NULL DEFAULT 'belum_direview',
  `catatan_dosen` text DEFAULT NULL,
  `detail_nilai` text DEFAULT NULL,
  `nilai_akhir` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tugas`
--

INSERT INTO `tugas` (`id`, `user_id`, `dosen_id`, `kantong_id`, `judul`, `matkul`, `deadline`, `deskripsi`, `tingkat_kesulitan`, `url_link`, `file_tugas`, `is_pinned`, `status`, `status_review`, `catatan_dosen`, `detail_nilai`, `nilai_akhir`, `created_at`) VALUES
(6, 3, NULL, NULL, 'Membuat web kos', 'SPK', '2026-12-07', 'Web kos\r\nKel. 8', 'Sulit', '', NULL, 0, 'Belum', 'belum_direview', NULL, NULL, NULL, '2026-11-13 07:23:29'),
(8, 3, NULL, NULL, 'Web Manajemen Tugas', 'MPTI', '2026-11-25', 'Membuat Web dengan Cetrin', 'Sulit', '', NULL, 0, 'Belum', 'belum_direview', NULL, NULL, NULL, '2026-11-13 07:26:09'),
(9, 3, NULL, NULL, 'RPP', 'Micro Teaching', '2026-11-30', 'MEMBUAT VIDEO MICROTEACHING', 'Sedang', '', NULL, 0, 'Belum', 'belum_direview', NULL, NULL, NULL, '2026-11-13 07:30:20'),
(10, 3, NULL, NULL, 'Artikel', 'Cloud Computing', '2026-12-04', 'Membuat artikel berdasarkan RI\r\nKelompok 6', 'Sulit', '', NULL, 0, 'Belum', 'belum_direview', NULL, NULL, NULL, '2026-11-13 07:32:07'),
(11, 3, NULL, NULL, 'Projek IoT', 'IoT', '2026-12-02', 'Membuat sensor kebakaran berbasis IoT', 'Sulit', '', NULL, 0, 'Belum', 'belum_direview', NULL, NULL, NULL, '2026-11-13 07:34:53'),
(12, 3, 8, 1, 'TR2', 'Matematika', '2026-11-26', 'Soal\"', 'Sedang', '', 'tugas_3_1795156948.pdf', 0, 'Belum', 'revisi', 'JIDAXLK', '[{\"kriteria\":\"Isi \\/ Substansi\",\"bobot\":60,\"nilai\":80},{\"kriteria\":\"Kerapian & Format\",\"bobot\":40,\"nilai\":70}]', 76.00, '2026-11-20 06:23:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','dosen') NOT NULL DEFAULT 'mahasiswa',
  `matkul_ampu` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `nama_lengkap`, `password`, `role`, `matkul_ampu`, `created_at`) VALUES
(1, 'Wasti', NULL, '$2y$10$rEkhphVvThSfCvzZGUi6L.kJyDdhxWxyCLux4eO6LilrVioicFso2', 'mahasiswa', NULL, '2026-11-09 03:15:59'),
(3, 'wasti002', NULL, '$2y$10$neazCPYUHC2GXOf0ZsJiXOLqKedJOh.h9py/18U0k8UMxQi2pteaS', 'mahasiswa', NULL, '2026-11-09 03:28:35'),
(4, 'Cet', NULL, '$2y$10$.Vh4o38pWKUcfGA22dJfmeiARlvjwDxwTL73EEu3WskFkiZa4awVW', 'mahasiswa', NULL, '2026-11-11 12:13:27'),
(6, 'jsaisk', NULL, '$2y$10$91RJlLLN3UIJhc1LXqd9AOBQIboV3ED/ooGtAxyZGYDu3iUwypLr2', 'mahasiswa', NULL, '2026-11-11 12:34:08'),
(7, 'wasti00231', NULL, '$2y$10$Uu2xwdjHmrpY9kuPD7be5OpgFJwIZPeUH3GnXQl7.hWCO3s1LtySu', 'mahasiswa', NULL, '2026-11-11 12:35:00'),
(8, 'Cahyani', 'Cahyani Harianja', '$2y$10$oOrXFailz3JLK7ouL3fly.SRexVE1xH6oGBemX7p0CFtOJLZzrbuG', 'dosen', 'Matematika', '2026-11-20 06:22:10');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `kantong_tugas`
--
ALTER TABLE `kantong_tugas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kritik_saran`
--
ALTER TABLE `kritik_saran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `sub_tugas`
--
ALTER TABLE `sub_tugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tugas_id` (`tugas_id`);

--
-- Indeks untuk tabel `tugas`
--
ALTER TABLE `tugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_tugas_dosen` (`dosen_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `kantong_tugas`
--
ALTER TABLE `kantong_tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `kritik_saran`
--
ALTER TABLE `kritik_saran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `sub_tugas`
--
ALTER TABLE `sub_tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT untuk tabel `tugas`
--
ALTER TABLE `tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `kritik_saran`
--
ALTER TABLE `kritik_saran`
  ADD CONSTRAINT `kritik_saran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `sub_tugas`
--
ALTER TABLE `sub_tugas`
  ADD CONSTRAINT `sub_tugas_ibfk_1` FOREIGN KEY (`tugas_id`) REFERENCES `tugas` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tugas`
--
ALTER TABLE `tugas`
  ADD CONSTRAINT `fk_tugas_dosen` FOREIGN KEY (`dosen_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tugas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
