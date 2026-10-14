<nav class="nav" id="navbar">
  <div class="container nav-inner">
    <div class="nav-logo" onclick="scrollToSection('hero')">
      <img src="{{ asset('images/Logo Quick Spot Color.svg') }}" alt="{{ $cms['site']['name'] ?? 'Quick Spot Color' }}">
    </div>
    <div class="nav-links" id="navLinks">
      <button class="nav-link" onclick="scrollToSection('cara-kerja')">Cara Kerja</button>
      <button class="nav-link" onclick="scrollToSection('harga')">Harga</button>
      <button class="nav-link" onclick="scrollToSection('faq')">FAQ</button>
    </div>
    <div class="nav-cta">
      <button class="btn btn-primary" onclick="openModal('login')">Masuk</button>
    </div>
    <button class="nav-hamburger" onclick="toggleMobileMenu()" aria-label="Menu">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
    </button>
  </div>
</nav>
