<!-- â•â•â•â•â•â•â•â•â•â•â• LANDING PAGE WRAPPER â•â•â•â•â•â•â•â•â•â•â• -->
<div id="appLanding" class="app-landing">

<!-- â•â•â•â•â•â•â•â•â•â•â• NAV â•â•â•â•â•â•â•â•â•â•â• -->
<nav class="nav" id="navbar">
  <div class="nav-logo gradient-text" onclick="scrollToSection('hero')">SoftSale</div>
  <div class="nav-links" id="navLinks">
    <button class="nav-link" onclick="scrollToSection('fitur')">Fitur</button>
    <button class="nav-link" onclick="scrollToSection('harga')">Harga</button>
    <button class="nav-link" onclick="scrollToSection('tentang')">Tentang</button>
    <button class="nav-link" onclick="scrollToSection('kontak')">Kontak</button>
  </div>
  <div class="nav-cta">
    <button class="btn btn-secondary" onclick="openModal('login')" style="padding:8px 18px; font-size:13px;">Masuk</button>
    <button class="btn btn-primary" onclick="openModal('register')" style="padding:8px 18px; font-size:13px;">Daftar</button>
  </div>
  <button class="nav-hamburger" onclick="toggleMobileMenu()">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
  </button>
</nav>

<!-- Mobile overlay -->
<div class="nav-mobile-overlay" id="mobileMenu">
  <button class="nav-mobile-close" onclick="toggleMobileMenu()">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
  </button>
  <button class="nav-mobile-link" onclick="scrollToSection('fitur'); toggleMobileMenu()">Fitur</button>
  <button class="nav-mobile-link" onclick="scrollToSection('harga'); toggleMobileMenu()">Harga</button>
  <button class="nav-mobile-link" onclick="scrollToSection('tentang'); toggleMobileMenu()">Tentang</button>
  <button class="nav-mobile-link" onclick="scrollToSection('kontak'); toggleMobileMenu()">Kontak</button>
  <div style="margin-top:20px; display:flex; gap:10px;">
    <button class="btn btn-secondary" onclick="openModal('login'); toggleMobileMenu()">Masuk</button>
    <button class="btn btn-primary" onclick="openModal('register'); toggleMobileMenu()">Daftar</button>
  </div>
</div>

<!-- â•â•â•â•â•â•â•â•â•â•â• HERO â•â•â•â•â•â•â•â•â•â•â• -->
<section class="hero" id="hero">
  <div class="hero-glow"></div>
  <div class="hero-grid"></div>
  <div class="hero-particles" id="particles"></div>
  <div class="hero-content">
    <div class="badge-label reveal" style="background:rgba(56,126,245,0.1); border:1px solid rgba(56,126,245,0.2); color:var(--accent-1);">
      âœ¦ Solusi Bisnis Digital Terdepan
    </div>
    <h1 class="reveal reveal-delay-1">Kelola Bisnis Anda <span class="gradient-text">Tanpa Batas</span></h1>
    <p class="hero-sub reveal reveal-delay-2">
      Platform manajemen bisnis all-in-one yang membantu Anda mengelola operasional, keuangan, dan tim dari satu dashboard terpadu.
    </p>
    <div class="hero-actions reveal reveal-delay-3">
      <button class="btn btn-primary btn-lg" onclick="openModal('register')">
        Mulai Gratis 14 Hari
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </button>
      <button class="btn btn-secondary btn-lg">Lihat Demo</button>
    </div>
    <div class="hero-stats reveal reveal-delay-4">
      <div>
        <div class="hero-stat-n" data-count="500">0</div>
        <div class="hero-stat-l">Klien Aktif</div>
      </div>
      <div>
        <div class="hero-stat-n">99.9%</div>
        <div class="hero-stat-l">Uptime</div>
      </div>
      <div>
        <div class="hero-stat-n">24/7</div>
        <div class="hero-stat-l">Support</div>
      </div>
    </div>
  </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â• FITUR â•â•â•â•â•â•â•â•â•â•â• -->
<section id="fitur" class="features-section">
  <div class="container">
    <div style="text-align:center;" class="reveal">
      <div class="badge-label" style="background:rgba(56,126,245,0.08); color:var(--accent-1);">Fitur Unggulan</div>
      <h2 class="section-title">Semua yang bisnis Anda butuhkan</h2>
      <p class="section-sub">Dirancang modular agar fleksibel mengikuti skala bisnis Anda, dari UMKM hingga korporasi.</p>
    </div>
    <div class="features-grid" id="featuresGrid"></div>
  </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â• HARGA â•â•â•â•â•â•â•â•â•â•â• -->
<section id="harga" class="pricing-section">
  <div class="container">
    <div style="text-align:center;" class="reveal">
      <div class="badge-label" style="background:rgba(6,191,212,0.08); color:var(--accent-2);">Harga Transparan</div>
      <h2 class="section-title">Pilih paket yang tepat</h2>
      <p class="section-sub">Tanpa biaya tersembunyi. Upgrade atau downgrade kapan saja.</p>
    </div>
    <div class="pricing-grid" id="pricingGrid"></div>
  </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â• TESTIMONIAL â•â•â•â•â•â•â•â•â•â•â• -->
<section class="testi-section">
  <div class="container">
    <div style="text-align:center;" class="reveal">
      <div class="badge-label" style="background:rgba(16,185,129,0.08); color:var(--green);">Testimonial</div>
      <h2 class="section-title">Dipercaya ratusan bisnis</h2>
      <p class="section-sub">Dengarkan langsung dari mereka yang sudah merasakan manfaatnya.</p>
    </div>
    <div class="testi-grid" id="testiGrid"></div>
  </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â• TENTANG â•â•â•â•â•â•â•â•â•â•â• -->
<section id="tentang" class="about-section">
  <div class="container">
    <div class="about-grid">
      <div class="about-left reveal">
        <div class="badge-label" style="background:rgba(245,166,35,0.08); color:var(--amber);">Tentang Kami</div>
        <h2 class="section-title" style="font-size:34px;">Dibangun oleh praktisi,<br>untuk praktisi bisnis</h2>
        <p>SoftSale lahir dari pengalaman nyata mengelola operasional bisnis digital di Indonesia. Kami memahami tantangan yang dihadapi UMKM hingga korporasi â€” mulai dari pencatatan manual yang rawan error, hingga kebutuhan akan sistem terintegrasi.</p>
        <p>Tim kami terdiri dari engineer dan konsultan bisnis berpengalaman yang berkomitmen menghadirkan solusi teknologi berkualitas enterprise dengan harga terjangkau.</p>
        <div class="about-stats">
          <div>
            <div class="about-stat-n" data-count="5">0</div>
            <div class="about-stat-l">Tahun Pengalaman</div>
          </div>
          <div>
            <div class="about-stat-n" data-count="20">0</div>
            <div class="about-stat-l">Tim Profesional</div>
          </div>
          <div>
            <div class="about-stat-n">99%</div>
            <div class="about-stat-l">Kepuasan Client</div>
          </div>
        </div>
      </div>
      <div class="about-values" id="aboutValues"></div>
    </div>
  </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â• AFFILIATE CTA â•â•â•â•â•â•â•â•â•â•â• -->
<section class="affiliate-section">
  <div class="container">
    <div class="glass affiliate-banner reveal">
      <div class="affiliate-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--accent-1)" stroke-width="1.8" stroke-linecap="round"><path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
      </div>
      <h2>Program Affiliate</h2>
      <p>Dapatkan komisi hingga 20% untuk setiap penjualan melalui link referral Anda. Bergabung sekarang dan mulai hasilkan penghasilan tambahan!</p>
      <button class="btn btn-primary btn-lg" onclick="openModal('register','affiliate')">
        Daftar Sebagai Affiliate
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </button>
    </div>
  </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â• KONTAK â•â•â•â•â•â•â•â•â•â•â• -->
<section id="kontak" class="contact-section">
  <div class="container">
    <div style="text-align:center;" class="reveal">
      <div class="badge-label" style="background:rgba(240,104,104,0.08); color:var(--rose);">Kontak</div>
      <h2 class="section-title">Hubungi Kami</h2>
      <p class="section-sub">Punya pertanyaan atau butuh konsultasi? Tim kami siap membantu Anda.</p>
    </div>
    <div class="contact-grid" id="contactGrid"></div>
  </div>
</section>

<!-- â•â•â•â•â•â•â•â•â•â•â• FOOTER â•â•â•â•â•â•â•â•â•â•â• -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-brand gradient-text">SoftSale</div>
        <p class="footer-desc">Platform manajemen bisnis all-in-one untuk era digital. Dibuat dengan teknologi terdepan untuk bisnis Indonesia.</p>
      </div>
      <div>
        <div class="footer-col-title">Produk</div>
        <span class="footer-link" onclick="scrollToSection('fitur')">Fitur</span>
        <span class="footer-link" onclick="scrollToSection('harga')">Harga</span>
        <span class="footer-link">Demo</span>
        <span class="footer-link">Changelog</span>
      </div>
      <div>
        <div class="footer-col-title">Perusahaan</div>
        <span class="footer-link" onclick="scrollToSection('tentang')">Tentang</span>
        <span class="footer-link">Karir</span>
        <span class="footer-link">Blog</span>
        <span class="footer-link" onclick="scrollToSection('kontak')">Kontak</span>
      </div>
      <div>
        <div class="footer-col-title">Legal</div>
        <span class="footer-link">Kebijakan Privasi</span>
        <span class="footer-link">Syarat & Ketentuan</span>
        <span class="footer-link">Lisensi</span>
        <span class="footer-link">SLA</span>
      </div>
    </div>
    <div class="footer-bottom">Â© 2026 SoftSale. All rights reserved.</div>
  </div>
</footer>

</div><!-- /appLanding -->

<!-- â•â•â•â•â•â•â•â•â•â•â• CLIENT DASHBOARD â•â•â•â•â•â•â•â•â•â•â• -->
<div id="dashClient" class="app-dashboard">
  <aside class="dash-sidebar">
    <div class="dash-sidebar-brand"><div class="gradient-text" style="font-size:18px;font-weight:800;">SoftSale</div><span>Client Portal</span></div>
    <div class="dash-nav">
      <button class="dash-nav-item active" onclick="switchDashPage('client','overview',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg> Dashboard
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('client','license',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg> Lisensi Saya
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('client','downloads',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg> Download
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('client','billing',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> Pembayaran
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('client','settings',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg> Pengaturan
      </button>
    </div>
    <div class="dash-sidebar-footer"><button class="dash-logout" onclick="doLogout()">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg> Keluar
    </button></div>
  </aside>
  <div class="dash-main">
    <!-- Overview -->
    <div id="client-overview" class="dash-page active">
      <div class="dash-header"><h1>Selamat Datang, PT Maju Jaya</h1><p>Berikut ringkasan akun Anda</p></div>
      <div class="stat-grid">
        <div class="stat-card"><div class="stat-icon" style="background:rgba(16,185,129,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="1.8" stroke-linecap="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg></div><div><div class="stat-label">Lisensi Aktif</div><div class="stat-value">1</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(56,126,245,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-1)" stroke-width="1.8" stroke-linecap="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zM12 6v6l4 2"/></svg></div><div><div class="stat-label">Hari Tersisa</div><div class="stat-value">365</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(6,191,212,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-2)" stroke-width="1.8" stroke-linecap="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div><div><div class="stat-label">Paket</div><div class="stat-value">Professional</div></div></div>
      </div>
      <div class="detail-grid">
        <div class="detail-item"><div class="detail-label">License Key</div><div class="detail-value" style="font-family:var(--mono);">PRO-2026-X7K9</div></div>
        <div class="detail-item"><div class="detail-label">Status</div><div class="detail-value"><span class="badge badge-active">Active</span></div></div>
        <div class="detail-item"><div class="detail-label">Paket</div><div class="detail-value">Professional</div></div>
        <div class="detail-item"><div class="detail-label">Berlaku Hingga</div><div class="detail-value">20 Jul 2027</div></div>
      </div>
    </div>
    <!-- License -->
    <div id="client-license" class="dash-page">
      <div class="dash-header"><h1>Lisensi Saya</h1><p>Kelola dan lihat detail lisensi software Anda</p></div>
      <div class="dash-table-wrap"><table class="dash-table"><thead><tr><th>License Key</th><th>Paket</th><th>Status</th><th>Berlaku Hingga</th></tr></thead><tbody>
        <tr><td class="td-mono">PRO-2026-X7K9</td><td>Professional</td><td><span class="badge badge-active">Active</span></td><td>20 Jul 2027</td></tr>
      </tbody></table></div>
    </div>
    <!-- Downloads -->
    <div id="client-downloads" class="dash-page">
      <div class="dash-header"><h1>Download Center</h1><p>Unduh installer dan dokumentasi terbaru</p></div>
      <div class="dl-grid">
        <div class="dl-card"><div><div class="dl-name">SoftSale Pro v3.2.1</div><div class="dl-meta">Installer â€¢ 245 MB â€¢ 15 Jul 2026</div></div><button class="btn btn-secondary" style="padding:8px 14px;font-size:12px;">Unduh</button></div>
        <div class="dl-card"><div><div class="dl-name">SoftSale Pro v3.2.0</div><div class="dl-meta">Installer â€¢ 240 MB â€¢ 01 Jul 2026</div></div><button class="btn btn-secondary" style="padding:8px 14px;font-size:12px;">Unduh</button></div>
        <div class="dl-card"><div><div class="dl-name">User Manual v3.x</div><div class="dl-meta">Dokumentasi â€¢ 12 MB â€¢ 10 Jul 2026</div></div><button class="btn btn-secondary" style="padding:8px 14px;font-size:12px;">Unduh</button></div>
        <div class="dl-card"><div><div class="dl-name">API Documentation</div><div class="dl-meta">Dokumentasi â€¢ 5 MB â€¢ 10 Jul 2026</div></div><button class="btn btn-secondary" style="padding:8px 14px;font-size:12px;">Unduh</button></div>
      </div>
    </div>
    <!-- Billing -->
    <div id="client-billing" class="dash-page">
      <div class="dash-header"><h1>Riwayat Pembayaran</h1><p>Detail transaksi dan invoice Anda</p></div>
      <div class="dash-table-wrap"><table class="dash-table"><thead><tr><th>Invoice</th><th>Deskripsi</th><th>Jumlah</th><th>Tanggal</th><th>Status</th></tr></thead><tbody>
        <tr><td class="td-mono">INV-2026-0001</td><td>Professional Plan - Annual</td><td>Rp 4.000.000</td><td>15 Jul 2026</td><td><span class="badge badge-paid">Paid</span></td></tr>
      </tbody></table></div>
    </div>
    <!-- Settings -->
    <div id="client-settings" class="dash-page">
      <div class="dash-header"><h1>Pengaturan Akun</h1><p>Kelola profil dan preferensi akun Anda</p></div>
      <div class="dash-form-card">
        <h3>Profil Akun</h3>
        <div class="form-group"><label class="form-label">Nama Perusahaan</label><div class="form-input-wrap"><input class="form-input" value="PT Maju Jaya"></div></div>
        <div class="form-group"><label class="form-label">Email</label><div class="form-input-wrap"><input class="form-input" value="admin@majujaya.co.id"></div></div>
        <div class="form-group"><label class="form-label">WhatsApp</label><div class="form-input-wrap"><input class="form-input" value="081234567890"></div></div>
        <button class="btn btn-primary" style="margin-top:8px;">Simpan Perubahan</button>
      </div>
    </div>
  </div>
</div>

<!-- â•â•â•â•â•â•â•â•â•â•â• AFFILIATE DASHBOARD â•â•â•â•â•â•â•â•â•â•â• -->
<div id="dashAffiliate" class="app-dashboard">
  <aside class="dash-sidebar">
    <div class="dash-sidebar-brand"><div class="gradient-text" style="font-size:18px;font-weight:800;">SoftSale</div><span>Affiliate Portal</span></div>
    <div class="dash-nav">
      <button class="dash-nav-item active" onclick="switchDashPage('affiliate','overview',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg> Dashboard
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('affiliate','referrals',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg> Referral Saya
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('affiliate','commissions',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> Komisi
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('affiliate','settings',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg> Pengaturan
      </button>
    </div>
    <div class="dash-sidebar-footer"><button class="dash-logout" onclick="doLogout()">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg> Keluar
    </button></div>
  </aside>
  <div class="dash-main">
    <!-- Overview -->
    <div id="affiliate-overview" class="dash-page active">
      <div class="dash-header"><h1>Halo, Rina Susanti!</h1><p>Pantau performa affiliate Anda</p></div>
      <div class="ref-link-card">
        <div style="font-size:13px;color:var(--text-2);">Link Referral Anda</div>
        <div class="ref-link-bar">
          <div class="ref-link-text" id="refLinkText">{{ $landingData['referralUrl'] ?? url('/?ref=AFF001') }}</div>
          <button class="btn btn-primary" style="padding:10px 18px;" onclick="copyRefLink()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 9h-9a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2zM5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            <span id="copyBtnText">Salin</span>
          </button>
        </div>
      </div>
      <div class="stat-grid" style="grid-template-columns:repeat(4,1fr);">
        <div class="stat-card"><div class="stat-icon" style="background:rgba(56,126,245,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-1)" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg></div><div><div class="stat-label">Total Referral</div><div class="stat-value">12</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(16,185,129,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="1.8" stroke-linecap="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div><div class="stat-label">Total Komisi</div><div class="stat-value">Rp 4.800.000</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(245,166,35,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--amber)" stroke-width="1.8" stroke-linecap="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zM12 6v6l4 2"/></svg></div><div><div class="stat-label">Pending</div><div class="stat-value">Rp 1.200.000</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(6,191,212,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-2)" stroke-width="1.8" stroke-linecap="round"><path d="M18 20V10M12 20V4M6 20v-6"/></svg></div><div><div class="stat-label">Konversi</div><div class="stat-value">15%</div></div></div>
      </div>
      <div class="dash-table-wrap"><div class="dash-table-header"><h3>Referral Terbaru</h3></div><table class="dash-table"><thead><tr><th>Client</th><th>Paket</th><th>Komisi</th><th>Tanggal</th><th>Status</th></tr></thead><tbody>
        <tr><td class="td-bold">PT Maju Jaya</td><td>Professional</td><td class="td-green">Rp 400.000</td><td>15 Jul 2026</td><td><span class="badge badge-paid">Paid</span></td></tr>
        <tr><td class="td-bold">PT Karya Mandiri</td><td>Professional</td><td class="td-amber">Rp 400.000</td><td>10 Jul 2026</td><td><span class="badge badge-pending">Pending</span></td></tr>
      </tbody></table></div>
    </div>
    <!-- Referrals -->
    <div id="affiliate-referrals" class="dash-page">
      <div class="dash-header"><h1>Referral Saya</h1><p>Daftar client yang mendaftar melalui link Anda</p></div>
      <div class="dash-table-wrap"><table class="dash-table"><thead><tr><th>Client</th><th>Paket</th><th>Status</th><th>Bergabung</th></tr></thead><tbody>
        <tr><td class="td-bold">PT Maju Jaya</td><td>Professional</td><td><span class="badge badge-active">Active</span></td><td>15 Jul 2026</td></tr>
        <tr><td class="td-bold">PT Karya Mandiri</td><td>Professional</td><td><span class="badge badge-active">Active</span></td><td>10 Jul 2026</td></tr>
      </tbody></table></div>
    </div>
    <!-- Commissions -->
    <div id="affiliate-commissions" class="dash-page">
      <div class="dash-header"><h1>Riwayat Komisi</h1><p>Detail semua komisi dari referral Anda</p></div>
      <div class="dash-table-wrap"><table class="dash-table"><thead><tr><th>Client</th><th>Paket</th><th>Komisi</th><th>Tanggal</th><th>Status</th></tr></thead><tbody>
        <tr><td class="td-bold">PT Maju Jaya</td><td>Professional</td><td class="td-green">Rp 400.000</td><td>15 Jul 2026</td><td><span class="badge badge-paid">Paid</span></td></tr>
        <tr><td class="td-bold">PT Karya Mandiri</td><td>Professional</td><td class="td-amber">Rp 400.000</td><td>10 Jul 2026</td><td><span class="badge badge-pending">Pending</span></td></tr>
      </tbody></table></div>
    </div>
    <!-- Settings -->
    <div id="affiliate-settings" class="dash-page">
      <div class="dash-header"><h1>Pengaturan</h1><p>Kelola profil dan metode pembayaran</p></div>
      <div class="dash-form-card">
        <h3>Profil Affiliate</h3>
        <div class="form-group"><label class="form-label">Nama</label><div class="form-input-wrap"><input class="form-input" value="Rina Susanti"></div></div>
        <div class="form-group"><label class="form-label">Email</label><div class="form-input-wrap"><input class="form-input" value="rina@gmail.com"></div></div>
        <div class="form-group"><label class="form-label">Kode Affiliate</label><div class="form-input-wrap"><input class="form-input" value="AFF001" readonly style="color:var(--accent-1);"></div></div>
        <div style="padding:14px;border-radius:8px;background:rgba(255,255,255,0.025);margin-bottom:18px;">
          <div class="detail-label">Metode Pembayaran Komisi</div>
          <div style="font-size:14px;color:var(--text-1);margin-top:4px;">Bank BCA â€” 1234567890 a.n. Rina Susanti</div>
        </div>
        <button class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </div>
  </div>
</div>

<!-- â•â•â•â•â•â•â•â•â•â•â• ADMIN DASHBOARD â•â•â•â•â•â•â•â•â•â•â• -->
<div id="dashAdmin" class="app-dashboard">
  <aside class="dash-sidebar">
    <div class="dash-sidebar-brand"><div class="gradient-text" style="font-size:18px;font-weight:800;">SoftSale</div><span>Admin Panel</span></div>
    <div class="dash-nav">
      <button class="dash-nav-item active" onclick="switchDashPage('admin','overview',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg> Dashboard
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('admin','clients',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg> Client
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('admin','licenses',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg> Lisensi
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('admin','affiliates',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg> Affiliate
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('admin','commissions',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> Komisi
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('admin','revenue',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M18 20V10M12 20V4M6 20v-6"/></svg> Laporan
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('admin','settings',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg> Pengaturan
      </button>
    </div>
    <div class="dash-sidebar-footer"><button class="dash-logout" onclick="doLogout()">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg> Keluar
    </button></div>
  </aside>
  <div class="dash-main">
    <!-- Overview -->
    <div id="admin-overview" class="dash-page active">
      <div class="dash-header"><h1>Admin Dashboard</h1><p>Ringkasan keseluruhan sistem</p></div>
      <div class="stat-grid" style="grid-template-columns:repeat(4,1fr);">
        <div class="stat-card"><div class="stat-icon" style="background:rgba(56,126,245,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-1)" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg></div><div><div class="stat-label">Total Client</div><div class="stat-value">5</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(16,185,129,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="1.8" stroke-linecap="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div><div class="stat-label">Revenue</div><div class="stat-value">Rp 20.000.000</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(6,191,212,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-2)" stroke-width="1.8" stroke-linecap="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></div><div><div class="stat-label">Affiliate Aktif</div><div class="stat-value">2</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(245,166,35,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--amber)" stroke-width="1.8" stroke-linecap="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg></div><div><div class="stat-label">Lisensi Aktif</div><div class="stat-value">3</div></div></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:20px;">
        <div class="dash-table-wrap"><div class="dash-table-header"><h3>Client Terbaru</h3></div><table class="dash-table"><thead><tr><th>Nama</th><th>Paket</th><th>Status</th></tr></thead><tbody>
          <tr><td class="td-bold">PT Maju Jaya</td><td>Professional</td><td><span class="badge badge-active">Active</span></td></tr>
          <tr><td class="td-bold">CV Tekno Solusi</td><td>Enterprise</td><td><span class="badge badge-active">Active</span></td></tr>
          <tr><td class="td-bold">UD Sinar Abadi</td><td>Starter</td><td><span class="badge badge-trial">Trial</span></td></tr>
        </tbody></table></div>
        <div class="dash-table-wrap"><div class="dash-table-header"><h3>Komisi Pending</h3></div><table class="dash-table"><thead><tr><th>Affiliate</th><th>Jumlah</th><th>Status</th></tr></thead><tbody>
          <tr><td class="td-mono">AFF001</td><td>Rp 400.000</td><td><span class="badge badge-pending">Pending</span></td></tr>
          <tr><td class="td-mono">AFF003</td><td>Rp 200.000</td><td><span class="badge badge-pending">Pending</span></td></tr>
        </tbody></table></div>
      </div>
      <div class="dash-table-wrap"><div class="dash-table-header"><h3>Revenue Bulanan (2026)</h3></div><div style="padding:20px;">
        <div class="revenue-bars" id="revenueBars"></div>
      </div></div>
    </div>
    <!-- Clients -->
    <div id="admin-clients" class="dash-page">
      <div class="dash-header"><h1>Manajemen Client</h1><p>Kelola semua client terdaftar</p></div>
      <div class="dash-table-wrap"><div class="dash-table-header"><h3>5 client terdaftar</h3><button class="btn btn-primary" style="padding:8px 16px;font-size:12px;">+ Tambah Client</button></div><table class="dash-table"><thead><tr><th>Nama</th><th>Email</th><th>Paket</th><th>License Key</th><th>Status</th><th>Referral</th></tr></thead><tbody>
        <tr><td class="td-bold">PT Maju Jaya</td><td>admin@majujaya.co.id</td><td>Professional</td><td class="td-mono">PRO-2026-X7K9</td><td><span class="badge badge-active">Active</span></td><td style="color:var(--accent-2);">AFF001</td></tr>
        <tr><td class="td-bold">CV Tekno Solusi</td><td>info@teknosolusi.id</td><td>Enterprise</td><td class="td-mono">ENT-2026-M3P1</td><td><span class="badge badge-active">Active</span></td><td style="color:var(--text-3);">â€”</td></tr>
        <tr><td class="td-bold">Toko Digital Budi</td><td>budi@digitalstore.id</td><td>Starter</td><td class="td-mono">STR-2026-Q2W8</td><td><span class="badge badge-expired">Expired</span></td><td style="color:var(--accent-2);">AFF002</td></tr>
        <tr><td class="td-bold">PT Karya Mandiri</td><td>hrd@karyamandiri.com</td><td>Professional</td><td class="td-mono">PRO-2026-L5N4</td><td><span class="badge badge-active">Active</span></td><td style="color:var(--accent-2);">AFF001</td></tr>
        <tr><td class="td-bold">UD Sinar Abadi</td><td>sinar@abadi.co.id</td><td>Starter</td><td class="td-mono">STR-2026-R9T6</td><td><span class="badge badge-trial">Trial</span></td><td style="color:var(--accent-2);">AFF003</td></tr>
      </tbody></table></div>
    </div>
    <!-- Licenses -->
    <div id="admin-licenses" class="dash-page">
      <div class="dash-header"><h1>Manajemen Lisensi</h1><p>Generate, renew, atau revoke lisensi</p></div>
      <div class="dash-table-wrap"><div class="dash-table-header"><h3>5 lisensi terdaftar</h3><button class="btn btn-primary" style="padding:8px 16px;font-size:12px;">+ Generate Lisensi</button></div><table class="dash-table"><thead><tr><th>License Key</th><th>Client</th><th>Paket</th><th>Status</th><th>Berlaku Hingga</th><th>Aksi</th></tr></thead><tbody>
        <tr><td class="td-mono">PRO-2026-X7K9</td><td>PT Maju Jaya</td><td>Professional</td><td><span class="badge badge-active">Active</span></td><td>20 Jul 2027</td><td><button class="btn btn-secondary" style="padding:4px 10px;font-size:11px;">Renew</button> <button class="btn" style="padding:4px 10px;font-size:11px;background:rgba(240,104,104,0.12);color:var(--rose);border:1px solid rgba(240,104,104,0.2);">Revoke</button></td></tr>
        <tr><td class="td-mono">ENT-2026-M3P1</td><td>CV Tekno Solusi</td><td>Enterprise</td><td><span class="badge badge-active">Active</span></td><td>15 Mar 2027</td><td><button class="btn btn-secondary" style="padding:4px 10px;font-size:11px;">Renew</button> <button class="btn" style="padding:4px 10px;font-size:11px;background:rgba(240,104,104,0.12);color:var(--rose);border:1px solid rgba(240,104,104,0.2);">Revoke</button></td></tr>
        <tr><td class="td-mono">STR-2026-Q2W8</td><td>Toko Digital Budi</td><td>Starter</td><td><span class="badge badge-expired">Expired</span></td><td>01 Jun 2026</td><td><button class="btn btn-secondary" style="padding:4px 10px;font-size:11px;">Renew</button></td></tr>
        <tr><td class="td-mono">PRO-2026-L5N4</td><td>PT Karya Mandiri</td><td>Professional</td><td><span class="badge badge-active">Active</span></td><td>10 Jan 2027</td><td><button class="btn btn-secondary" style="padding:4px 10px;font-size:11px;">Renew</button> <button class="btn" style="padding:4px 10px;font-size:11px;background:rgba(240,104,104,0.12);color:var(--rose);border:1px solid rgba(240,104,104,0.2);">Revoke</button></td></tr>
        <tr><td class="td-mono">STR-2026-R9T6</td><td>UD Sinar Abadi</td><td>Starter</td><td><span class="badge badge-trial">Trial</span></td><td>20 Aug 2026</td><td><button class="btn btn-secondary" style="padding:4px 10px;font-size:11px;">Renew</button></td></tr>
      </tbody></table></div>
    </div>
    <!-- Affiliates -->
    <div id="admin-affiliates" class="dash-page">
      <div class="dash-header"><h1>Manajemen Affiliate</h1><p>Kelola dan approve pendaftaran affiliate</p></div>
      <div class="dash-table-wrap"><table class="dash-table"><thead><tr><th>Kode</th><th>Nama</th><th>Email</th><th>Sales</th><th>Total Komisi</th><th>Pending</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        <tr><td class="td-mono">AFF001</td><td class="td-bold">Rina Susanti</td><td>rina@gmail.com</td><td>12</td><td class="td-green">Rp 4.800.000</td><td class="td-amber">Rp 1.200.000</td><td><span class="badge badge-active">Active</span></td><td><button class="btn btn-secondary" style="padding:4px 10px;font-size:11px;">Detail</button></td></tr>
        <tr><td class="td-mono">AFF002</td><td class="td-bold">Dedi Kurniawan</td><td>dedi.k@yahoo.com</td><td>5</td><td class="td-green">Rp 1.500.000</td><td class="td-amber">Rp 500.000</td><td><span class="badge badge-active">Active</span></td><td><button class="btn btn-secondary" style="padding:4px 10px;font-size:11px;">Detail</button></td></tr>
        <tr><td class="td-mono">AFF003</td><td class="td-bold">Siti Aminah</td><td>siti.a@outlook.com</td><td>2</td><td class="td-green">Rp 600.000</td><td class="td-amber">Rp 600.000</td><td><span class="badge badge-pending">Pending</span></td><td><button class="btn btn-primary" style="padding:4px 10px;font-size:11px;">Approve</button></td></tr>
      </tbody></table></div>
    </div>
    <!-- Commissions -->
    <div id="admin-commissions" class="dash-page">
      <div class="dash-header"><h1>Komisi Affiliate</h1><p>Kelola pembayaran komisi</p></div>
      <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card"><div class="stat-icon" style="background:rgba(16,185,129,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="1.8" stroke-linecap="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div><div class="stat-label">Total Dibayar</div><div class="stat-value">Rp 600.000</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(245,166,35,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--amber)" stroke-width="1.8" stroke-linecap="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zM12 6v6l4 2"/></svg></div><div><div class="stat-label">Menunggu Bayar</div><div class="stat-value">Rp 1.000.000</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(56,126,245,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-1)" stroke-width="1.8" stroke-linecap="round"><path d="M18 20V10M12 20V4M6 20v-6"/></svg></div><div><div class="stat-label">Total Transaksi</div><div class="stat-value">4</div></div></div>
      </div>
      <div class="dash-table-wrap"><table class="dash-table"><thead><tr><th>Affiliate</th><th>Client</th><th>Paket</th><th>Komisi</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        <tr><td class="td-mono">AFF001</td><td>PT Maju Jaya</td><td>Professional</td><td class="td-green">Rp 400.000</td><td>15 Jul 2026</td><td><span class="badge badge-paid">Paid</span></td><td></td></tr>
        <tr><td class="td-mono">AFF001</td><td>PT Karya Mandiri</td><td>Professional</td><td class="td-amber">Rp 400.000</td><td>10 Jul 2026</td><td><span class="badge badge-pending">Pending</span></td><td><button class="btn btn-primary" style="padding:4px 10px;font-size:11px;">Bayar</button></td></tr>
        <tr><td class="td-mono">AFF002</td><td>Toko Digital Budi</td><td>Starter</td><td class="td-green">Rp 200.000</td><td>28 Jun 2026</td><td><span class="badge badge-paid">Paid</span></td><td></td></tr>
        <tr><td class="td-mono">AFF003</td><td>UD Sinar Abadi</td><td>Starter</td><td class="td-amber">Rp 200.000</td><td>18 Jul 2026</td><td><span class="badge badge-pending">Pending</span></td><td><button class="btn btn-primary" style="padding:4px 10px;font-size:11px;">Bayar</button></td></tr>
      </tbody></table></div>
    </div>
    <!-- Revenue -->
    <div id="admin-revenue" class="dash-page">
      <div class="dash-header"><h1>Laporan & Analitik</h1><p>Insight penjualan dan performa affiliate</p></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
        <div class="dash-table-wrap" style="padding:24px;"><h3 style="font-size:15px;font-weight:700;color:var(--text-1);margin-bottom:18px;">Distribusi Paket</h3>
          <div style="margin-bottom:16px;"><div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;"><span style="color:var(--text-2);">Starter</span><span style="color:var(--text-3);">2 client</span></div><div style="height:8px;border-radius:4px;background:rgba(255,255,255,0.05);"><div style="height:100%;border-radius:4px;background:var(--amber);width:40%;"></div></div></div>
          <div style="margin-bottom:16px;"><div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;"><span style="color:var(--text-2);">Professional</span><span style="color:var(--text-3);">2 client</span></div><div style="height:8px;border-radius:4px;background:rgba(255,255,255,0.05);"><div style="height:100%;border-radius:4px;background:var(--accent-1);width:40%;"></div></div></div>
          <div><div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;"><span style="color:var(--text-2);">Enterprise</span><span style="color:var(--text-3);">1 client</span></div><div style="height:8px;border-radius:4px;background:rgba(255,255,255,0.05);"><div style="height:100%;border-radius:4px;background:var(--accent-2);width:20%;"></div></div></div>
        </div>
        <div class="dash-table-wrap" style="padding:24px;"><h3 style="font-size:15px;font-weight:700;color:var(--text-1);margin-bottom:18px;">Sumber Penjualan</h3>
          <div style="display:flex;align-items:center;gap:12px;padding:14px;border-radius:10px;background:rgba(255,255,255,0.015);margin-bottom:12px;"><div style="width:10px;height:10px;border-radius:50%;background:var(--accent-1);"></div><span style="flex:1;font-size:14px;color:var(--text-2);">Direct (tanpa referral)</span><span style="font-size:20px;font-weight:700;color:var(--text-1);">2</span></div>
          <div style="display:flex;align-items:center;gap:12px;padding:14px;border-radius:10px;background:rgba(255,255,255,0.015);"><div style="width:10px;height:10px;border-radius:50%;background:var(--green);"></div><span style="flex:1;font-size:14px;color:var(--text-2);">Via Affiliate</span><span style="font-size:20px;font-weight:700;color:var(--text-1);">3</span></div>
        </div>
      </div>
      <div class="dash-table-wrap" style="margin-top:18px;"><div class="dash-table-header"><h3>Top Affiliate</h3></div><table class="dash-table"><thead><tr><th>Rank</th><th>Affiliate</th><th>Sales</th><th>Total Komisi</th><th>Status</th></tr></thead><tbody>
        <tr><td style="font-weight:700;color:var(--amber);">#1</td><td class="td-bold">Rina Susanti</td><td>12</td><td class="td-green">Rp 4.800.000</td><td><span class="badge badge-active">Active</span></td></tr>
        <tr><td style="font-weight:700;color:var(--amber);">#2</td><td class="td-bold">Dedi Kurniawan</td><td>5</td><td class="td-green">Rp 1.500.000</td><td><span class="badge badge-active">Active</span></td></tr>
        <tr><td style="font-weight:700;color:var(--amber);">#3</td><td class="td-bold">Siti Aminah</td><td>2</td><td class="td-green">Rp 600.000</td><td><span class="badge badge-pending">Pending</span></td></tr>
      </tbody></table></div>
    </div>
    <!-- Settings -->
    <div id="admin-settings" class="dash-page">
      <div class="dash-header"><h1>Pengaturan Sistem</h1><p>Konfigurasi komisi dan lisensi</p></div>
      <div class="settings-pair">
        <div class="dash-form-card">
          <h3>Pengaturan Komisi</h3>
          <div class="form-group"><label class="form-label">Persentase Komisi (%)</label><div class="form-input-wrap"><input class="form-input" value="20"></div></div>
          <div class="form-group"><label class="form-label">Cookie Tracking (hari)</label><div class="form-input-wrap"><input class="form-input" value="30"></div></div>
          <div class="form-group"><label class="form-label">Minimum Withdrawal (Rp)</label><div class="form-input-wrap"><input class="form-input" value="500000"></div></div>
          <button class="btn btn-primary" style="margin-top:8px;">Simpan</button>
        </div>
        <div class="dash-form-card">
          <h3>Pengaturan Lisensi</h3>
          <div class="form-group"><label class="form-label">Format License Key</label><div class="form-input-wrap"><input class="form-input" value="{PLAN}-{YEAR}-{RANDOM4}" style="font-family:var(--mono);font-size:13px;"></div></div>
          <div class="form-group"><label class="form-label">Durasi Trial (hari)</label><div class="form-input-wrap"><input class="form-input" value="14"></div></div>
          <div class="form-group"><label class="form-label">Max Device per License</label><div class="form-input-wrap"><input class="form-input" value="3"></div></div>
          <button class="btn btn-primary" style="margin-top:8px;">Simpan</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- â•â•â•â•â•â•â•â•â•â•â• AUTH MODAL â•â•â•â•â•â•â•â•â•â•â• -->
<div class="modal-overlay" id="authModal">
  <div class="glass modal-box">
    <button class="modal-close" onclick="closeModal()">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>
    <div style="text-align:center; margin-bottom:24px;">
      <div class="gradient-text" style="font-size:24px; font-weight:800;">SoftSale</div>
    </div>
    <div class="modal-tabs">
      <button class="modal-tab active" id="tabLogin" onclick="switchTab('login')">Masuk</button>
      <button class="modal-tab" id="tabRegister" onclick="switchTab('register')">Daftar</button>
    </div>

    <!-- LOGIN FORM -->
    <div id="loginForm">
      <div class="role-selector">
        <button class="role-btn active" onclick="selectRole(this,'login')">Client</button>
        <button class="role-btn" onclick="selectRole(this,'login')">Affiliate</button>
        <button class="role-btn" onclick="selectRole(this,'login')">Admin</button>
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <div class="form-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.8" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zM22 6l-10 7L2 6"/></svg>
          <input class="form-input" type="email" placeholder="email@example.com">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="form-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.8" stroke-linecap="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
          <input class="form-input" type="password" placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢">
        </div>
      </div>
      <button class="btn btn-primary btn-full" style="margin-top:6px;" onclick="doLogin()">Masuk</button>
    </div>

    <!-- REGISTER FORM -->
    <div id="registerForm" style="display:none;">
      <div class="role-selector">
        <button class="role-btn active" onclick="selectRole(this,'register')">Client</button>
        <button class="role-btn" onclick="selectRole(this,'register')">Affiliate</button>
      </div>
      <div class="form-group">
        <label class="form-label">Nama Lengkap</label>
        <div class="form-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg>
          <input class="form-input" type="text" placeholder="Nama Anda">
        </div>
      </div>
      <div class="form-group" id="companyField">
        <label class="form-label">Nama Perusahaan</label>
        <div class="form-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.8" stroke-linecap="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
          <input class="form-input" type="text" placeholder="PT / CV / UD">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <div class="form-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.8" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zM22 6l-10 7L2 6"/></svg>
          <input class="form-input" type="email" placeholder="email@example.com">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">No. WhatsApp</label>
        <div class="form-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.8" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          <input class="form-input" type="text" placeholder="08xxxxxxxxxx">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="form-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.8" stroke-linecap="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
          <input class="form-input" type="password" placeholder="Min. 8 karakter">
        </div>
      </div>
      <button class="btn btn-primary btn-full" id="registerBtn" style="margin-top:6px;">Daftar Sekarang</button>
    </div>
  </div>
</div>
