<!-- FOOTER -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-brand gradient-text">{{ $cms['site']['name'] ?? 'SoftSale' }}</div>
        <p class="footer-desc">{{ $cms['footer']['description'] ?? '' }}</p>
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
    <div class="footer-bottom">{{ $cms['footer']['copyright'] ?? '' }}</div>
  </div>
</footer>
