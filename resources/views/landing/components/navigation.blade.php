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
