-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 11 Nov 2026 pada 13.41
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
(1, 2, 'Membuat kuesioner', 1),
(2, 2, 'Ngurus surat', 0),
(3, 1, 'Buat laporan', 1),
(4, 1, 'buat video', 1),
(5, 5, 'RI', 1),
(6, 5, 'MR', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tugas`
--

CREATE TABLE `tugas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `matkul` varchar(100) NOT NULL,
  `deadline` date NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tingkat_kesulitan` varchar(20) DEFAULT 'Sedang',
  `url_link` varchar(255) DEFAULT NULL,
  `is_pinned` tinyint(1) DEFAULT 0,
  `status` varchar(50) DEFAULT 'Belum',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tugas`
--

INSERT INTO `tugas` (`id`, `user_id`, `judul`, `matkul`, `deadline`, `deskripsi`, `tingkat_kesulitan`, `url_link`, `is_pinned`, `status`, `created_at`) VALUES
(1, 3, 'TR2', 'Pemrograman', '2026-10-11', 'Tugasnya membuat flowchart', 'Sedang', 'https://share.google/69LZtsEQgbgdA0ac8', 1, 'Selesai', '2026-11-10 13:41:14'),
(2, 3, 'MR', 'CC', '2026-12-11', 'ke sekolah', 'Sulit', '', 0, 'Belum', '2026-11-10 14:14:29'),
(3, 3, 'RPP', 'Pemrograman', '2026-12-11', 'dpolkxa', 'Mudah', 'https://share.google/69LZtsEQgbgdA0ac8', 0, 'Belum', '2026-11-10 15:42:19'),
(4, 3, 'Penting', 'MPTI', '2026-11-11', 'KDJWS', 'Sedang', '', 0, 'Belum', '2026-11-11 02:01:41'),
(5, 3, 'RPP', 'CC', '2026-11-10', 'KDE', 'Sedang', '', 0, 'Belum', '2026-11-11 02:02:51');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'Wasti', '$2y$10$rEkhphVvThSfCvzZGUi6L.kJyDdhxWxyCLux4eO6LilrVioicFso2', '2026-11-09 03:15:59'),
(3, 'wasti002', '$2y$10$neazCPYUHC2GXOf0ZsJiXOLqKedJOh.h9py/18U0k8UMxQi2pteaS', '2026-11-09 03:28:35'),
(4, 'Cet', '$2y$10$.Vh4o38pWKUcfGA22dJfmeiARlvjwDxwTL73EEu3WskFkiZa4awVW', '2026-11-11 12:13:27'),
(6, 'jsaisk', '$2y$10$91RJlLLN3UIJhc1LXqd9AOBQIboV3ED/ooGtAxyZGYDu3iUwypLr2', '2026-11-11 12:34:08'),
(7, 'wasti00231', '$2y$10$Uu2xwdjHmrpY9kuPD7be5OpgFJwIZPeUH3GnXQl7.hWCO3s1LtySu', '2026-11-11 12:35:00');

--
-- Indexes for dumped tables
--

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
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT untuk tabel `kritik_saran`
--
ALTER TABLE `kritik_saran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `sub_tugas`
--
ALTER TABLE `sub_tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `tugas`
--
ALTER TABLE `tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  ADD CONSTRAINT `tugas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
