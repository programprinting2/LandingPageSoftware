<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-brand">{{ $cms['site']['name'] ?? 'Quick Spot Color' }}</div>
        <p class="footer-desc">{{ $cms['footer']['description'] ?? '' }}</p>
      </div>
      <div>
        <div class="footer-col-title">Produk</div>
        <span class="footer-link" onclick="scrollToSection('fitur')">Fitur</span>
        <span class="footer-link" onclick="scrollToSection('harga')">Harga</span>
        <span class="footer-link" onclick="scrollToSection('cara-kerja')">Cara Kerja</span>
        <span class="footer-link" onclick="scrollToSection('faq')">FAQ</span>
      </div>
      <div>
        <div class="footer-col-title">Akun</div>
        <span class="footer-link" onclick="openModal('register')">Daftar</span>
        <span class="footer-link" onclick="openModal('login')">Masuk</span>
      </div>
      <div>
        <div class="footer-col-title">Legal</div>
        <span class="footer-link">Kebijakan Privasi</span>
        <span class="footer-link">Syarat & Ketentuan</span>
      </div>
    </div>
    <div class="footer-bottom">{{ $cms['footer']['copyright'] ?? '' }}</div>
  </div>
</footer>
