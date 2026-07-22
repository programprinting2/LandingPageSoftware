const {
  features: FEATURES = [],
  plans: PLANS = [],
  testimonials: TESTIMONIALS = [],
  aboutValues: ABOUT_VALUES = [],
  contacts: CONTACTS = [],
  referralUrl = '',
} = window.SOFTSALE_DATA || {};
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// SVG HELPER
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function svgIcon(paths, size = 20, color = 'currentColor') {
  return `<svg width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="${color}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">${paths}</svg>`;
}
const checkSVG = svgIcon('<path d="M20 6L9 17l-5-5"/>', 15, 'var(--green)');
const starSVG = svgIcon('<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>', 14, 'var(--amber)');

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// RENDER SECTIONS
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function renderFeatures() {
  const grid = document.getElementById('featuresGrid');
  grid.innerHTML = FEATURES.map((f, i) => `
    <div class="glass feature-card reveal reveal-delay-${i % 3 + 1}">
      <div class="feature-icon" style="background:${f.color}12;">
        ${svgIcon(f.icon, 22, f.color)}
      </div>
      <h3>${f.title}</h3>
      <p>${f.desc}</p>
    </div>
  `).join('');
}

function renderPricing() {
  const grid = document.getElementById('pricingGrid');
  grid.innerHTML = PLANS.map((p, i) => `
    <div class="glass price-card ${p.popular ? 'popular' : ''} reveal reveal-delay-${i + 1}">
      <h3>${p.name}</h3>
      <div style="margin:16px 0 0;">
        <span class="price-amount">${p.price}</span>
        <span class="price-period">${p.period}</span>
      </div>
      <div class="price-features">
        ${p.features.map(f => `<div class="price-feat">${checkSVG}<span>${f}</span></div>`).join('')}
      </div>
      <button class="btn ${p.popular ? 'btn-primary' : 'btn-secondary'} btn-full" onclick="openModal('register')">
        Pilih ${p.name}
      </button>
    </div>
  `).join('');
}

function renderTestimonials() {
  const grid = document.getElementById('testiGrid');
  grid.innerHTML = TESTIMONIALS.map((t, i) => `
    <div class="glass testi-card reveal reveal-delay-${i + 1}">
      <div class="testi-stars">${Array(5).fill(starSVG).join('')}</div>
      <p class="testi-text">"${t.text}"</p>
      <div class="testi-author">
        <div class="testi-avatar">${t.avatar}</div>
        <div>
          <div class="testi-name">${t.name}</div>
          <div class="testi-role">${t.role}</div>
        </div>
      </div>
    </div>
  `).join('');
}

function renderAboutValues() {
  const grid = document.getElementById('aboutValues');
  grid.innerHTML = ABOUT_VALUES.map((v, i) => `
    <div class="glass about-val reveal reveal-delay-${i + 1}">
      <div class="about-val-icon" style="background:${v.color}12;">
        ${svgIcon(v.icon, 18, v.color)}
      </div>
      <h4>${v.title}</h4>
      <p>${v.desc}</p>
    </div>
  `).join('');
}

function renderContacts() {
  const grid = document.getElementById('contactGrid');
  grid.innerHTML = CONTACTS.map((c, i) => `
    <div class="glass contact-card reveal reveal-delay-${i + 1}">
      <div class="contact-icon" style="background:${c.color}12;">
        ${svgIcon(c.icon, 22, c.color)}
      </div>
      <h4>${c.title}</h4>
      <div class="contact-detail" style="color:var(--accent-1);">${c.detail}</div>
      <div class="contact-sub">${c.sub}</div>
    </div>
  `).join('');
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// SMOOTH SCROLL
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function scrollToSection(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// NAV SCROLL EFFECT + ACTIVE LINK
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
const sectionIds = ['fitur', 'harga', 'tentang', 'kontak'];

function updateNav() {
  const nav = document.getElementById('navbar');
  const scrolled = window.scrollY > 40;
  nav.classList.toggle('scrolled', scrolled);

  // Active link highlight
  const links = document.querySelectorAll('#navLinks .nav-link');
  let current = '';
  for (const id of sectionIds) {
    const sec = document.getElementById(id);
    if (sec && sec.getBoundingClientRect().top <= 140) current = id;
  }
  links.forEach(link => {
    const target = link.textContent.toLowerCase();
    link.classList.toggle('active', target === current);
  });
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// SCROLL REVEAL
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function revealOnScroll() {
  const els = document.querySelectorAll('.reveal');
  const trigger = window.innerHeight * 0.88;
  els.forEach(el => {
    if (el.getBoundingClientRect().top < trigger) el.classList.add('visible');
  });
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// COUNTER ANIMATION
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
let counterDone = {};
function animateCounters() {
  document.querySelectorAll('[data-count]').forEach(el => {
    if (counterDone[el.dataset.count + el.textContent]) return;
    if (el.getBoundingClientRect().top > window.innerHeight) return;
    counterDone[el.dataset.count + el.textContent] = true;
    const target = parseInt(el.dataset.count);
    let current = 0;
    const step = Math.max(1, Math.floor(target / 60));
    const timer = setInterval(() => {
      current += step;
      if (current >= target) { el.textContent = target + '+'; clearInterval(timer); }
      else el.textContent = current + '+';
    }, 20);
  });
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// HERO PARTICLES
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function createParticles() {
  const container = document.getElementById('particles');
  for (let i = 0; i < 18; i++) {
    const p = document.createElement('div');
    p.className = 'particle';
    const size = Math.random() * 3 + 1.5;
    const colors = ['rgba(56,126,245,0.5)', 'rgba(6,191,212,0.4)', 'rgba(167,139,250,0.35)'];
    Object.assign(p.style, {
      width: size + 'px', height: size + 'px',
      left: Math.random() * 100 + '%',
      top: (60 + Math.random() * 40) + '%',
      background: colors[Math.floor(Math.random() * colors.length)],
      animationDuration: (6 + Math.random() * 10) + 's',
      animationDelay: (Math.random() * 8) + 's',
    });
    container.appendChild(p);
  }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// MODAL
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function openModal(tab = 'login', role = null) {
  const modal = document.getElementById('authModal');
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
  switchTab(tab);
  if (role === 'affiliate' && tab === 'register') {
    setTimeout(() => {
      const btns = document.querySelectorAll('#registerForm .role-btn');
      btns.forEach(b => b.classList.remove('active'));
      if (btns[1]) { btns[1].classList.add('active'); updateRegisterRole('Affiliate'); }
    }, 50);
  }
}
function closeModal() {
  const modal = document.getElementById('authModal');
  modal.classList.remove('open');
  document.body.style.overflow = '';
}
function switchTab(tab) {
  document.getElementById('tabLogin').classList.toggle('active', tab === 'login');
  document.getElementById('tabRegister').classList.toggle('active', tab === 'register');
  document.getElementById('loginForm').style.display = tab === 'login' ? 'block' : 'none';
  document.getElementById('registerForm').style.display = tab === 'register' ? 'block' : 'none';
}
function selectRole(el, form) {
  const parent = el.parentElement;
  parent.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
  el.classList.add('active');
  if (form === 'register') updateRegisterRole(el.textContent.trim());
}
function updateRegisterRole(role) {
  const company = document.getElementById('companyField');
  const btn = document.getElementById('registerBtn');
  company.style.display = role === 'Client' ? 'block' : 'none';
  btn.textContent = role === 'Affiliate' ? 'Daftar Sebagai Affiliate' : 'Daftar Sekarang';
}

// Close modal on overlay click
document.getElementById('authModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
// Close modal on Escape
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// MOBILE MENU
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function toggleMobileMenu() {
  document.getElementById('mobileMenu').classList.toggle('open');
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// LOGIN / LOGOUT / DASHBOARD NAV
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function getSelectedLoginRole() {
  const active = document.querySelector('#loginForm .role-selector .role-btn.active');
  return active ? active.textContent.trim().toLowerCase() : 'client';
}

function doLogin() {
  const role = getSelectedLoginRole();
  closeModal();

  // Hide landing + navbar
  document.getElementById('appLanding').classList.add('hidden');
  document.getElementById('navbar').style.display = 'none';

  // Hide all dashboards
  document.querySelectorAll('.app-dashboard').forEach(d => d.classList.remove('active'));

  // Show the right dashboard
  const dashMap = { client: 'dashClient', affiliate: 'dashAffiliate', admin: 'dashAdmin' };
  const dash = document.getElementById(dashMap[role]);
  if (dash) {
    dash.classList.add('active');
    window.scrollTo(0, 0);
    // render revenue bars if admin
    if (role === 'admin') renderRevenueBars();
  }
}

function doLogout() {
  // Hide all dashboards
  document.querySelectorAll('.app-dashboard').forEach(d => d.classList.remove('active'));

  // Show landing + navbar
  document.getElementById('appLanding').classList.remove('hidden');
  document.getElementById('navbar').style.display = '';
  window.scrollTo(0, 0);

  // Reset sidebar nav active states
  document.querySelectorAll('.dash-nav-item').forEach((btn, i) => {
    // reset: only first item in each sidebar is active
    const isFirst = btn === btn.parentElement.firstElementChild;
    btn.classList.toggle('active', isFirst);
  });

  // Reset dashboard pages
  document.querySelectorAll('.dash-page').forEach(p => p.classList.remove('active'));
  ['client-overview','affiliate-overview','admin-overview'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.classList.add('active');
  });
}

function switchDashPage(portal, page, btn) {
  // Update sidebar
  const sidebar = btn.closest('.dash-nav');
  sidebar.querySelectorAll('.dash-nav-item').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');

  // Update pages
  const dashMain = btn.closest('.app-dashboard').querySelector('.dash-main');
  dashMain.querySelectorAll('.dash-page').forEach(p => p.classList.remove('active'));
  const target = document.getElementById(portal + '-' + page);
  if (target) target.classList.add('active');
}

function renderRevenueBars() {
  const container = document.getElementById('revenueBars');
  if (!container || container.children.length > 0) return;
  const data = [
    { label: 'Jan', value: 45 }, { label: 'Feb', value: 62 }, { label: 'Mar', value: 58 },
    { label: 'Apr', value: 74 }, { label: 'Mei', value: 85 }, { label: 'Jun', value: 92 }, { label: 'Jul', value: 78 },
  ];
  data.forEach((d, i) => {
    const col = document.createElement('div');
    col.className = 'revenue-col';
    const bar = document.createElement('div');
    bar.className = 'revenue-bar';
    bar.style.height = '0px';
    bar.style.background = `linear-gradient(180deg, rgba(56,126,245,${0.4 + d.value * 0.005}), rgba(6,191,212,${0.2 + d.value * 0.003}))`;
    const label = document.createElement('span');
    label.className = 'revenue-label';
    label.textContent = d.label;
    col.appendChild(bar);
    col.appendChild(label);
    container.appendChild(col);
    // animate
    setTimeout(() => { bar.style.height = (d.value * 1.5) + 'px'; }, 100 + i * 80);
  });
}

// Copy ref link
function copyRefLink() {
  const text = document.getElementById('refLinkText').textContent;
  navigator.clipboard.writeText(text).then(() => {
    const btn = document.getElementById('copyBtnText');
    btn.textContent = 'Tersalin!';
    setTimeout(() => { btn.textContent = 'Salin'; }, 2000);
  }).catch(() => {});
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// INIT
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
document.addEventListener('DOMContentLoaded', () => {
  const refEl = document.getElementById('refLinkText');
  if (refEl && referralUrl) refEl.textContent = referralUrl;

  renderFeatures();
  renderPricing();
  renderTestimonials();
  renderAboutValues();
  renderContacts();
  createParticles();

  // Initial reveal for above-fold
  setTimeout(revealOnScroll, 100);
  setTimeout(animateCounters, 600);

  window.addEventListener('scroll', () => {
    updateNav();
    revealOnScroll();
    animateCounters();
  }, { passive: true });
});
