<?php
session_start();
require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/HomeController.php';

// get data from controller
$stats = getStatistik($conn);
$images = getGaleriDummy();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desa Cantik Kota Dumai</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <!-- ICON -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- SWIPER -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">


    <style>
        :root {
            --primary: #27548a;
            --primary-dark: #1b3b63;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(14px);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(14px);
        }

        .card {
            border-radius: 18px;
            transition: .3s;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.12);
        }

        .fade {
            opacity: 0;
            transform: translateY(30px);
            transition: .7s;
        }

        .fade.show {
            opacity: 1;
            transform: none;
        }

        .btn-primary {
            background: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .swiper {
            padding-bottom: 20px;
        }

        .card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.15);
        }

        .swiper {
            padding-bottom: 50px;
            /* kasih ruang bawah lebih luas */
        }

        .swiper-pagination {
            bottom: 0 !important;
        }

        .swiper-pagination-bullet {
            background: #999;
            opacity: 0.6;
        }

        .swiper-pagination-bullet-active {
            background: var(--primary);
            opacity: 1;
        }
    </style>
</head>

<body class="text-gray-800">

    <div id="modal" class="fixed inset-0 bg-black/90 hidden items-center justify-center z-50 p-6">

        <div class="relative max-w-5xl w-full">

            <img id="modalImg" class="w-full max-h-[85vh] object-contain rounded-xl">
            <p id="modalCaption" class="text-white text-center mt-4 text-sm"></p>

            <button onclick="closeModal()"
                class="absolute -top-4 -right-4 bg-white text-black w-10 h-10 rounded-full shadow hover:scale-110 transition">
                ✕
            </button>

        </div>

    </div>

    <!-- NAVBAR -->
    <header class="navbar fixed top-0 w-full z-50 shadow-sm">
        <div class="px-6 md:px-12 lg:px-20 flex justify-between items-center py-4">

            <div class="flex items-center gap-3">
                <img src="<?= BASE_URL ?>assets/images/logo.png" class="h-9">
                <img src="<?= BASE_URL ?>assets/images/logo2.png" class="h-9">
                <h1 class="font-semibold text-lg text-[var(--primary-dark)]">Desa Cantik</h1>
            </div>

            <nav class="hidden md:flex gap-8 text-sm font-medium">
                <a href="#beranda">Beranda</a>
                <a href="#tentang">Tentang</a>
                <a href="#statistik">Statistik</a>
                <a href="#galeri">Galeri</a>
            </nav>

            <button id="menuBtn" class="md:hidden text-xl">☰</button>
        </div>

        <div id="mobileMenu" class="hidden px-6 pb-4 space-y-2 text-sm">
            <a href="#beranda">Beranda</a>
            <a href="#tentang">Tentang</a>
            <a href="#statistik">Statistik</a>
            <a href="#galeri">Galeri</a>
            <a href="<?= BASE_URL ?>login" class="font-semibold text-[var(--primary)]">Login</a>
        </div>
    </header>

    <!-- HERO -->
    <section id="beranda" class="relative h-screen flex items-center justify-center text-center pt-20">

        <div class="px-6 md:px-16 lg:px-24 fade max-w-4xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-100 text-xs text-gray-600 mb-4">
                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                Platform Monografi Kelurahan
            </div>

            <h2 class="text-4xl md:text-6xl font-bold text-[var(--primary-dark)] leading-tight mb-6">
                Sistem Informasi Monografi Kelurahan
            </h2>

            <p class="text-gray-600 text-lg md:text-xl mb-10 leading-relaxed">
                Platform resmi untuk pengelolaan data desa/kelurahan secara terstruktur,
                akurat, dan berkelanjutan oleh Badan Pusat Statistik Kota Dumai dalam Pembinaan Desa Cinta Statistik.
            </p>

            <a href="<?= BASE_URL ?>login"
                class="btn-primary text-white px-8 py-4 rounded-lg shadow-md transition duration-300 hover:scale-105 inline-block">
                Masuk ke Sistem
            </a>

        </div>

        <!-- PANAH -->
        <a href="#tentang"
            class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce text-[var(--primary)] text-2xl">
            <i class="fas fa-chevron-down"></i>
        </a>

    </section>

    <!-- TENTANG -->
    <section id="tentang" class="py-24">
        <div class="max-w-7xl mx-auto px-6 md:px-12 lg:px-20 fade">

            <!-- HEADER -->
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h3 class="text-3xl md:text-4xl font-bold text-[var(--primary-dark)] mb-5">
                    Tentang Desa Cantik
                </h3>

                <p class="text-gray-600 leading-relaxed">
                    Desa Cantik merupakan program Badan Pusat Statistik (BPS) yang bertujuan
                    meningkatkan kualitas pengelolaan dan pemanfaatan data di tingkat desa/kelurahan,
                    sehingga pembangunan menjadi lebih tepat sasaran dan berkelanjutan.
                </p>
            </div>

            <!-- MANFAAT -->
            <div class="max-w-5xl mx-auto">

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">

                    <div class="glass p-7 rounded-2xl shadow card text-center">
                        <div
                            class="w-14 h-14 mx-auto flex items-center justify-center rounded-xl bg-[var(--primary)]/10 text-[var(--primary)] text-xl mb-4">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h5 class="font-semibold text-base mb-2">Literasi Statistik</h5>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Meningkatkan pemahaman dan kesadaran statistik masyarakat.
                        </p>
                    </div>

                    <div class="glass p-7 rounded-2xl shadow card text-center">
                        <div
                            class="w-14 h-14 mx-auto flex items-center justify-center rounded-xl bg-[var(--primary)]/10 text-[var(--primary)] text-xl mb-4">
                            <i class="fas fa-database"></i>
                        </div>
                        <h5 class="font-semibold text-base mb-2">Standarisasi Data</h5>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Menjaga kualitas dan konsistensi data antar wilayah.
                        </p>
                    </div>

                    <div class="glass p-7 rounded-2xl shadow card text-center">
                        <div
                            class="w-14 h-14 mx-auto flex items-center justify-center rounded-xl bg-[var(--primary)]/10 text-[var(--primary)] text-xl mb-4">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h5 class="font-semibold text-base mb-2">Pemanfaatan Data</h5>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Mendukung perencanaan pembangunan berbasis data.
                        </p>
                    </div>

                    <div class="glass p-7 rounded-2xl shadow card text-center">
                        <div
                            class="w-14 h-14 mx-auto flex items-center justify-center rounded-xl bg-[var(--primary)]/10 text-[var(--primary)] text-xl mb-4">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="font-semibold text-base mb-2">Agen Statistik</h5>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Membentuk penggerak budaya sadar data di desa/kelurahan.
                        </p>
                    </div>

                </div>

            </div>

        </div>
    </section>

    <!-- STATISTIK -->
    <section id="statistik" class="py-24 text-center">
        <h3 class="text-3xl font-bold text-[var(--primary-dark)] mb-14">
            Statistik Sistem
        </h3>

        <div class="grid md:grid-cols-3 gap-8 px-6">

            <?php
            $statsDisplay = [
                [$stats['total_kelurahan'], "Kelurahan"],
                [$stats['total_data'], "Data Monografi"],
                [$stats['tahun'], "Tahun Berjalan"]
            ];
            ?>

            <?php foreach ($statsDisplay as $s): ?>
                <div class="glass p-10 rounded-xl shadow">

                    <p class="text-5xl font-bold text-[var(--primary)] counter" data-target="<?= $s[0] ?>"
                        data-plus="false">
                        0
                    </p>

                    <p class="text-gray-500 mt-2"><?= $s[1] ?></p>

                </div>
            <?php endforeach; ?>

        </div>
    </section>

    <!-- GALERI -->
    <section id="galeri" class="py-28">
        <div class="max-w-7xl mx-auto px-6 fade">

            <h3 class="text-4xl font-bold text-center text-[var(--primary-dark)] mb-14">
                Galeri Kegiatan
            </h3>

            <?php
            $images = getGaleriDummy();
            ?>

            <div class="swiper mySwiper">
                <div class="swiper-wrapper">

                    <?php foreach ($images as $img): ?>
                        <div class="swiper-slide relative group">
                            <img src="<?= $img['src'] ?>"
                                onclick="openModal('<?= $img['src'] ?>', '<?= htmlspecialchars($img['title'], ENT_QUOTES) ?>')"
                                class="w-full h-60 object-cover">

                            <div
                                class="absolute bottom-0 left-0 w-full text-white text-sm p-2 bg-black/40 opacity-0 group-hover:opacity-100 transition">
                                <?= $img['title'] ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>

                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>

        </div>
    </section>

    <!-- FOOTER -->
    <footer class="relative overflow-hidden">

        <div class="bg-[#0f172a]/90 backdrop-blur-xl text-gray-300 pt-16 pb-8">

            <div
                class="max-w-6xl mx-auto px-6 md:px-10 grid grid-cols-1 md:grid-cols-3 gap-10 md:gap-12 text-center md:text-left">

                <!-- KIRI -->
                <div class="text-center md:text-left">
                    <h4 class="text-white text-lg font-semibold mb-4">BPS Kota Dumai</h4>

                    <div class="text-sm opacity-80 space-y-3 leading-relaxed">

                        <p class="flex justify-center md:justify-start gap-2">
                            <i class="fas fa-map-marker-alt mt-1 text-gray-400"></i>
                            <span>Jl. Tuanku Tambusai Dumai - Riau</span>
                        </p>

                        <p class="flex justify-center md:justify-start gap-2">
                            <i class="fas fa-phone text-gray-400"></i>
                            <span>(0765) 4300005</span>
                        </p>

                        <p class="flex justify-center md:justify-start gap-2">
                            <i class="fas fa-envelope text-gray-400"></i>
                            <span>bps1473@bps.go.id</span>
                        </p>

                    </div>
                </div>

                <!-- TENGAH -->
                <div class="text-center">
                    <h4 class="text-white text-lg font-semibold mb-4">Navigasi</h4>

                    <ul class="text-sm space-y-3">
                        <li>
                            <a href="#beranda" class="hover:text-white transition">
                                Beranda
                            </a>
                        </li>
                        <li>
                            <a href="#tentang" class="hover:text-white transition">
                                Tentang
                            </a>
                        </li>
                        <li>
                            <a href="#galeri" class="hover:text-white transition">
                                Galeri
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- KANAN -->
                <div class="text-center md:text-right">
                    <h4 class="text-white text-lg font-semibold mb-4">Sosial Media</h4>

                    <div class="flex flex-col items-center md:items-end gap-3 text-sm">

                        <a href="https://www.instagram.com/bpskotadumai/"
                            class="flex items-center gap-3 hover:text-white transition">
                            <i class="fab fa-instagram text-lg"></i>
                            <span>Instagram</span>
                        </a>

                        <a href="https://www.youtube.com/@bpskotadumai1473/streams"
                            class="flex items-center gap-3 hover:text-white transition">
                            <i class="fab fa-youtube text-lg"></i>
                            <span>YouTube</span>
                        </a>

                        <a href="https://www.facebook.com/bps.dumai.3/"
                            class="flex items-center gap-3 hover:text-white transition">
                            <i class="fab fa-facebook-f text-lg"></i>
                            <span>Facebook</span>
                        </a>

                    </div>
                </div>

            </div>

            <!-- DIVIDER -->
            <div class="max-w-6xl mx-auto px-6 md:px-10 mt-12">
                <div class="h-px bg-white/10"></div>
            </div>

            <!-- COPYRIGHT -->
            <div class="text-center text-sm mt-6 opacity-70">
                ©
                <?php echo date('Y'); ?> BPS Kota Dumai
            </div>

        </div>

    </footer>

    <script src="<?= BASE_URL ?>assets/js/main.js"></script>

</body>

</html>