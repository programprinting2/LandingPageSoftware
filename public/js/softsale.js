const {
  features: FEATURES = [],
  plans: PLANS = [],
  testimonials: TESTIMONIALS = [],
  aboutValues: ABOUT_VALUES = [],
  contacts: CONTACTS = [],
  referralUrl = '',
  registerClientUrl = '/register/client',
  loginClientUrl = '/login/client',
  logoutUrl = '/logout',
  clientMeUrl = '/api/client/me',
  clientLicenseUrl = '/api/client/license',
  clientLicenseDeactivateUrl = '/api/client/license/deactivate',
  clientTransactionsUrl = '/api/client/transactions',
  adminClientsUrl = '/api/admin/clients',
  adminApplicationsUrl = '/api/admin/applications',
  adminLicensesUrl = '/api/admin/licenses',
  adminTransactionsUrl = '/api/admin/transactions',
  adminPaymentOrdersUrl = '/api/admin/payment-orders',
} = window.SOFTSALE_DATA || {};

let appDialogResolver = null;

const APP_ROUTE_KEY = 'softsale_route';
const DEMO_PORTAL_KEY = 'softsale_demo_portal';
let adminClientsCache = [];
let adminApplicationsCache = [];
let adminLicensesCache = [];
let adminTransactionsCache = [];
let adminPaymentOrdersCache = [];
let adminPaymentOrdersSearchTimer = null;
let adminTransactionsActiveTab = 'ledger';
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
  clearAuthAlert();
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
  const companyInput = document.getElementById('registerCompany');
  const btn = document.getElementById('registerBtn');
  const isClient = role === 'Client';
  company.style.display = isClient ? 'block' : 'none';
  if (companyInput) companyInput.required = isClient;
  btn.textContent = role === 'Affiliate' ? 'Daftar Sebagai Affiliate' : 'Daftar Sekarang';
}

function getSelectedRegisterRole() {
  const active = document.querySelector('#registerForm .role-selector .role-btn.active');
  return active ? active.textContent.trim() : 'Client';
}

function showAuthAlert(message, type = 'error') {
  const alert = document.getElementById('authAlert');
  if (!alert) return;
  alert.style.display = 'block';
  alert.textContent = message;
  alert.style.background = type === 'success' ? 'rgba(16,185,129,0.12)' : 'rgba(240,104,104,0.12)';
  alert.style.border = type === 'success' ? '1px solid rgba(16,185,129,0.25)' : '1px solid rgba(240,104,104,0.25)';
  alert.style.color = type === 'success' ? 'var(--green)' : 'var(--rose)';
}

function clearAuthAlert() {
  const alert = document.getElementById('authAlert');
  if (alert) {
    alert.style.display = 'none';
    alert.textContent = '';
  }
}

function updateClientDashboard(client) {
  const overview = document.getElementById('client-overview');
  if (!overview || !client) return;

  const headerTitle = overview.querySelector('.dash-header h1');
  if (headerTitle) headerTitle.textContent = `Selamat Datang, ${client.company_name}`;

  const companyInput = document.getElementById('clientSettingsCompany');
  const emailInput = document.getElementById('clientSettingsEmail');
  const waInput = document.getElementById('clientSettingsWhatsapp');
  if (companyInput) companyInput.value = client.company_name;
  if (emailInput) emailInput.value = client.email;
  if (waInput) waInput.value = client.whatsapp;
}

function formatLicenseDate(iso) {
  if (!iso) return '—';
  try {
    return new Date(iso).toLocaleString('id-ID', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  } catch {
    return iso;
  }
}

async function loadClientLicense() {
  const quotaEl = document.getElementById('clientLicenseQuota');
  const hintEl = document.getElementById('clientLicenseHint');
  const tbody = document.getElementById('clientLicenseDevices');
  if (!tbody) return;

  if (quotaEl) quotaEl.textContent = '…';
  if (hintEl) hintEl.textContent = 'Memuat data lisensi…';
  tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);">Memuat…</td></tr>';

  try {
    const response = await fetch(clientLicenseUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (!response.ok) {
      if (hintEl) hintEl.textContent = data.message || 'Gagal memuat lisensi.';
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);">Tidak ada data.</td></tr>';
      return;
    }

    const license = data.license || {};
    const applications = license.applications || [];
    const totalApps = applications.length;
    const totalDevices = applications.reduce((sum, app) => sum + Number(app.devices_active ?? 0), 0);

    if (quotaEl) quotaEl.textContent = `${totalApps} aplikasi · ${totalDevices} perangkat aktif`;
    if (hintEl) {
      hintEl.textContent = !totalApps
        ? 'Belum ada aplikasi yang ditetapkan ke akun Anda. Hubungi admin untuk menambahkan aplikasi yang dibeli.'
        : 'Aktivasi perangkat dilakukan dari aplikasi desktop masing-masing (Settings → Lisensi → Activate).';
    }

    const rows = [];
    applications.forEach((app) => {
      (app.devices || []).forEach((device) => {
        const ip = escapeHtml(device.ip || '—');
        const host = escapeHtml(device.hostname || device.computer || '—');
        const platform = escapeHtml(device.platform || '—');
        const appName = escapeHtml(app.name || '—');
        rows.push(`<tr>
          <td class="td-bold">${appName}</td>
          <td class="td-mono">${ip}</td>
          <td>${host}</td>
          <td>${platform}</td>
          <td>${formatLicenseDate(device.activated_at)}</td>
          <td><span class="badge badge-active">Active</span></td>
          <td><button type="button" class="btn btn-secondary" style="padding:6px 12px;font-size:12px;color:var(--rose);" data-ip="${ip}" data-host="${host}" onclick="deactivateClientDevice(${Number(device.id)}, this)">Deactivate</button></td>
        </tr>`);
      });
    });

    if (!rows.length) {
      tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--text-muted);">${totalApps ? 'Belum ada perangkat aktif untuk aplikasi yang dibeli.' : 'Belum ada aplikasi yang dibeli.'}</td></tr>`;
      return;
    }

    tbody.innerHTML = rows.join('');
  } catch {
    if (hintEl) hintEl.textContent = 'Tidak dapat menghubungi server.';
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-muted);">Gagal memuat.</td></tr>';
  }
}

async function loadClientBilling() {
  const tbody = document.getElementById('clientBillingTableBody');
  const countEl = document.getElementById('clientBillingCount');
  if (!tbody) return;

  if (countEl) countEl.textContent = 'Memuat…';
  tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-3);padding:28px;">Memuat…</td></tr>';

  try {
    const response = await fetch(clientTransactionsUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (!response.ok) {
      if (countEl) countEl.textContent = 'Riwayat pembayaran';
      tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:var(--text-3);padding:28px;">${escapeHtml(data.message || 'Gagal memuat riwayat pembayaran.')}</td></tr>`;
      return;
    }

    const transactions = data.transactions || [];

    if (countEl) {
      countEl.textContent = transactions.length
        ? `${transactions.length} transaksi tercatat`
        : 'Belum ada transaksi';
    }

    if (!transactions.length) {
      tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-3);padding:28px;">Belum ada riwayat pembayaran.</td></tr>';
      return;
    }

    tbody.innerHTML = transactions.map((trx) => {
      const badge = adminTransactionStatusBadge(trx.status);
      const statusLabel = trx.status_label || trx.status || '—';

      return `<tr>
        <td class="td-mono">${escapeHtml(trx.invoice || '—')}</td>
        <td>${escapeHtml(trx.description || '—')}</td>
        <td class="td-green">${escapeHtml(trx.amount_label || formatAdminCurrency(trx.amount))}</td>
        <td>${escapeHtml(trx.paid_at || '—')}</td>
        <td><span class="badge ${badge}">${escapeHtml(statusLabel)}</span></td>
      </tr>`;
    }).join('');
  } catch {
    if (countEl) countEl.textContent = 'Riwayat pembayaran';
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-3);padding:28px;">Gagal memuat riwayat pembayaran.</td></tr>';
  }
}

function openAppDialog(options = {}) {
  const {
    type = 'confirm',
    title = 'Konfirmasi',
    message = '',
    okText = 'OK',
    cancelText = 'Batal',
    danger = false,
  } = options;

  return new Promise((resolve) => {
    appDialogResolver = resolve;
    const modal = document.getElementById('appDialogModal');
    const titleEl = document.getElementById('appDialogTitle');
    const msgEl = document.getElementById('appDialogMessage');
    const okBtn = document.getElementById('appDialogOkBtn');
    const cancelBtn = document.getElementById('appDialogCancelBtn');

    if (!modal) {
      resolve(type === 'alert');
      return;
    }

    if (titleEl) titleEl.textContent = title;
    if (msgEl) msgEl.textContent = message;
    if (okBtn) {
      okBtn.textContent = okText;
      okBtn.style.background = danger ? 'var(--rose)' : '';
      okBtn.style.borderColor = danger ? 'var(--rose)' : '';
    }
    if (cancelBtn) {
      cancelBtn.textContent = cancelText;
      cancelBtn.style.display = type === 'alert' ? 'none' : '';
    }

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
  });
}

function closeAppDialog(result) {
  const modal = document.getElementById('appDialogModal');
  if (modal) modal.classList.remove('open');
  document.body.style.overflow = '';
  if (appDialogResolver) {
    const resolve = appDialogResolver;
    appDialogResolver = null;
    resolve(Boolean(result));
  }
}

async function showAppAlert(message, title = 'Informasi') {
  await openAppDialog({ type: 'alert', title, message, okText: 'OK' });
}

async function deactivateClientDevice(deviceId, btn) {
  if (!deviceId) return;
  const ipLabel = btn?.dataset?.ip || '—';
  const hostLabel = btn?.dataset?.host || '';
  const target = hostLabel && hostLabel !== '—' ? `${hostLabel} (${ipLabel})` : ipLabel;
  const confirmed = await openAppDialog({
    type: 'confirm',
    title: 'Konfirmasi',
    message: `Lepas lisensi dari ${target}? Perangkat ini tidak lagi terdaftar di akun Anda.`,
    okText: 'Deactivate',
    danger: true,
  });
  if (!confirmed) return;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const prevText = btn?.textContent;
  if (btn) {
    btn.disabled = true;
    btn.textContent = '…';
  }

  try {
    const response = await fetch(clientLicenseDeactivateUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
      body: JSON.stringify({ device_id: deviceId }),
    });
    const data = await response.json();

    if (!response.ok) {
      await showAppAlert(data.message || 'Deaktivasi gagal.');
      if (btn) {
        btn.disabled = false;
        btn.textContent = prevText;
      }
      return;
    }

    await loadClientLicense();
  } catch {
    await showAppAlert('Tidak dapat menghubungi server.');
    if (btn) {
      btn.disabled = false;
      btn.textContent = prevText;
    }
  }
}

async function restoreClientSession() {
  try {
    const response = await fetch(clientMeUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (data.logged_in && data.client) {
      updateClientDashboard(data.client);
      showClientDashboard(false);
      return true;
    }
  } catch {
    // Tetap tampilkan landing jika session tidak ada.
  }

  return false;
}

function parseRouteHash() {
  const match = window.location.hash.match(/^#\/(client|admin|affiliate)\/([a-z]+)$/);
  if (!match) return null;
  return { portal: match[1], page: match[2] };
}

function getSavedRoute() {
  try {
    const raw = localStorage.getItem(APP_ROUTE_KEY);
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
}

function saveAppRoute(portal, page) {
  localStorage.setItem(APP_ROUTE_KEY, JSON.stringify({ portal, page }));
  const hash = `#/${portal}/${page}`;
  if (window.location.hash !== hash) {
    history.replaceState(null, '', `${window.location.pathname}${window.location.search}${hash}`);
  }
}

function clearAppRoute() {
  localStorage.removeItem(APP_ROUTE_KEY);
  localStorage.removeItem(DEMO_PORTAL_KEY);
  if (window.location.hash) {
    history.replaceState(null, '', `${window.location.pathname}${window.location.search}`);
  }
}

function activateSidebarNav(portal, page) {
  const dashMap = { client: 'dashClient', admin: 'dashAdmin', affiliate: 'dashAffiliate' };
  const dash = document.getElementById(dashMap[portal]);
  if (!dash) return;

  dash.querySelectorAll('.dash-nav-item').forEach((btn) => {
    const onclick = btn.getAttribute('onclick') || '';
    btn.classList.toggle('active', onclick.includes(`'${page}'`));
  });
}

function applyDashboardRoute(portal, page) {
  const dashMap = { client: 'dashClient', admin: 'dashAdmin', affiliate: 'dashAffiliate' };
  const dash = document.getElementById(dashMap[portal]);
  if (!dash) return;

  document.getElementById('appLanding')?.classList.add('hidden');
  document.getElementById('navbar').style.display = 'none';
  document.querySelectorAll('.app-dashboard').forEach((d) => d.classList.remove('active'));
  dash.classList.add('active');

  dash.querySelectorAll('.dash-page').forEach((p) => p.classList.remove('active'));
  const target = document.getElementById(`${portal}-${page}`);
  if (target) target.classList.add('active');

  activateSidebarNav(portal, page);
  saveAppRoute(portal, page);
  window.scrollTo(0, 0);

  if (portal === 'client' && page === 'license') loadClientLicense();
  if (portal === 'client' && page === 'billing') loadClientBilling();
  if (portal === 'admin' && page === 'clients') refreshAdminClientsTable();
  if (portal === 'admin' && page === 'applications') refreshAdminApplicationsTable();
  if (portal === 'admin' && page === 'licenses') refreshAdminLicensesTable();
  if (portal === 'admin' && page === 'transactions') {
    refreshAdminTransactionsTable();
    if (adminTransactionsActiveTab === 'invoices') refreshAdminPaymentOrdersTable();
  }
  if (portal === 'admin' && page === 'overview') renderRevenueBars();
}

async function initAppState() {
  const savedRoute = parseRouteHash() || getSavedRoute();
  const clientLoggedIn = await restoreClientSession();

  if (clientLoggedIn) {
    const page = savedRoute?.portal === 'client' ? savedRoute.page : 'overview';
    applyDashboardRoute('client', page);
    return;
  }

  const demoPortal = localStorage.getItem(DEMO_PORTAL_KEY);
  const portal = savedRoute?.portal === 'admin' || savedRoute?.portal === 'affiliate'
    ? savedRoute.portal
    : demoPortal;

  if (portal === 'admin' || portal === 'affiliate') {
    showDemoDashboard(portal, false);
    const page = savedRoute?.page || 'overview';
    applyDashboardRoute(portal, page);
  }
}

function showClientDashboard(closeModalOnShow = true) {
  if (closeModalOnShow) closeModal();
  document.getElementById('appLanding').classList.add('hidden');
  document.getElementById('navbar').style.display = 'none';
  document.querySelectorAll('.app-dashboard').forEach((d) => d.classList.remove('active'));
  const dash = document.getElementById('dashClient');
  if (dash) {
    dash.classList.add('active');
    window.scrollTo(0, 0);
  }
  localStorage.removeItem(DEMO_PORTAL_KEY);
}

function showDemoDashboard(role, closeModalOnShow = true) {
  if (closeModalOnShow) closeModal();
  document.getElementById('appLanding').classList.add('hidden');
  document.getElementById('navbar').style.display = 'none';
  document.querySelectorAll('.app-dashboard').forEach((d) => d.classList.remove('active'));

  const dashMap = { affiliate: 'dashAffiliate', admin: 'dashAdmin' };
  const dash = document.getElementById(dashMap[role]);
  if (dash) {
    dash.classList.add('active');
    window.scrollTo(0, 0);
  }

  localStorage.setItem(DEMO_PORTAL_KEY, role);
}

async function submitClientLogin(event) {
  event.preventDefault();
  clearAuthAlert();

  const role = getSelectedLoginRole();
  if (role !== 'client') {
    showDemoDashboard(role);
    applyDashboardRoute(role, 'overview');
    return;
  }

  const btn = document.getElementById('loginBtn');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const payload = {
    email: document.getElementById('loginEmail')?.value.trim(),
    password: document.getElementById('loginPassword')?.value,
  };

  btn.disabled = true;
  btn.textContent = 'Memproses...';

  try {
    const response = await fetch(loginClientUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      body: JSON.stringify(payload),
    });

    const data = await response.json();

    if (!response.ok) {
      const firstError = data.errors
        ? Object.values(data.errors).flat()[0]
        : (data.message || 'Login gagal.');
      showAuthAlert(firstError);
      return;
    }

    updateClientDashboard(data.client);
    showClientDashboard();
    applyDashboardRoute('client', 'overview');
  } catch (error) {
    showAuthAlert('Terjadi kesalahan jaringan. Silakan coba lagi.');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Masuk';
  }
}

async function submitClientRegistration(event) {
  event.preventDefault();
  clearAuthAlert();

  const role = getSelectedRegisterRole();
  if (role !== 'Client') {
    showAuthAlert('Pendaftaran affiliate belum tersedia. Silakan pilih Client.');
    return;
  }

  const form = document.getElementById('registerForm');
  const btn = document.getElementById('registerBtn');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const payload = {
    name: document.getElementById('registerName')?.value.trim(),
    company_name: document.getElementById('registerCompany')?.value.trim(),
    email: document.getElementById('registerEmail')?.value.trim(),
    whatsapp: document.getElementById('registerWhatsapp')?.value.trim(),
    password: document.getElementById('registerPassword')?.value,
  };

  btn.disabled = true;
  btn.textContent = 'Memproses...';

  try {
    const response = await fetch(registerClientUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      body: JSON.stringify(payload),
    });

    const data = await response.json();

    if (!response.ok) {
      const firstError = data.errors
        ? Object.values(data.errors).flat()[0]
        : (data.message || 'Pendaftaran gagal.');
      showAuthAlert(firstError);
      return;
    }

    showAuthAlert(data.message, 'success');
    updateClientDashboard(data.client);
    form.reset();
    updateRegisterRole('Client');

    setTimeout(() => {
      showClientDashboard();
      applyDashboardRoute('client', 'overview');
    }, 700);
  } catch (error) {
    showAuthAlert('Terjadi kesalahan jaringan. Silakan coba lagi.');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Daftar Sekarang';
  }
}

// Modal hanya ditutup lewat tombol silang (onclick="closeModal()").

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
  document.getElementById('loginForm')?.requestSubmit();
}

async function doLogout() {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  try {
    await fetch(logoutUrl, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
    });
  } catch (error) {
    // Demo logout tetap jalan meski request gagal.
  }

  document.querySelectorAll('.app-dashboard').forEach((d) => d.classList.remove('active'));
  document.getElementById('appLanding').classList.remove('hidden');
  document.getElementById('navbar').style.display = '';
  window.scrollTo(0, 0);
  clearAppRoute();

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
  if (btn) {
    const sidebar = btn.closest('.dash-nav');
    sidebar.querySelectorAll('.dash-nav-item').forEach((b) => b.classList.remove('active'));
    btn.classList.add('active');
  }

  applyDashboardRoute(portal, page);
}

function adminClientStatusBadge(status) {
  const map = {
    active: 'badge-active',
    trial: 'badge-trial',
    expired: 'badge-expired',
    pending: 'badge-pending',
  };
  return map[status] || 'badge-pending';
}

function renderAdminClientsRows(clients) {
  const tbody = document.getElementById('adminClientsTableBody');
  const countEl = document.getElementById('adminClientsCount');
  if (!tbody) return;

  adminClientsCache = clients;

  if (countEl) {
    countEl.textContent = `${clients.length} client terdaftar`;
  }

  if (!clients.length) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-3);padding:28px;">Belum ada client terdaftar.</td></tr>';
    return;
  }

  tbody.innerHTML = clients.map((client) => {
    const badge = adminClientStatusBadge(client.status);
    const label = client.status ? client.status.charAt(0).toUpperCase() + client.status.slice(1) : 'Pending';
    const apps = (client.applications || []).map((app) => formatAdminClientAppLabel(app)).join(', ') || '—';
    return `<tr data-client-id="${client.id}">
      <td class="td-bold">${escapeHtml(client.company_name || '—')}</td>
      <td>${escapeHtml(client.name || '—')}</td>
      <td>${escapeHtml(client.email || '—')}</td>
      <td>${escapeHtml(apps)}</td>
      <td>${escapeHtml(client.whatsapp || '—')}</td>
      <td><span class="badge ${badge}">${escapeHtml(label)}</span></td>
      <td>${escapeHtml(client.created_at || '—')}</td>
      <td>
        <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;margin-right:4px;" onclick="openAdminClientModal(${client.id})">Edit</button>
        <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;color:var(--rose);" onclick="deleteAdminClient(${client.id})">Hapus</button>
      </td>
    </tr>`;
  }).join('');
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

async function refreshAdminClientsTable() {
  const tbody = document.getElementById('adminClientsTableBody');
  if (!tbody) return;

  try {
    const response = await fetch(adminClientsUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (!response.ok) {
      return;
    }

    renderAdminClientsRows(data.clients || []);
  } catch {
    // Biarkan data server-side jika request gagal.
  }
}

function adminApplicationStatusBadge(isActive) {
  return isActive ? 'badge-active' : 'badge-expired';
}

function formatAdminPackageDaysLabel(days) {
  if (Number(days) === 0) {
    return '<span class="acc-days-unlimited" title="Unlimited">∞</span>';
  }
  return `${Number(days)} hari`;
}

function formatAdminPackagePriceDisplay(value) {
  const num = Number(value ?? 0);
  if (Number.isNaN(num)) return 'Rp 0';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(num);
}

function renderAdminApplicationPackageAccordionBody(packages) {
  if (!packages?.length) {
    return '<div class="admin-app-acc-empty">Belum ada paket. Klik Edit untuk menambahkan.</div>';
  }

  return `<table class="admin-app-acc-table">
    <thead>
      <tr>
        <th>Nama Paket</th>
        <th>Limit</th>
        <th>Hari</th>
        <th>Harga</th>
      </tr>
    </thead>
    <tbody>
      ${packages.map((pkg) => `<tr>
        <td class="td-bold">${escapeHtml(pkg.name || '—')}</td>
        <td>${Number(pkg.devices_quota ?? 0)}</td>
        <td>${formatAdminPackageDaysLabel(pkg.duration_days)}</td>
        <td class="acc-price">${escapeHtml(formatAdminPackagePriceDisplay(pkg.price))}</td>
      </tr>`).join('')}
    </tbody>
  </table>`;
}

function toggleAdminApplicationAccordion(appId, btn) {
  const row = document.getElementById(`adminAppAcc-${appId}`);
  const mainRow = document.querySelector(`tr.admin-app-main-row[data-application-id="${appId}"]`);
  if (!row || !btn) return;

  const willOpen = row.hidden;
  row.hidden = !willOpen;
  btn.classList.toggle('is-open', willOpen);
  btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
  if (mainRow) mainRow.classList.toggle('is-open', willOpen);
}

function renderAdminApplicationsRows(applications) {
  const tbody = document.getElementById('adminApplicationsTableBody');
  const countEl = document.getElementById('adminApplicationsCount');
  if (!tbody) return;

  adminApplicationsCache = applications;

  if (countEl) {
    countEl.textContent = `${applications.length} aplikasi terdaftar`;
  }

  if (!applications.length) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-3);padding:28px;">Belum ada aplikasi.</td></tr>';
    return;
  }

  tbody.innerHTML = applications.map((app) => {
    const badge = adminApplicationStatusBadge(app.is_active);
    const label = app.is_active ? 'Active' : 'Nonaktif';
    const packages = app.packages || [];
    const pkgCount = Number(app.packages_count ?? packages.length ?? 0);
    const pkgCell = pkgCount
      ? `<button type="button" class="admin-app-acc-toggle" onclick="toggleAdminApplicationAccordion(${app.id}, this)" aria-expanded="false">
          <span>${pkgCount} paket</span>
          <svg class="admin-app-acc-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>`
      : '<span style="color:var(--text-3);">—</span>';

    return `<tr class="admin-app-main-row" data-application-id="${app.id}">
      <td class="td-bold">${escapeHtml(app.name || '—')}</td>
      <td class="td-mono">${escapeHtml(app.slug || '—')}</td>
      <td>${pkgCell}</td>
      <td>${Number(app.clients_count ?? 0)}</td>
      <td>${Number(app.devices_count ?? 0)}</td>
      <td><span class="badge ${badge}">${label}</span></td>
      <td>
        <div class="admin-apps-actions">
          <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;" onclick="openAdminApplicationModal(${app.id})">Edit</button>
          <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;color:var(--rose);" onclick="deleteAdminApplication(${app.id})">Hapus</button>
        </div>
      </td>
    </tr>
    <tr class="admin-app-acc-row" id="adminAppAcc-${app.id}" hidden>
      <td colspan="7">
        <div class="admin-app-acc-panel">
          ${renderAdminApplicationPackageAccordionBody(packages)}
        </div>
      </td>
    </tr>`;
  }).join('');
}

async function refreshAdminApplicationsTable(silent = false) {
  const tbody = document.getElementById('adminApplicationsTableBody');
  if (!tbody && !silent) return;

  try {
    const response = await fetch(adminApplicationsUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (!response.ok) {
      return;
    }

    renderAdminApplicationsRows(data.applications || []);
  } catch {
    if (!silent && tbody) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-3);padding:28px;">Gagal memuat aplikasi.</td></tr>';
    }
  }
}

function adminLicenseStatusBadge(status) {
  return adminClientStatusBadge(status);
}

function renderAdminLicensesRows(licenses) {
  const tbody = document.getElementById('adminLicensesTableBody');
  const countEl = document.getElementById('adminLicensesCount');
  if (!tbody) return;

  adminLicensesCache = licenses;

  if (countEl) {
    countEl.textContent = `${licenses.length} lisensi terdaftar`;
  }

  if (!licenses.length) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-3);padding:28px;">Belum ada lisensi. Assign aplikasi ke client terlebih dahulu.</td></tr>';
    return;
  }

  tbody.innerHTML = licenses.map((license) => {
    const badge = adminLicenseStatusBadge(license.status);
    const label = license.status ? license.status.charAt(0).toUpperCase() + license.status.slice(1) : '—';
    const canRevoke = license.status === 'active' && !license.is_expired;
    const revokeBtn = canRevoke
      ? `<button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;color:var(--rose);margin-left:4px;" onclick="revokeAdminLicense(${license.id})">Revoke</button>`
      : '';

    return `<tr data-license-id="${license.id}">
      <td class="td-mono">${escapeHtml(license.license_key || '—')}</td>
      <td class="td-bold">${escapeHtml(license.client_name || '—')}</td>
      <td>${escapeHtml(license.application_name || '—')}</td>
      <td>${escapeHtml(license.package_name || '—')}${license.devices_max ? ` <span style="color:var(--text-3);font-size:11px;">(${Number(license.devices_max)} pc)</span>` : ''}</td>
      <td><span class="badge ${badge}">${escapeHtml(label)}</span></td>
      <td>${escapeHtml(license.expires_at || '—')}</td>
      <td>
        <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;" onclick="renewAdminLicense(${license.id})">Renew</button>
        ${revokeBtn}
      </td>
    </tr>`;
  }).join('');
}

async function refreshAdminLicensesTable() {
  const tbody = document.getElementById('adminLicensesTableBody');
  if (!tbody) return;

  try {
    const response = await fetch(adminLicensesUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (!response.ok) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-3);padding:28px;">Gagal memuat lisensi.</td></tr>';
      return;
    }

    renderAdminLicensesRows(data.licenses || []);
  } catch {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-3);padding:28px;">Gagal memuat lisensi.</td></tr>';
  }
}

async function renewAdminLicense(licenseId) {
  const license = adminLicensesCache.find((item) => item.id === licenseId);
  const licenseKey = license?.license_key || `#${licenseId}`;

  const confirmed = await openAppDialog({
    type: 'confirm',
    title: 'Perpanjang Lisensi',
    message: `Perpanjang lisensi ${licenseKey}? Masa berlaku akan dihitung ulang dari paket saat ini.`,
    okText: 'Renew',
  });
  if (!confirmed) return;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  try {
    const response = await fetch(`${adminLicensesUrl}/${licenseId}/renew`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
    });
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      await showAppAlert(data.message || 'Gagal memperpanjang lisensi.');
      return;
    }

    await refreshAdminLicensesTable();
    await showAppAlert(data.message || 'Lisensi berhasil diperpanjang.', 'success');
  } catch {
    await showAppAlert('Terjadi kesalahan jaringan. Silakan coba lagi.');
  }
}

async function revokeAdminLicense(licenseId) {
  const license = adminLicensesCache.find((item) => item.id === licenseId);
  const licenseKey = license?.license_key || `#${licenseId}`;

  const confirmed = await openAppDialog({
    type: 'confirm',
    title: 'Cabut Lisensi',
    message: `Yakin ingin mencabut lisensi ${licenseKey}? Semua perangkat teraktivasi untuk aplikasi ini akan dinonaktifkan.`,
    okText: 'Revoke',
    danger: true,
  });
  if (!confirmed) return;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  try {
    const response = await fetch(`${adminLicensesUrl}/${licenseId}/revoke`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
    });
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      await showAppAlert(data.message || 'Gagal mencabut lisensi.');
      return;
    }

    await refreshAdminLicensesTable();
    await showAppAlert(data.message || 'Lisensi berhasil dicabut.', 'success');
  } catch {
    await showAppAlert('Terjadi kesalahan jaringan. Silakan coba lagi.');
  }
}

function adminTransactionStatusBadge(status) {
  const map = {
    paid: 'badge-paid',
    pending: 'badge-pending',
    failed: 'badge-expired',
    refunded: 'badge-expired',
  };
  return map[status] || 'badge-pending';
}

function formatAdminCurrency(value) {
  const num = Number(value ?? 0);
  if (Number.isNaN(num)) return 'Rp 0';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(num);
}

function renderAdminTransactionsSummary(summary = {}) {
  const totalCountEl = document.getElementById('adminTransactionsTotalCount');
  const totalAmountEl = document.getElementById('adminTransactionsTotalAmount');
  const monthCountEl = document.getElementById('adminTransactionsMonthCount');
  const monthAmountEl = document.getElementById('adminTransactionsMonthAmount');

  if (totalCountEl) totalCountEl.textContent = String(summary.total_count ?? 0);
  if (totalAmountEl) totalAmountEl.textContent = formatAdminCurrency(summary.total_amount ?? 0);
  if (monthCountEl) monthCountEl.textContent = String(summary.month_count ?? 0);
  if (monthAmountEl) monthAmountEl.textContent = formatAdminCurrency(summary.month_amount ?? 0);
}

function renderAdminTransactionGatewayBody(gateway = {}) {
  if (!gateway?.has_details) {
    return `<div class="admin-app-acc-empty">${escapeHtml(gateway?.message || 'Tidak ada detail payment gateway.')}</div>`;
  }

  const summaryRows = (gateway.summary || []).map((row) => `<tr>
    <td>${escapeHtml(row.label || '—')}</td>
    <td>${escapeHtml(row.value || '—')}</td>
  </tr>`).join('');

  const createJson = escapeHtml(JSON.stringify(gateway.create_response || {}, null, 2));
  const fulfillJson = escapeHtml(JSON.stringify(gateway.fulfill_response || {}, null, 2));

  return `<div class="admin-trx-gateway-grid">
    <div>
      <h4 class="admin-trx-gateway-title">Ringkasan Gateway</h4>
      <table class="admin-trx-gateway-table"><tbody>${summaryRows}</tbody></table>
    </div>
    <div>
      <h4 class="admin-trx-gateway-title">Response Create Payment</h4>
      <pre class="admin-trx-gateway-json">${createJson || '{}'}</pre>
      <h4 class="admin-trx-gateway-title" style="margin-top:12px;">Response Fulfill / Callback</h4>
      <pre class="admin-trx-gateway-json">${fulfillJson || '{}'}</pre>
    </div>
  </div>`;
}

function toggleAdminTransactionGateway(trxId, btn) {
  const row = document.getElementById(`adminTrxAcc-${trxId}`);
  const mainRow = document.querySelector(`tr.admin-trx-main-row[data-transaction-id="${trxId}"]`);
  if (!row || !btn) return;

  const willOpen = row.hidden;
  row.hidden = !willOpen;
  btn.classList.toggle('is-open', willOpen);
  btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
  if (mainRow) mainRow.classList.toggle('is-open', willOpen);
}

function renderAdminTransactionsRows(transactions) {
  const tbody = document.getElementById('adminTransactionsTableBody');
  const countEl = document.getElementById('adminTransactionsCount');
  if (!tbody) return;

  adminTransactionsCache = transactions;

  if (countEl) {
    countEl.textContent = `${transactions.length} transaksi tercatat`;
  }

  if (!transactions.length) {
    tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:var(--text-3);padding:28px;">Belum ada transaksi.</td></tr>';
    return;
  }

  tbody.innerHTML = transactions.map((trx) => {
    const badge = adminTransactionStatusBadge(trx.status);
    const statusLabel = trx.status_label || trx.status || '—';
    const gatewayCell = trx.has_gateway_details
      ? `<button type="button" class="admin-app-acc-toggle" onclick="toggleAdminTransactionGateway(${trx.id}, this)" aria-expanded="false">
          <span>Detail</span>
          <svg class="admin-app-acc-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>`
      : '<span style="color:var(--text-3);font-size:11px;">—</span>';

    return `<tr class="admin-trx-main-row" data-transaction-id="${trx.id}">
      <td class="td-mono">${escapeHtml(trx.reference || '—')}</td>
      <td class="td-bold">${escapeHtml(trx.client_name || '—')}</td>
      <td>${escapeHtml(trx.type_label || trx.type || '—')}</td>
      <td>${escapeHtml(trx.description || '—')}</td>
      <td class="td-green">${escapeHtml(trx.amount_label || formatAdminCurrency(trx.amount))}</td>
      <td>${escapeHtml(trx.payment_method_label || trx.payment_method || '—')}</td>
      <td>${escapeHtml(trx.paid_at_date || trx.paid_at || '—')}</td>
      <td><span class="badge ${badge}">${escapeHtml(statusLabel)}</span></td>
      <td>${gatewayCell}</td>
    </tr>
    <tr class="admin-trx-acc-row" id="adminTrxAcc-${trx.id}" hidden>
      <td colspan="9">
        <div class="admin-trx-acc-panel">
          ${renderAdminTransactionGatewayBody(trx.gateway || {})}
        </div>
      </td>
    </tr>`;
  }).join('');
}

async function refreshAdminTransactionsTable() {
  const tbody = document.getElementById('adminTransactionsTableBody');
  if (!tbody) return;

  try {
    const response = await fetch(adminTransactionsUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (!response.ok) {
      tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:var(--text-3);padding:28px;">Gagal memuat transaksi.</td></tr>';
      return;
    }

    renderAdminTransactionsSummary(data.summary || {});
    renderAdminTransactionsRows(data.transactions || []);
  } catch {
    tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:var(--text-3);padding:28px;">Gagal memuat transaksi.</td></tr>';
  }
}

function switchAdminTransactionsTab(tab, btn) {
  adminTransactionsActiveTab = tab;

  document.querySelectorAll('[data-admin-trx-tab]').forEach((el) => {
    el.classList.toggle('active', el === btn);
  });

  const ledgerPanel = document.getElementById('adminTransactionsLedgerPanel');
  const invoicesPanel = document.getElementById('adminTransactionsInvoicesPanel');

  if (ledgerPanel) ledgerPanel.hidden = tab !== 'ledger';
  if (invoicesPanel) invoicesPanel.hidden = tab !== 'invoices';

  if (tab === 'invoices') refreshAdminPaymentOrdersTable();
}

function adminPaymentOrderStatusBadge(status) {
  const map = {
    paid: 'badge-paid',
    pending: 'badge-pending',
    expired: 'badge-expired',
    canceled: 'badge-expired',
  };
  return map[status] || 'badge-pending';
}

function renderAdminPaymentOrdersSummary(summary = {}) {
  const totalEl = document.getElementById('adminPaymentOrdersTotalCount');
  const pendingEl = document.getElementById('adminPaymentOrdersPendingCount');
  const paidEl = document.getElementById('adminPaymentOrdersPaidCount');
  const expiredEl = document.getElementById('adminPaymentOrdersExpiredCount');

  if (totalEl) totalEl.textContent = String(summary.total_count ?? 0);
  if (pendingEl) pendingEl.textContent = String(summary.pending_count ?? 0);
  if (paidEl) paidEl.textContent = String(summary.paid_count ?? 0);
  if (expiredEl) expiredEl.textContent = String(summary.expired_count ?? 0);
}

function toggleAdminPaymentOrderGateway(orderId, btn) {
  const row = document.getElementById(`adminPayAcc-${orderId}`);
  const mainRow = document.querySelector(`tr.admin-pay-main-row[data-payment-order-id="${orderId}"]`);
  if (!row || !btn) return;

  const willOpen = row.hidden;
  row.hidden = !willOpen;
  btn.classList.toggle('is-open', willOpen);
  btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
  if (mainRow) mainRow.classList.toggle('is-open', willOpen);
}

function renderAdminPaymentOrdersRows(orders) {
  const tbody = document.getElementById('adminPaymentOrdersTableBody');
  const countEl = document.getElementById('adminPaymentOrdersCount');
  if (!tbody) return;

  adminPaymentOrdersCache = orders;

  if (countEl) {
    countEl.textContent = `${orders.length} invoice payment customer`;
  }

  if (!orders.length) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-3);padding:28px;">Belum ada invoice gateway.</td></tr>';
    return;
  }

  tbody.innerHTML = orders.map((order) => {
    const badge = adminPaymentOrderStatusBadge(order.status);
    const statusLabel = order.has_gateway_mismatch && order.gateway_status_label
      ? `${order.gateway_status_label} (Gateway)`
      : (order.status_label || order.status || '—');
    const badgeClass = order.has_gateway_mismatch && order.gateway_status
      ? adminPaymentOrderStatusBadge(String(order.gateway_status).toLowerCase())
      : badge;

    return `<tr class="admin-pay-main-row" data-payment-order-id="${order.id}">
      <td class="td-mono">
        <span class="cell-main">${escapeHtml(order.payment_id || order.order_id || '—')}</span>
        <span class="cell-sub">${escapeHtml(order.created_at || '—')}</span>
      </td>
      <td class="td-bold">${escapeHtml(order.customer_name || '—')}</td>
      <td>
        <span class="cell-main td-green">${escapeHtml(order.amount_label || formatAdminCurrency(order.amount))}</span>
        <span class="cell-sub">${escapeHtml(order.payment_type_label || order.payment_type || '—')}</span>
      </td>
      <td><span class="badge ${badgeClass}">${escapeHtml(statusLabel)}</span></td>
      <td>${escapeHtml(order.expires_at || '—')}</td>
      <td>
        <button type="button" class="admin-app-acc-toggle" onclick="toggleAdminPaymentOrderGateway(${order.id}, this)" aria-expanded="false">
          <span>Detail</span>
          <svg class="admin-app-acc-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
      </td>
    </tr>
    <tr class="admin-trx-acc-row" id="adminPayAcc-${order.id}" hidden>
      <td colspan="6">
        <div class="admin-trx-acc-panel">
          ${renderAdminTransactionGatewayBody(order.gateway || {})}
        </div>
      </td>
    </tr>`;
  }).join('');
}

function debounceAdminPaymentOrdersSearch() {
  clearTimeout(adminPaymentOrdersSearchTimer);
  adminPaymentOrdersSearchTimer = setTimeout(() => refreshAdminPaymentOrdersTable(), 300);
}

async function refreshAdminPaymentOrdersTable() {
  const tbody = document.getElementById('adminPaymentOrdersTableBody');
  if (!tbody) return;

  const searchEl = document.getElementById('adminPaymentOrdersSearch');
  const statusEl = document.getElementById('adminPaymentOrdersStatusFilter');
  const params = new URLSearchParams();

  if (searchEl?.value?.trim()) params.set('q', searchEl.value.trim());
  if (statusEl?.value && statusEl.value !== 'all') params.set('status', statusEl.value);

  const url = params.toString() ? `${adminPaymentOrdersUrl}?${params}` : adminPaymentOrdersUrl;

  try {
    const response = await fetch(url, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (!response.ok) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-3);padding:28px;">Gagal memuat invoice gateway.</td></tr>';
      return;
    }

    renderAdminPaymentOrdersSummary(data.summary || {});
    renderAdminPaymentOrdersRows(data.orders || []);
  } catch {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-3);padding:28px;">Gagal memuat invoice gateway.</td></tr>';
  }
}

function formatAdminPackagePrice(value) {
  const num = Number(value ?? 0);
  if (Number.isNaN(num)) return '0';
  return String(num);
}

function updateAdminApplicationPackagesEmptyState() {
  const body = document.getElementById('adminApplicationPackagesBody');
  const empty = document.getElementById('adminApplicationPackagesEmpty');
  if (!body || !empty) return;
  empty.hidden = body.children.length > 0;
}

function addAdminApplicationPackageRow(pkg = {}) {
  const body = document.getElementById('adminApplicationPackagesBody');
  if (!body) return;

  const isUnlimited = Number(pkg.duration_days ?? 30) === 0;
  const daysValue = isUnlimited ? 30 : Number(pkg.duration_days ?? 30);

  const row = document.createElement('tr');
  row.className = 'admin-app-package-row';
  row.innerHTML = `
    <td><input class="admin-app-input pkg-name" type="text" placeholder="Trial / Basic / Pro" value="${escapeHtml(pkg.name || '')}"></td>
    <td><input class="admin-app-input pkg-quota" type="number" min="1" max="999" value="${Number(pkg.devices_quota ?? 1)}" required></td>
    <td>
      <div class="pkg-days-wrap${isUnlimited ? ' is-unlimited' : ''}">
        <input class="admin-app-input pkg-days" type="number" min="1" max="36500" value="${daysValue}" ${isUnlimited ? 'disabled' : ''}>
        <span class="pkg-unlimited-label" title="Unlimited">∞</span>
        <button type="button" class="pkg-unlimited-btn${isUnlimited ? ' is-on' : ''}" onclick="toggleAdminPackageUnlimited(this)" title="Unlimited (∞)">∞</button>
      </div>
    </td>
    <td><input class="admin-app-input pkg-price" type="number" min="0" step="0.01" value="${formatAdminPackagePrice(pkg.price ?? 0)}" required></td>
    <td><button type="button" class="admin-app-pkg-remove" title="Hapus paket" onclick="removeAdminApplicationPackageRow(this)">×</button></td>
  `;
  body.appendChild(row);
  updateAdminApplicationPackagesEmptyState();
}

function toggleAdminPackageUnlimited(btn) {
  const wrap = btn?.closest('.pkg-days-wrap');
  const input = wrap?.querySelector('.pkg-days');
  if (!wrap || !input) return;

  const nextUnlimited = !wrap.classList.contains('is-unlimited');
  wrap.classList.toggle('is-unlimited', nextUnlimited);
  btn.classList.toggle('is-on', nextUnlimited);

  if (nextUnlimited) {
    input.dataset.prevValue = input.value || '30';
    input.value = '';
    input.disabled = true;
  } else {
    input.disabled = false;
    input.value = input.dataset.prevValue || '30';
  }
}

function removeAdminApplicationPackageRow(btn) {
  const row = btn?.closest('tr');
  if (row) row.remove();
  updateAdminApplicationPackagesEmptyState();
}

function clearAdminApplicationPackageRows() {
  const body = document.getElementById('adminApplicationPackagesBody');
  if (body) body.innerHTML = '';
  updateAdminApplicationPackagesEmptyState();
}

function renderAdminApplicationPackages(packages = []) {
  clearAdminApplicationPackageRows();
  (packages || []).forEach((pkg) => addAdminApplicationPackageRow(pkg));
  updateAdminApplicationPackagesEmptyState();
}

function collectAdminApplicationPackages() {
  const rows = document.querySelectorAll('#adminApplicationPackagesBody .admin-app-package-row');
  const packages = [];

  rows.forEach((row, index) => {
    const name = row.querySelector('.pkg-name')?.value?.trim();
    if (!name) return;

    packages.push({
      name,
      devices_quota: Number(row.querySelector('.pkg-quota')?.value || 1),
      duration_days: row.querySelector('.pkg-days-wrap')?.classList.contains('is-unlimited')
        ? 0
        : Number(row.querySelector('.pkg-days')?.value || 1),
      price: Number(row.querySelector('.pkg-price')?.value || 0),
      sort_order: index,
    });
  });

  return packages;
}

function showAdminApplicationAlert(message, type = 'error') {
  const alert = document.getElementById('adminApplicationAlert');
  if (!alert) return;
  alert.hidden = false;
  alert.textContent = message;
  alert.classList.remove('is-error', 'is-success');
  alert.classList.add(type === 'success' ? 'is-success' : 'is-error');
}

function clearAdminApplicationAlert() {
  const alert = document.getElementById('adminApplicationAlert');
  if (alert) {
    alert.hidden = true;
    alert.textContent = '';
    alert.classList.remove('is-error', 'is-success');
  }
}

function switchAdminApplicationPanel(panel, btn) {
  const modal = document.getElementById('adminApplicationModal');
  if (!modal) return;
  modal.querySelectorAll('.admin-app-nav-item').forEach((el) => {
    el.classList.toggle('active', el === btn || el.dataset.panel === panel);
  });
  modal.querySelectorAll('.admin-app-panel').forEach((el) => {
    el.classList.toggle('active', el.dataset.panel === panel);
  });
}

function switchAdminClientPanel(panel, btn) {
  const modal = document.getElementById('adminClientModal');
  if (!modal) return;
  modal.querySelectorAll('.admin-app-nav-item').forEach((el) => {
    el.classList.toggle('active', el === btn || el.dataset.panel === panel);
  });
  modal.querySelectorAll('.admin-app-panel').forEach((el) => {
    el.classList.toggle('active', el.dataset.panel === panel);
  });
}

function openAdminApplicationModal(applicationId = null) {
  const modal = document.getElementById('adminApplicationModal');
  const form = document.getElementById('adminApplicationForm');
  const title = document.getElementById('adminApplicationModalTitle');
  const idInput = document.getElementById('adminApplicationId');
  if (!modal || !form) return;

  clearAdminApplicationAlert();
  form.reset();
  switchAdminApplicationPanel('general', modal.querySelector('.admin-app-nav-item[data-panel="general"]'));

  const app = applicationId
    ? adminApplicationsCache.find((item) => Number(item.id) === Number(applicationId))
    : null;

  if (app) {
    title.textContent = 'Edit Aplikasi';
    idInput.value = app.id;
    document.getElementById('adminApplicationName').value = app.name || '';
    document.getElementById('adminApplicationSlug').value = app.slug || '';
    document.getElementById('adminApplicationDescription').value = app.description || '';
    document.getElementById('adminApplicationDevicesMax').value = app.default_devices_max ?? 3;
    document.getElementById('adminApplicationSortOrder').value = app.sort_order ?? 0;
    document.getElementById('adminApplicationIsActive').checked = !!app.is_active;
    renderAdminApplicationPackages(app.packages || []);
  } else {
    title.textContent = 'Tambah Aplikasi';
    idInput.value = '';
    document.getElementById('adminApplicationDevicesMax').value = 3;
    document.getElementById('adminApplicationSortOrder').value = 0;
    document.getElementById('adminApplicationIsActive').checked = true;
    renderAdminApplicationPackages([]);
  }

  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeAdminApplicationModal() {
  const modal = document.getElementById('adminApplicationModal');
  if (!modal) return;
  modal.classList.remove('open');
  document.body.style.overflow = '';
  clearAdminApplicationAlert();
  switchAdminApplicationPanel('general', modal.querySelector('.admin-app-nav-item[data-panel="general"]'));
}

async function submitAdminApplicationForm(event) {
  event.preventDefault();
  clearAdminApplicationAlert();

  const btn = document.getElementById('adminApplicationSubmitBtn');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const applicationId = document.getElementById('adminApplicationId')?.value;
  const payload = {
    name: document.getElementById('adminApplicationName')?.value.trim(),
    slug: document.getElementById('adminApplicationSlug')?.value.trim(),
    description: document.getElementById('adminApplicationDescription')?.value.trim(),
    default_devices_max: Number(document.getElementById('adminApplicationDevicesMax')?.value || 3),
    sort_order: Number(document.getElementById('adminApplicationSortOrder')?.value || 0),
    is_active: document.getElementById('adminApplicationIsActive')?.checked ?? true,
    packages: collectAdminApplicationPackages(),
  };

  const isEdit = Boolean(applicationId);
  const url = isEdit ? `${adminApplicationsUrl}/${applicationId}` : adminApplicationsUrl;
  const method = isEdit ? 'PUT' : 'POST';

  btn.disabled = true;
  btn.textContent = 'Menyimpan...';

  try {
    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      body: JSON.stringify(payload),
    });
    const data = await response.json();

    if (!response.ok) {
      const firstError = data.errors
        ? Object.values(data.errors).flat()[0]
        : (data.message || 'Gagal menyimpan aplikasi.');
      showAdminApplicationAlert(firstError);
      return;
    }

    showAdminApplicationAlert(data.message, 'success');
    await refreshAdminApplicationsTable();

    setTimeout(() => {
      closeAdminApplicationModal();
    }, 500);
  } catch {
    showAdminApplicationAlert('Terjadi kesalahan jaringan. Silakan coba lagi.');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Simpan';
  }
}

async function deleteAdminApplication(applicationId) {
  const confirmed = await openAppDialog({
    type: 'confirm',
    title: 'Hapus Aplikasi',
    message: 'Yakin ingin menghapus aplikasi ini?',
    okText: 'Hapus',
    danger: true,
  });
  if (!confirmed) return;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  try {
    const response = await fetch(`${adminApplicationsUrl}/${applicationId}`, {
      method: 'DELETE',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
    });
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      await showAppAlert(data.message || 'Gagal menghapus aplikasi.');
      return;
    }

    await refreshAdminApplicationsTable();
  } catch {
    await showAppAlert('Terjadi kesalahan jaringan.');
  }
}

function showAdminClientAlert(message, type = 'error') {
  const alert = document.getElementById('adminClientAlert');
  if (!alert) return;
  alert.hidden = false;
  alert.textContent = message;
  alert.classList.remove('is-error', 'is-success');
  alert.classList.add(type === 'success' ? 'is-success' : 'is-error');
}

function clearAdminClientAlert() {
  const alert = document.getElementById('adminClientAlert');
  if (alert) {
    alert.hidden = true;
    alert.textContent = '';
    alert.classList.remove('is-error', 'is-success');
  }
}

function getAdminClientFromDom(clientId) {
  const row = document.querySelector(`#adminClientsTableBody tr[data-client-id="${clientId}"]`);
  if (!row || !row.cells?.length) return null;

  return {
    id: Number(clientId),
    company_name: row.cells[0].textContent.trim(),
    name: row.cells[1].textContent.trim(),
    email: row.cells[2].textContent.trim(),
    whatsapp: row.cells[4].textContent.trim(),
    status: row.querySelector('.badge')?.textContent.trim().toLowerCase() || 'active',
  };
}

function formatAdminClientAppLabel(app) {
  if (app?.package?.name) {
    return `${app.name} (${app.package.name})`;
  }
  return app?.name || '—';
}

function formatAdminClientPackageMeta(pkg) {
  if (!pkg) return '—';
  const days = Number(pkg.duration_days) === 0 ? '∞' : `${Number(pkg.duration_days)} hari`;
  const price = formatAdminPackagePriceDisplay(pkg.price);
  return `Limit ${pkg.devices_quota} · ${days} · ${price}`;
}

function toggleAdminClientAppCard(checkbox) {
  const card = checkbox?.closest('.admin-client-app-card');
  const panel = card?.querySelector('.admin-client-app-packages');
  if (!panel) return;
  panel.hidden = !checkbox.checked;
  if (checkbox.checked) {
    const radios = panel.querySelectorAll('input[type="radio"]');
    if (radios.length && !panel.querySelector('input[type="radio"]:checked')) {
      radios[0].checked = true;
    }
  }
}

function getSelectedAdminClientApplications() {
  const items = [];
  document.querySelectorAll('.admin-client-app-card').forEach((card) => {
    const check = card.querySelector('.admin-client-app-check');
    if (!check?.checked) return;
    const applicationId = Number(check.value);
    const pkgInput = card.querySelector('.admin-client-pkg-option input[type="radio"]:checked');
    const packageId = Number(pkgInput?.value);
    if (applicationId > 0 && packageId > 0) {
      items.push({ application_id: applicationId, package_id: packageId });
    }
  });
  return items;
}

function validateAdminClientForm(isEdit) {
  const name = document.getElementById('adminClientName')?.value.trim();
  const company = document.getElementById('adminClientCompany')?.value.trim();
  const email = document.getElementById('adminClientEmail')?.value.trim();
  const whatsapp = document.getElementById('adminClientWhatsapp')?.value.trim();
  const password = document.getElementById('adminClientPassword')?.value;
  const status = document.getElementById('adminClientStatus')?.value;
  const modal = document.getElementById('adminClientModal');

  if (!name) {
    return { valid: false, panel: 'general', message: 'Nama lengkap wajib diisi.' };
  }
  if (!company) {
    return { valid: false, panel: 'general', message: 'Nama perusahaan wajib diisi.' };
  }
  if (!email) {
    return { valid: false, panel: 'general', message: 'Email wajib diisi.' };
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    return { valid: false, panel: 'general', message: 'Format email tidak valid.' };
  }
  if (!whatsapp) {
    return { valid: false, panel: 'general', message: 'Nomor WhatsApp wajib diisi.' };
  }
  if (!/^08[0-9]{8,13}$/.test(whatsapp)) {
    return { valid: false, panel: 'general', message: 'Nomor WhatsApp harus dimulai 08 dan 10-15 digit.' };
  }
  if (!isEdit && (!password || password.length < 8)) {
    return { valid: false, panel: 'general', message: 'Password wajib diisi minimal 8 karakter.' };
  }
  if (isEdit && password && password.length < 8) {
    return { valid: false, panel: 'general', message: 'Password minimal 8 karakter jika diubah.' };
  }
  if (!status) {
    return { valid: false, panel: 'applications', message: 'Status wajib dipilih.' };
  }

  const checkedApps = document.querySelectorAll('.admin-client-app-check:checked').length;
  const assignments = getSelectedAdminClientApplications();
  if (checkedApps > 0 && assignments.length !== checkedApps) {
    return { valid: false, panel: 'applications', message: 'Pilih identitas paket untuk setiap aplikasi yang dicentang.' };
  }

  if (modal) {
    modal.querySelectorAll('.admin-app-nav-item').forEach((el) => {
      if (el.dataset.panel === 'general' || el.dataset.panel === 'applications') {
        el.classList.remove('has-error');
      }
    });
  }

  return { valid: true };
}

function focusAdminClientPanel(panel) {
  const modal = document.getElementById('adminClientModal');
  if (!modal) return;
  const btn = modal.querySelector(`.admin-app-nav-item[data-panel="${panel}"]`);
  switchAdminClientPanel(panel, btn);
  if (btn) btn.classList.add('has-error');
}

async function renderAdminClientApplicationOptions(selectedApps = []) {
  const container = document.getElementById('adminClientApplications');
  if (!container) return;

  if (!adminApplicationsCache.length) {
    await refreshAdminApplicationsTable(true);
  }

  if (!adminApplicationsCache.length) {
    container.innerHTML = '<div class="admin-app-check-empty">Belum ada aplikasi. Tambahkan di Master Aplikasi.</div>';
    return;
  }

  const selectedMap = new Map(
    (selectedApps || []).map((app) => [Number(app.id), Number(app.package_id || app.package?.id || 0)])
  );

  container.innerHTML = adminApplicationsCache.map((app) => {
    const appId = Number(app.id);
    const isChecked = selectedMap.has(appId);
    const selectedPackageId = selectedMap.get(appId) || 0;
    const packages = app.packages || [];

    if (!packages.length) {
      return `<div class="admin-client-app-card is-disabled">
        <div class="admin-client-app-head">
          <strong>${escapeHtml(app.name)}</strong>
          <small>${escapeHtml(app.slug)} · belum ada paket di Master Aplikasi</small>
        </div>
      </div>`;
    }

    const defaultPackageId = selectedPackageId || Number(packages[0]?.id);
    const packageOptions = packages.map((pkg) => {
      const checked = Number(pkg.id) === defaultPackageId ? 'checked' : '';
      return `<label class="admin-client-pkg-option">
        <input type="radio" name="adminClientPkg_${appId}" value="${pkg.id}" ${checked}>
        <span class="admin-client-pkg-body">
          <strong>${escapeHtml(pkg.name)}</strong>
          <small>${escapeHtml(formatAdminClientPackageMeta(pkg))}</small>
        </span>
      </label>`;
    }).join('');

    return `<div class="admin-client-app-card" data-app-id="${appId}">
      <label class="admin-client-app-head">
        <input type="checkbox" class="admin-client-app-check" value="${appId}" ${isChecked ? 'checked' : ''} onchange="toggleAdminClientAppCard(this)">
        <span>
          <strong>${escapeHtml(app.name)}</strong>
          <small>${escapeHtml(app.slug)}</small>
        </span>
      </label>
      <div class="admin-client-app-packages" ${isChecked ? '' : 'hidden'}>
        <div class="admin-client-app-packages-label">Identitas paket</div>
        ${packageOptions}
      </div>
    </div>`;
  }).join('');
}

function openAdminClientModal(clientId = null) {
  const modal = document.getElementById('adminClientModal');
  const form = document.getElementById('adminClientForm');
  const title = document.getElementById('adminClientModalTitle');
  const idInput = document.getElementById('adminClientId');
  const passwordInput = document.getElementById('adminClientPassword');
  const passwordHint = document.getElementById('adminClientPasswordHint');
  if (!modal || !form) return;

  clearAdminClientAlert();
  form.reset();
  modal.querySelectorAll('.admin-app-nav-item.has-error').forEach((el) => el.classList.remove('has-error'));
  switchAdminClientPanel('general', modal.querySelector('.admin-app-nav-item[data-panel="general"]'));

  let client = clientId
    ? adminClientsCache.find((item) => Number(item.id) === Number(clientId))
    : null;

  if (clientId && !client) {
    client = getAdminClientFromDom(clientId);
  }

  if (client) {
    title.textContent = 'Edit Client';
    idInput.value = client.id;
    document.getElementById('adminClientName').value = client.name || '';
    document.getElementById('adminClientCompany').value = client.company_name || '';
    document.getElementById('adminClientEmail').value = client.email || '';
    document.getElementById('adminClientWhatsapp').value = client.whatsapp || '';
    document.getElementById('adminClientStatus').value = client.status || 'active';
    passwordInput.required = false;
    passwordHint.textContent = '(kosongkan jika tidak diubah)';
    renderAdminClientApplicationOptions(client.applications || []);
  } else {
    title.textContent = 'Tambah Client';
    idInput.value = '';
    document.getElementById('adminClientStatus').value = 'active';
    passwordInput.required = true;
    passwordHint.textContent = '(wajib)';
    renderAdminClientApplicationOptions([]);
  }

  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeAdminClientModal() {
  const modal = document.getElementById('adminClientModal');
  if (!modal) return;
  modal.classList.remove('open');
  document.body.style.overflow = '';
  clearAdminClientAlert();
  switchAdminClientPanel('general', modal.querySelector('.admin-app-nav-item[data-panel="general"]'));
}

async function submitAdminClientForm(event) {
  event.preventDefault();
  clearAdminClientAlert();

  const btn = document.getElementById('adminClientSubmitBtn');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const clientId = document.getElementById('adminClientId')?.value;
  const isEdit = Boolean(clientId);
  const validation = validateAdminClientForm(isEdit);

  if (!validation.valid) {
    focusAdminClientPanel(validation.panel);
    showAdminClientAlert(validation.message);
    document.getElementById('adminClientAlert')?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    return;
  }

  const payload = {
    name: document.getElementById('adminClientName')?.value.trim(),
    company_name: document.getElementById('adminClientCompany')?.value.trim(),
    email: document.getElementById('adminClientEmail')?.value.trim(),
    whatsapp: document.getElementById('adminClientWhatsapp')?.value.trim(),
    status: document.getElementById('adminClientStatus')?.value,
    applications: getSelectedAdminClientApplications(),
  };

  const password = document.getElementById('adminClientPassword')?.value;
  if (password) payload.password = password;

  const url = isEdit ? `${adminClientsUrl}/${clientId}` : adminClientsUrl;
  const method = isEdit ? 'PUT' : 'POST';

  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';
  }

  try {
    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
      body: JSON.stringify(payload),
    });
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      const firstError = data.errors
        ? Object.values(data.errors).flat()[0]
        : (data.message || 'Gagal menyimpan client.');
      const errorPanel = /whatsapp|password|email|name|company/i.test(firstError) ? 'general' : 'applications';
      focusAdminClientPanel(errorPanel);
      showAdminClientAlert(firstError);
      document.getElementById('adminClientAlert')?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
      return;
    }

    showAdminClientAlert(data.message, 'success');
    await refreshAdminClientsTable();

    setTimeout(() => {
      closeAdminClientModal();
    }, 500);
  } catch {
    showAdminClientAlert('Terjadi kesalahan jaringan. Silakan coba lagi.');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Simpan';
    }
  }
}

async function deleteAdminClient(clientId) {
  const confirmed = await openAppDialog({
    type: 'confirm',
    title: 'Hapus Client',
    message: 'Yakin ingin menghapus client ini? Semua data lisensi terkait juga akan dihapus.',
    okText: 'Hapus',
    danger: true,
  });
  if (!confirmed) return;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  try {
    const response = await fetch(`${adminClientsUrl}/${clientId}`, {
      method: 'DELETE',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
    });
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      await showAppAlert(data.message || 'Gagal menghapus client.');
      return;
    }

    await refreshAdminClientsTable();
  } catch {
    await showAppAlert('Terjadi kesalahan jaringan. Silakan coba lagi.');
  }
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

  initAppState();
});

document.addEventListener('visibilitychange', () => {
  if (document.visibilityState !== 'visible') return;
  const licensePage = document.getElementById('client-license');
  if (licensePage?.classList.contains('active')) {
    loadClientLicense();
  }
});
