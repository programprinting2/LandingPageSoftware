<div class="nav-mobile-overlay" id="mobileMenu">
  <button class="nav-mobile-close" onclick="toggleMobileMenu()">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
  </button>
  <button class="nav-mobile-link" onclick="scrollToSection('cara-kerja'); toggleMobileMenu()">Cara Kerja</button>
  <button class="nav-mobile-link" onclick="scrollToSection('fitur'); toggleMobileMenu()">Fitur</button>
  <button class="nav-mobile-link" onclick="scrollToSection('harga'); toggleMobileMenu()">Harga</button>
  <button class="nav-mobile-link" onclick="scrollToSection('faq'); toggleMobileMenu()">FAQ</button>
  <div style="margin-top:20px; display:flex; gap:10px;">
    <button class="btn btn-primary" onclick="openModal('register'); toggleMobileMenu()">Coba Gratis</button>
    <button class="btn btn-outline-white" onclick="openModal('login'); toggleMobileMenu()">Masuk</button>
  </div>
</div>
