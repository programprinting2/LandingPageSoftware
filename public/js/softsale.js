const {
  plans: PLANS = [],
  appMode = 'landing',
  landingUrl = '/',
  adminDashboardUrl = '/admin',
  clientDashboardUrl = '/client',
  affiliateDashboardUrl = '/affiliate',
  referralUrl = '',
  registerClientUrl = '/register/client',
  verificationResendUrl = '/email/verification-resend',
  loginClientUrl = '/login/client',
  logoutUrl = '/logout',
  clientMeUrl = '/api/client/me',
  clientLicenseUrl = '/api/client/license',
  clientLicenseDeactivateUrl = '/api/client/license/deactivate',
  clientTransactionsUrl = '/api/client/transactions',
  adminClientsUrl = '/api/admin/clients',
  adminAffiliatesUrl = '/api/admin/affiliates',
  adminCommissionsUrl = '/api/admin/commissions',
  adminApplicationsUrl = '/api/admin/applications',
  adminLicensesUrl = '/api/admin/licenses',
  adminTransactionsUrl = '/api/admin/transactions',
  adminPaymentOrdersUrl = '/api/admin/payment-orders',
  adminLandingContentUrl = '/api/admin/landing-content',
  adminLandingImageUploadUrl = '/api/admin/landing-content/upload-image',
  adminMailSettingsUrl = '/api/admin/mail-settings',
  adminMailSettingsTestUrl = '/api/admin/mail-settings/test',
} = window.SOFTSALE_DATA || {};

let appDialogResolver = null;

const APP_ROUTE_KEY = 'softsale_route';
const DEMO_PORTAL_KEY = 'softsale_demo_portal';
let adminClientsCache = [];
let adminAffiliatesCache = [];
let adminCommissionsCache = [];
let adminApplicationsCache = [];
let adminLicensesCache = [];
let adminTransactionsCache = [];
let adminPaymentOrdersCache = [];
let adminPaymentOrdersSearchTimer = null;
let adminTransactionsActiveTab = 'ledger';
let adminSettingsActiveTab = 'email';
let adminLandingCmsCache = {};
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// SVG HELPER
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function svgIcon(paths, size = 20, color = 'currentColor') {
  return `<svg width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="${color}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">${paths}</svg>`;
}
const checkSVG = svgIcon('<path d="M20 6L9 17l-5-5"/>', 15, 'var(--green)');

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// RENDER SECTIONS
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function renderPricing() {
  const grid = document.getElementById('pricingGrid');
  if (!grid) return;
  const items = window.SOFTSALE_DATA?.plans ?? PLANS;
  grid.innerHTML = items.map((p, i) => `
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// SMOOTH SCROLL
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
function toggleFaq(btn) {
  const item = btn.closest('.faq-item');
  if (!item) return;
  const isOpen = item.classList.contains('open');
  document.querySelectorAll('.faq-item.open').forEach(el => {
    el.classList.remove('open');
    el.querySelector('.faq-question')?.setAttribute('aria-expanded', 'false');
  });
  if (!isOpen) {
    item.classList.add('open');
    btn.setAttribute('aria-expanded', 'true');
  }
}

function scrollToSection(id) {
  const el = document.getElementById(id);
  if (!el) return;

  if (id === 'video') {
    const video = el.querySelector('.video-wrapper') || el;
    const navHeight = document.getElementById('navbar')?.offsetHeight || 72;
    const offset = 100;
    const top = video.getBoundingClientRect().top + window.scrollY - navHeight - offset;
    window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
    return;
  }

  el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// NAV SCROLL EFFECT + ACTIVE LINK
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
const sectionIds = ['cara-kerja', 'fitur', 'harga', 'faq'];

function updateNav() {
  const nav = document.getElementById('navbar');
  if (!nav) return;
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
  if (type === 'success') {
    alert.style.background = 'rgba(16,185,129,0.12)';
    alert.style.border = '1px solid rgba(16,185,129,0.25)';
    alert.style.color = 'var(--green)';
  } else if (type === 'warning') {
    alert.style.background = 'rgba(245,166,35,0.12)';
    alert.style.border = '1px solid rgba(245,166,35,0.25)';
    alert.style.color = 'var(--amber)';
  } else {
    alert.style.background = 'rgba(240,104,104,0.12)';
    alert.style.border = '1px solid rgba(240,104,104,0.25)';
    alert.style.color = 'var(--rose)';
  }
}

function showVerificationResend(email) {
  const wrap = document.getElementById('verificationResendWrap');
  const emailEl = document.getElementById('verificationResendEmail');
  if (!wrap || !emailEl) return;
  emailEl.textContent = email || '';
  wrap.hidden = false;
}

function hideVerificationResend() {
  const wrap = document.getElementById('verificationResendWrap');
  if (wrap) wrap.hidden = true;
}

async function resendVerificationEmail() {
  const email = document.getElementById('verificationResendEmail')?.textContent?.trim()
    || document.getElementById('loginEmail')?.value?.trim()
    || document.getElementById('registerEmail')?.value?.trim();
  if (!email) {
    showAuthAlert('Masukkan email terlebih dahulu.', 'warning');
    return;
  }

  const btn = document.getElementById('verificationResendBtn');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Mengirim...';
  }

  try {
    const response = await fetch(verificationResendUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      body: JSON.stringify({ email }),
    });
    const data = await response.json();
    showAuthAlert(data.message || 'Link verifikasi telah dikirim.', data.verified ? 'success' : 'warning');
    if (data.verified) hideVerificationResend();
  } catch {
    showAuthAlert('Gagal mengirim ulang email verifikasi.', 'error');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Kirim Ulang Link Verifikasi';
    }
  }
}

function clearAuthAlert() {
  const alert = document.getElementById('authAlert');
  if (alert) {
    alert.style.display = 'none';
    alert.textContent = '';
  }
  hideVerificationResend();
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
  if (appMode !== 'landing') {
    const short = window.location.hash.match(/^#\/([a-z-]+)$/);
    if (short) return { portal: appMode, page: short[1] };
  }

  const match = window.location.hash.match(/^#\/(client|admin|affiliate)\/([a-z-]+)$/);
  if (!match) return null;
  return { portal: match[1], page: match[2] };
}

function dashboardUrlFor(portal) {
  const urls = {
    admin: adminDashboardUrl,
    client: clientDashboardUrl,
    affiliate: affiliateDashboardUrl,
  };
  return urls[portal] || landingUrl;
}

function resolveDashboardPage(portal) {
  const fromHash = parseRouteHash();
  if (fromHash?.portal === portal) return fromHash.page;

  const saved = getSavedRoute();
  if (saved?.portal === portal) return saved.page;

  return 'overview';
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
  const hash = appMode !== 'landing' && appMode === portal
    ? `#/${page}`
    : `#/${portal}/${page}`;
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
  const navbar = document.getElementById('navbar');
  if (navbar) navbar.style.display = 'none';
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
  if (portal === 'admin' && page === 'affiliates') refreshAdminAffiliatesTable();
  if (portal === 'admin' && page === 'commissions') refreshAdminCommissionsTable();
  if (portal === 'admin' && page === 'applications') refreshAdminApplicationsTable();
  if (portal === 'admin' && page === 'licenses') refreshAdminLicensesTable();
  if (portal === 'admin' && page === 'transactions') {
    refreshAdminTransactionsTable();
    if (adminTransactionsActiveTab === 'invoices') refreshAdminPaymentOrdersTable();
  }
  if (portal === 'admin' && page === 'overview') renderRevenueBars();
  if (portal === 'admin' && page === 'landing-cms') refreshAdminLandingCms();
  if (portal === 'admin' && page === 'settings') {
    switchAdminSettingsTab(adminSettingsActiveTab);
    if (adminSettingsActiveTab === 'email') refreshAdminMailSettings();
  }
}

async function initAppState() {
  if (appMode === 'landing') {
    const params = new URLSearchParams(window.location.search);
    if (params.get('login')) openModal('login');
    if (params.get('verified') === 'already') {
      openModal('login');
      showAuthAlert('Email sudah diverifikasi. Silakan login.', 'success');
    }
    return;
  }

  if (appMode === 'client') {
    const clientLoggedIn = await restoreClientSession();
    if (!clientLoggedIn) {
      window.location.href = `${landingUrl}?login=client`;
      return;
    }
    applyDashboardRoute('client', resolveDashboardPage('client'));
    return;
  }

  if (appMode === 'admin' || appMode === 'affiliate') {
    applyDashboardRoute(appMode, resolveDashboardPage(appMode));
  }
}

function showClientDashboard(closeModalOnShow = true) {
  if (closeModalOnShow) closeModal();
  if (appMode === 'landing') {
    window.location.href = clientDashboardUrl;
    return;
  }
  document.getElementById('appLanding')?.classList.add('hidden');
  const navbar = document.getElementById('navbar');
  if (navbar) navbar.style.display = 'none';
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
  if (appMode === 'landing') {
    window.location.href = dashboardUrlFor(role);
    return;
  }
  document.getElementById('appLanding')?.classList.add('hidden');
  const navbar = document.getElementById('navbar');
  if (navbar) navbar.style.display = 'none';
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
    closeModal();
    window.location.href = dashboardUrlFor(role);
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
      if (response.status === 403 && data.requires_verification) {
        showAuthAlert(data.message, 'warning');
        showVerificationResend(data.email || payload.email);
        return;
      }
      const firstError = data.errors
        ? Object.values(data.errors).flat()[0]
        : (data.message || 'Login gagal.');
      showAuthAlert(firstError);
      return;
    }

    updateClientDashboard(data.client);
    if (appMode === 'landing') {
      window.location.href = clientDashboardUrl;
      return;
    }
    showClientDashboard(false);
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

    form.reset();
    updateRegisterRole('Client');
    switchTab('login');
    if (data.requires_verification) {
      showAuthAlert(data.message, 'warning');
      showVerificationResend(data.email || payload.email);
    } else {
      showAuthAlert(data.message, 'success');
    }
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

  clearAppRoute();
  localStorage.removeItem(DEMO_PORTAL_KEY);

  if (appMode !== 'landing') {
    window.location.href = landingUrl;
    return;
  }

  document.querySelectorAll('.app-dashboard').forEach((d) => d.classList.remove('active'));
  document.getElementById('appLanding')?.classList.remove('hidden');
  const navbar = document.getElementById('navbar');
  if (navbar) navbar.style.display = '';
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
  if (btn) {
    const sidebar = btn.closest('.dash-nav');
    sidebar.querySelectorAll('.dash-nav-item').forEach((b) => b.classList.remove('active'));
    btn.classList.add('active');
  }

  applyDashboardRoute(portal, page);
}

function adminClientStatusLabel(status) {
  return status === 'inactive' ? 'Nonaktif' : 'Aktif';
}

function adminClientStatusBadge(status) {
  const map = {
    active: 'badge-active',
    inactive: 'badge-expired',
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
    const label = adminClientStatusLabel(client.status);
    const apps = (client.applications || []).map((app) => formatAdminClientAppLabel(app)).join(', ') || '—';
    return `<tr data-client-id="${client.id}">
      <td class="td-bold">${escapeHtml(client.company_name || '—')}</td>
      <td>${escapeHtml(client.name || '—')}</td>
      <td>${escapeHtml(client.email || '—')}${client.email_verified ? ' <span class="badge badge-active" style="font-size:10px;padding:2px 6px;margin-left:4px;">Verified</span>' : ''}</td>
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

function adminAffiliateStatusBadge(status) {
  const map = {
    active: 'badge-active',
    pending: 'badge-pending',
    suspended: 'badge-expired',
  };
  return map[status] || 'badge-pending';
}

function adminAffiliateStatusLabel(status) {
  const map = {
    active: 'Active',
    pending: 'Pending',
    suspended: 'Suspended',
  };
  return map[status] || (status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Pending');
}

function renderAdminAffiliatesRows(affiliates) {
  const tbody = document.getElementById('adminAffiliatesTableBody');
  const countEl = document.getElementById('adminAffiliatesCount');
  if (!tbody) return;

  adminAffiliatesCache = affiliates;

  if (countEl) {
    countEl.textContent = `${affiliates.length} affiliate terdaftar`;
  }

  if (!affiliates.length) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-3);padding:28px;">Belum ada affiliate terdaftar.</td></tr>';
    return;
  }

  tbody.innerHTML = affiliates.map((affiliate) => {
    const badge = adminAffiliateStatusBadge(affiliate.status);
    const label = adminAffiliateStatusLabel(affiliate.status);
    return `<tr data-affiliate-id="${affiliate.id}">
      <td class="td-mono">${escapeHtml(affiliate.code || '—')}</td>
      <td class="td-bold">${escapeHtml(affiliate.name || '—')}</td>
      <td>${escapeHtml(affiliate.email || '—')}</td>
      <td>${escapeHtml(String(affiliate.sales_count ?? 0))}</td>
      <td class="td-green">${escapeHtml(affiliate.total_commission_label || 'Rp 0')}</td>
      <td class="td-amber">${escapeHtml(affiliate.pending_commission_label || 'Rp 0')}</td>
      <td><span class="badge ${badge}">${escapeHtml(label)}</span></td>
      <td>
        <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;margin-right:4px;" onclick="openAdminAffiliateTransactionsModal(${affiliate.id})">Detail</button>
        <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;margin-right:4px;" onclick="openAdminAffiliateModal(${affiliate.id})">Edit</button>
        <button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;color:var(--rose);" onclick="deleteAdminAffiliate(${affiliate.id})">Hapus</button>
      </td>
    </tr>`;
  }).join('');
}

async function refreshAdminAffiliatesTable() {
  const tbody = document.getElementById('adminAffiliatesTableBody');
  if (!tbody) return;

  try {
    const response = await fetch(adminAffiliatesUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (!response.ok) {
      return;
    }

    renderAdminAffiliatesRows(data.affiliates || []);
  } catch {
    // Biarkan data server-side jika request gagal.
  }
}

function renderAdminCommissionsRows(commissions = [], summary = {}) {
  adminCommissionsCache = commissions;

  const totalPaidEl = document.getElementById('adminCommissionsTotalPaid');
  const pendingEl = document.getElementById('adminCommissionsPending');
  const totalCountEl = document.getElementById('adminCommissionsTotalCount');
  const countEl = document.getElementById('adminCommissionsCount');
  const tbody = document.getElementById('adminCommissionsTableBody');

  if (totalPaidEl) totalPaidEl.textContent = summary.total_paid_out_label || 'Rp 0';
  if (pendingEl) pendingEl.textContent = summary.pending_payout_label || 'Rp 0';
  if (totalCountEl) totalCountEl.textContent = String(summary.total_count ?? commissions.length ?? 0);
  if (countEl) countEl.textContent = `${commissions.length} komisi referral`;

  if (!tbody) return;

  if (!commissions.length) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-3);padding:24px;">Belum ada komisi affiliate.</td></tr>';
    return;
  }

  tbody.innerHTML = commissions.map((item) => {
    const isPaid = item.payout_status === 'paid';
    const badge = isPaid ? 'badge-paid' : 'badge-pending';
    const amountClass = isPaid ? 'td-green' : 'td-amber';
    const actionCell = isPaid
      ? ''
      : `<button type="button" class="btn btn-primary" style="padding:4px 10px;font-size:11px;" onclick="payAdminCommission(${item.id})">Bayar</button>`;

    return `<tr data-commission-id="${item.id}">
      <td class="td-mono">${escapeHtml(item.affiliate_code || '—')}</td>
      <td>${escapeHtml(item.client_name || '—')}</td>
      <td>${escapeHtml(item.package_name || '—')}</td>
      <td class="${amountClass}">${escapeHtml(item.commission_label || '—')}</td>
      <td>${escapeHtml(item.date || '—')}</td>
      <td><span class="badge ${badge}">${escapeHtml(item.payout_status_label || '—')}</span></td>
      <td>${actionCell}</td>
    </tr>`;
  }).join('');
}

async function refreshAdminCommissionsTable() {
  const tbody = document.getElementById('adminCommissionsTableBody');
  if (!tbody) return;

  try {
    const response = await fetch(adminCommissionsUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (!response.ok) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--rose);padding:24px;">Gagal memuat data komisi.</td></tr>';
      return;
    }

    renderAdminCommissionsRows(data.commissions || [], data.summary || {});
  } catch {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--rose);padding:24px;">Gagal memuat data komisi.</td></tr>';
  }
}

async function payAdminCommission(orderId) {
  const confirmed = await openAppDialog({
    type: 'confirm',
    title: 'Bayar Komisi',
    message: 'Tandai komisi ini sebagai sudah dibayar ke affiliate?',
    okText: 'Bayar',
  });

  if (!confirmed) return;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  try {
    const response = await fetch(`${adminCommissionsUrl}/${orderId}/pay`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (!response.ok) {
      await openAppDialog({
        type: 'alert',
        title: 'Gagal',
        message: data.message || 'Gagal memproses pembayaran komisi.',
      });
      return;
    }

    await refreshAdminCommissionsTable();
    await refreshAdminAffiliatesTable();
  } catch {
    await openAppDialog({
      type: 'alert',
      title: 'Gagal',
      message: 'Terjadi kesalahan jaringan. Silakan coba lagi.',
    });
  }
}

function showAdminAffiliateAlert(message, type = 'error') {
  const alert = document.getElementById('adminAffiliateAlert');
  if (!alert) return;
  alert.hidden = false;
  alert.textContent = message;
  alert.className = `admin-app-alert ${type === 'success' ? 'is-success' : 'is-error'}`;
}

function clearAdminAffiliateAlert() {
  const alert = document.getElementById('adminAffiliateAlert');
  if (!alert) return;
  alert.hidden = true;
  alert.textContent = '';
  alert.className = 'admin-app-alert';
}

function getAdminAffiliateFromCache(affiliateId) {
  return adminAffiliatesCache.find((item) => Number(item.id) === Number(affiliateId)) || null;
}

function getAdminAffiliateFromDom(affiliateId) {
  const cached = getAdminAffiliateFromCache(affiliateId);
  if (cached) return cached;

  const row = document.querySelector(`#adminAffiliatesTableBody tr[data-affiliate-id="${affiliateId}"]`);
  if (!row || !row.cells?.length) return null;

  const statusText = (row.querySelector('.badge')?.textContent.trim() || 'Pending').toLowerCase();

  return {
    id: Number(affiliateId),
    code: row.cells[0].textContent.trim(),
    name: row.cells[1].textContent.trim(),
    email: row.cells[2].textContent.trim(),
    status: statusText === 'active' ? 'active' : statusText === 'suspended' ? 'suspended' : 'pending',
  };
}

function switchAdminAffiliatePanel(panel, btn) {
  const modal = document.getElementById('adminAffiliateModal');
  if (!modal) return;
  modal.querySelectorAll('.admin-app-nav-item').forEach((el) => {
    el.classList.toggle('active', el === btn || el.dataset.panel === panel);
  });
  modal.querySelectorAll('.admin-app-panel').forEach((el) => {
    el.classList.toggle('active', el.dataset.panel === panel);
  });
}

async function openAdminAffiliateModal(affiliateId = null) {
  const modal = document.getElementById('adminAffiliateModal');
  const form = document.getElementById('adminAffiliateForm');
  if (!modal || !form) return;

  if (affiliateId && !getAdminAffiliateFromCache(affiliateId)) {
    await refreshAdminAffiliatesTable();
  }

  clearAdminAffiliateAlert();
  form.reset();
  switchAdminAffiliatePanel('general', modal.querySelector('.admin-app-nav-item[data-panel="general"]'));

  const idEl = document.getElementById('adminAffiliateId');
  const titleEl = document.getElementById('adminAffiliateModalTitle');
  const passwordEl = document.getElementById('adminAffiliatePassword');
  const passwordHint = document.getElementById('adminAffiliatePasswordHint');
  const codeEl = document.getElementById('adminAffiliateCode');
  const submitBtn = document.getElementById('adminAffiliateSubmitBtn');

  let affiliate = null;
  if (affiliateId) {
    affiliate = getAdminAffiliateFromDom(affiliateId);
    if (!affiliate) {
      showAdminAffiliateAlert('Data affiliate tidak ditemukan. Muat ulang halaman.');
      return;
    }
  }

  if (idEl) idEl.value = affiliate ? affiliate.id : '';
  if (titleEl) titleEl.textContent = affiliate ? 'Edit Affiliate' : 'Tambah Affiliate';
  if (passwordEl) {
    passwordEl.required = !affiliate;
    passwordEl.value = '';
  }
  if (passwordHint) {
    passwordHint.textContent = affiliate ? '(kosongkan jika tidak diubah)' : '(wajib)';
  }
  if (codeEl) {
    codeEl.value = affiliate?.code || '';
    codeEl.placeholder = 'AA0001';
  }
  if (submitBtn) submitBtn.textContent = affiliate ? 'Simpan Perubahan' : 'Simpan';

  document.getElementById('adminAffiliateName').value = affiliate?.name || '';
  document.getElementById('adminAffiliateEmail').value = affiliate?.email || '';
  document.getElementById('adminAffiliateWhatsapp').value = affiliate?.whatsapp || '';
  document.getElementById('adminAffiliateStatus').value = affiliate?.status || 'pending';
  document.getElementById('adminAffiliateBankName').value = affiliate?.bank_name || '';
  document.getElementById('adminAffiliateBankAccount').value = affiliate?.bank_account || '';
  document.getElementById('adminAffiliateBankAccountName').value = affiliate?.bank_account_name || '';

  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeAdminAffiliateModal() {
  const modal = document.getElementById('adminAffiliateModal');
  if (!modal) return;
  modal.classList.remove('open');
  document.body.style.overflow = '';
  clearAdminAffiliateAlert();
  switchAdminAffiliatePanel('general', modal.querySelector('.admin-app-nav-item[data-panel="general"]'));
}

async function submitAdminAffiliateForm(event) {
  event.preventDefault();
  clearAdminAffiliateAlert();

  const affiliateId = document.getElementById('adminAffiliateId')?.value?.trim();
  const isEdit = Boolean(affiliateId);
  const password = document.getElementById('adminAffiliatePassword')?.value || '';

  if (!isEdit && password.length < 8) {
    showAdminAffiliateAlert('Password wajib diisi minimal 8 karakter.');
    return;
  }

  const payload = {
    name: document.getElementById('adminAffiliateName')?.value?.trim() || '',
    email: document.getElementById('adminAffiliateEmail')?.value?.trim() || '',
    code: document.getElementById('adminAffiliateCode')?.value?.trim() || '',
    whatsapp: document.getElementById('adminAffiliateWhatsapp')?.value?.trim() || '',
    bank_name: document.getElementById('adminAffiliateBankName')?.value?.trim() || '',
    bank_account: document.getElementById('adminAffiliateBankAccount')?.value?.trim() || '',
    bank_account_name: document.getElementById('adminAffiliateBankAccountName')?.value?.trim() || '',
    status: document.getElementById('adminAffiliateStatus')?.value || 'pending',
  };

  if (password) payload.password = password;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const submitBtn = document.getElementById('adminAffiliateSubmitBtn');
  submitBtn.disabled = true;
  submitBtn.textContent = 'Menyimpan...';

  try {
    const response = await fetch(isEdit ? `${adminAffiliatesUrl}/${affiliateId}` : adminAffiliatesUrl, {
      method: isEdit ? 'PUT' : 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
      body: JSON.stringify(payload),
    });
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
      showAdminAffiliateAlert(firstError || data.message || 'Gagal menyimpan affiliate.');
      return;
    }

    showAdminAffiliateAlert(data.message || 'Affiliate berhasil disimpan.', 'success');
    await refreshAdminAffiliatesTable();
    setTimeout(() => closeAdminAffiliateModal(), 500);
  } catch {
    showAdminAffiliateAlert('Terjadi kesalahan jaringan. Silakan coba lagi.');
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = isEdit ? 'Simpan Perubahan' : 'Simpan';
  }
}

async function openAdminAffiliateTransactionsModal(affiliateId) {
  const modal = document.getElementById('adminAffiliateTransactionsModal');
  const tbody = document.getElementById('adminAffiliateTransactionsBody');
  if (!modal || !tbody) return;

  if (!getAdminAffiliateFromCache(affiliateId)) {
    await refreshAdminAffiliatesTable();
  }

  const affiliate = getAdminAffiliateFromDom(affiliateId);
  if (!affiliate) return;

  const titleEl = document.getElementById('adminAffiliateTransactionsTitle');
  const subtitleEl = document.getElementById('adminAffiliateTransactionsSubtitle');
  if (titleEl) titleEl.textContent = `Transaksi Referral — ${affiliate.code || '—'}`;
  if (subtitleEl) {
    subtitleEl.textContent = affiliate.name
      ? `${affiliate.name} · ${affiliate.email || ''}`.trim()
      : 'Daftar transaksi dari kode affiliate';
  }

  tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-3);padding:28px;">Memuat…</td></tr>';
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';

  try {
    const response = await fetch(`${adminAffiliatesUrl}/${affiliateId}/transactions`, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (!response.ok) {
      tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-3);padding:28px;">Gagal memuat transaksi affiliate.</td></tr>';
      return;
    }

    renderAdminAffiliateTransactionsSummary(data.summary || {});
    renderAdminAffiliateTransactionsRows(data.transactions || []);
  } catch {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-3);padding:28px;">Gagal memuat transaksi affiliate.</td></tr>';
  }
}

function closeAdminAffiliateTransactionsModal() {
  const modal = document.getElementById('adminAffiliateTransactionsModal');
  if (!modal) return;
  modal.classList.remove('open');
  document.body.style.overflow = '';
}

function renderAdminAffiliateTransactionsSummary(summary) {
  const totalEl = document.getElementById('adminAffiliateTrxTotalCount');
  const paidEl = document.getElementById('adminAffiliateTrxPaidCount');
  const pendingEl = document.getElementById('adminAffiliateTrxPendingCount');
  const amountEl = document.getElementById('adminAffiliateTrxPaidAmount');

  if (totalEl) totalEl.textContent = String(summary.total_count ?? 0);
  if (paidEl) paidEl.textContent = String(summary.paid_count ?? 0);
  if (pendingEl) pendingEl.textContent = String(summary.pending_count ?? 0);
  if (amountEl) {
    amountEl.textContent = summary.paid_amount_label || formatAdminCurrency(summary.paid_amount || 0);
  }
}

function renderAdminAffiliateTransactionsRows(transactions) {
  const tbody = document.getElementById('adminAffiliateTransactionsBody');
  if (!tbody) return;

  if (!transactions.length) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-3);padding:28px;">Belum ada transaksi dari kode affiliate ini.</td></tr>';
    return;
  }

  tbody.innerHTML = transactions.map((trx) => {
    const badge = adminPaymentOrderStatusBadge(trx.status);
    const statusLabel = trx.status_label || trx.status || '—';
    const packageLabel = [trx.application_name, trx.package_name].filter(Boolean).join(' · ') || '—';
    const dateLabel = trx.paid_at_date || trx.paid_at || trx.created_at || '—';

    return `<tr>
      <td class="td-mono">
        <span class="cell-main">${escapeHtml(trx.reference || trx.order_id || '—')}</span>
        <span class="cell-sub">${escapeHtml(trx.referral_code || '—')}</span>
      </td>
      <td class="td-bold">
        <span class="cell-main">${escapeHtml(trx.customer_name || '—')}</span>
        <span class="cell-sub">${escapeHtml(trx.customer_email || '—')}</span>
      </td>
      <td>${escapeHtml(packageLabel)}</td>
      <td>${escapeHtml(trx.type_label || trx.type || '—')}</td>
      <td class="td-green">${escapeHtml(trx.amount_label || formatAdminCurrency(trx.amount))}</td>
      <td class="td-amber">${escapeHtml(trx.commission_label || (trx.commission_amount ? formatAdminCurrency(trx.commission_amount) : '—'))}</td>
      <td><span class="badge ${badge}">${escapeHtml(statusLabel)}</span></td>
      <td>${escapeHtml(dateLabel)}</td>
    </tr>`;
  }).join('');
}

async function deleteAdminAffiliate(affiliateId) {
  const confirmed = await openAppDialog({
    type: 'confirm',
    title: 'Hapus Affiliate',
    message: 'Yakin ingin menghapus affiliate ini? Akun login affiliate juga akan dihapus.',
    okText: 'Hapus',
    danger: true,
  });
  if (!confirmed) return;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  try {
    const response = await fetch(`${adminAffiliatesUrl}/${affiliateId}`, {
      method: 'DELETE',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
    });
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      await showAppAlert(data.message || 'Gagal menghapus affiliate.');
      return;
    }

    await refreshAdminAffiliatesTable();
  } catch {
    await showAppAlert('Terjadi kesalahan jaringan. Silakan coba lagi.');
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
        <th>Komisi</th>
      </tr>
    </thead>
    <tbody>
      ${packages.map((pkg) => `<tr>
        <td class="td-bold">${escapeHtml(pkg.name || '—')}</td>
        <td>${Number(pkg.devices_quota ?? 0)}</td>
        <td>${formatAdminPackageDaysLabel(pkg.duration_days)}</td>
        <td class="acc-price">${escapeHtml(formatAdminPackagePriceDisplay(pkg.price))}</td>
        <td>${escapeHtml(formatAdminPackageCommissionLabel(pkg))}</td>
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
    tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;color:var(--text-3);padding:28px;">Belum ada transaksi.</td></tr>';
    return;
  }

  tbody.innerHTML = transactions.map((trx) => {
    const badge = adminTransactionStatusBadge(trx.status);
    const statusLabel = trx.status_label || trx.status || '—';
    const referralCell = trx.referral_label && trx.referral_label !== '—'
      ? `<span class="cell-main">${escapeHtml(trx.referral_code || '—')}</span>${trx.affiliate_name ? `<span class="cell-sub">${escapeHtml(trx.affiliate_name)}</span>` : ''}`
      : '<span style="color:var(--text-3);font-size:11px;">—</span>';
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
      <td>${referralCell}</td>
      <td>${escapeHtml(trx.paid_at_date || trx.paid_at || '—')}</td>
      <td><span class="badge ${badge}">${escapeHtml(statusLabel)}</span></td>
      <td>${gatewayCell}</td>
    </tr>
    <tr class="admin-trx-acc-row" id="adminTrxAcc-${trx.id}" hidden>
      <td colspan="10">
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
      tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;color:var(--text-3);padding:28px;">Gagal memuat transaksi.</td></tr>';
      return;
    }

    renderAdminTransactionsSummary(data.summary || {});
    renderAdminTransactionsRows(data.transactions || []);
  } catch {
    tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;color:var(--text-3);padding:28px;">Gagal memuat transaksi.</td></tr>';
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
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-3);padding:28px;">Belum ada invoice gateway.</td></tr>';
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
    const referralCell = order.referral_label && order.referral_label !== '—'
      ? `<span class="cell-main">${escapeHtml(order.referral_code || '—')}</span>${order.affiliate_name ? `<span class="cell-sub">${escapeHtml(order.affiliate_name)}</span>` : ''}`
      : '<span style="color:var(--text-3);font-size:11px;">—</span>';

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
      <td>${referralCell}</td>
      <td>${escapeHtml(order.expires_at || '—')}</td>
      <td>
        <button type="button" class="admin-app-acc-toggle" onclick="toggleAdminPaymentOrderGateway(${order.id}, this)" aria-expanded="false">
          <span>Detail</span>
          <svg class="admin-app-acc-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
      </td>
    </tr>
    <tr class="admin-trx-acc-row" id="adminPayAcc-${order.id}" hidden>
      <td colspan="7">
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
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-3);padding:28px;">Gagal memuat invoice gateway.</td></tr>';
      return;
    }

    renderAdminPaymentOrdersSummary(data.summary || {});
    renderAdminPaymentOrdersRows(data.orders || []);
  } catch {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--text-3);padding:28px;">Gagal memuat invoice gateway.</td></tr>';
  }
}

function formatAdminPackagePrice(value) {
  const num = Number(value ?? 0);
  if (Number.isNaN(num)) return '0';
  return String(num);
}

function parseAdminPackagePriceInput(value) {
  const raw = String(value ?? '').trim();
  if (!raw) return 0;
  const cleaned = raw.replace(/[^\d.,-]/g, '').replace(/\./g, '').replace(',', '.');
  const num = Number(cleaned);
  return Number.isNaN(num) ? 0 : Math.max(0, num);
}

function formatAdminPackagePriceDisplay(value) {
  const num = Math.max(0, Number(value ?? 0));
  if (Number.isNaN(num)) return 'Rp 0';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(num);
}

function bindAdminPackagePriceInput(input) {
  if (!input || input.dataset.priceBound === '1') return;
  input.dataset.priceBound = '1';

  input.addEventListener('focus', () => {
    input.value = String(parseAdminPackagePriceInput(input.value));
    input.select();
  });

  input.addEventListener('blur', () => {
    input.value = formatAdminPackagePriceDisplay(parseAdminPackagePriceInput(input.value));
  });
}

function formatAdminPackageCommissionLabel(pkg = {}, saleAmount = null) {
  const percent = Number(pkg.affiliate_commission_percent ?? 0);
  const fixed = Number(pkg.affiliate_commission_fixed ?? 0);
  const price = saleAmount !== null ? Number(saleAmount) : Number(pkg.price ?? 0);

  if (percent > 0) {
    const amount = Math.round(price * (percent / 100));
    return `${formatAdminPackagePrice(percent)}% (Rp ${formatAdminPackagePrice(amount)})`;
  }

  if (fixed > 0) {
    return `Rp ${formatAdminPackagePrice(fixed)}`;
  }

  return '—';
}

function formatAdminPackageCommissionPercent(value) {
  const num = Number(value ?? 0);
  if (Number.isNaN(num) || num === 0) return '0';
  const rounded = Math.round(num * 100) / 100;
  return Number.isInteger(rounded) ? String(rounded) : String(rounded);
}

function getAdminPackageRowPrice(row) {
  return parseAdminPackagePriceInput(row?.querySelector('.pkg-price')?.value || 0);
}

function setAdminPackageCommissionFields(row, { fixed, percent }, source) {
  if (!row) return;
  row.dataset.commissionUpdating = '1';
  const fixedEl = row.querySelector('.pkg-commission-fixed');
  const percentEl = row.querySelector('.pkg-commission-percent');
  if (fixedEl && typeof fixed !== 'undefined') fixedEl.value = formatAdminPackagePrice(fixed);
  if (percentEl && typeof percent !== 'undefined') percentEl.value = formatAdminPackageCommissionPercent(percent);
  if (source) row.dataset.commissionSource = source;
  delete row.dataset.commissionUpdating;
}

function syncAdminPackageCommissionFromFixed(row) {
  const price = getAdminPackageRowPrice(row);
  const fixed = Math.max(0, Number(row.querySelector('.pkg-commission-fixed')?.value || 0));
  if (price <= 0) {
    setAdminPackageCommissionFields(row, { percent: 0 }, 'fixed');
    return;
  }
  const percent = fixed > 0 ? (fixed / price) * 100 : 0;
  setAdminPackageCommissionFields(row, { percent }, 'fixed');
}

function syncAdminPackageCommissionFromPercent(row) {
  const price = getAdminPackageRowPrice(row);
  const percent = Math.max(0, Number(row.querySelector('.pkg-commission-percent')?.value || 0));
  if (price <= 0) {
    setAdminPackageCommissionFields(row, { fixed: 0 }, 'percent');
    return;
  }
  const fixed = percent > 0 ? Math.round(price * (percent / 100)) : 0;
  setAdminPackageCommissionFields(row, { fixed }, 'percent');
}

function syncAdminPackageCommissionFromPrice(row) {
  const source = row?.dataset?.commissionSource || 'percent';
  if (source === 'fixed') syncAdminPackageCommissionFromFixed(row);
  else syncAdminPackageCommissionFromPercent(row);
}

function bindAdminPackageCommissionSync(row) {
  if (!row || row.dataset.commissionBound === '1') return;
  row.dataset.commissionBound = '1';

  const priceEl = row.querySelector('.pkg-price');
  const fixedEl = row.querySelector('.pkg-commission-fixed');
  const percentEl = row.querySelector('.pkg-commission-percent');

  fixedEl?.addEventListener('input', () => {
    if (row.dataset.commissionUpdating) return;
    syncAdminPackageCommissionFromFixed(row);
  });

  percentEl?.addEventListener('input', () => {
    if (row.dataset.commissionUpdating) return;
    syncAdminPackageCommissionFromPercent(row);
  });

  priceEl?.addEventListener('input', () => {
    if (row.dataset.commissionUpdating) return;
    syncAdminPackageCommissionFromPrice(row);
  });
}

function initializeAdminPackageCommissionRow(row, pkg = {}) {
  bindAdminPackageCommissionSync(row);
  const percent = Number(pkg.affiliate_commission_percent ?? 0);
  const fixed = Number(pkg.affiliate_commission_fixed ?? 0);
  if (percent > 0) {
    row.dataset.commissionSource = 'percent';
    syncAdminPackageCommissionFromPercent(row);
  } else if (fixed > 0) {
    row.dataset.commissionSource = 'fixed';
    syncAdminPackageCommissionFromFixed(row);
  }
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
    <td class="pkg-quota-cell"><input class="admin-app-input pkg-quota" type="number" min="1" max="999" value="${Number(pkg.devices_quota ?? 1)}" required></td>
    <td>
      <div class="pkg-days-wrap${isUnlimited ? ' is-unlimited' : ''}">
        <input class="admin-app-input pkg-days" type="number" min="1" max="36500" value="${daysValue}" ${isUnlimited ? 'disabled' : ''}>
        <span class="pkg-unlimited-label" title="Unlimited">∞</span>
        <button type="button" class="pkg-unlimited-btn${isUnlimited ? ' is-on' : ''}" onclick="toggleAdminPackageUnlimited(this)" title="Unlimited (∞)">∞</button>
      </div>
    </td>
    <td class="pkg-price-cell"><input class="admin-app-input pkg-price" type="text" inputmode="numeric" value="${formatAdminPackagePriceDisplay(pkg.price ?? 0)}" required></td>
    <td class="pkg-discount-cell"><input class="admin-app-input pkg-discount" type="number" min="0" max="100" step="0.01" value="${formatAdminPackageCommissionPercent(pkg.discount_percent ?? 0)}" placeholder="0"></td>
    <td class="pkg-commission-fixed-cell"><input class="admin-app-input pkg-commission-fixed" type="number" min="0" step="1" value="${formatAdminPackagePrice(pkg.affiliate_commission_fixed ?? 0)}" placeholder="0"></td>
    <td class="pkg-commission-percent-cell"><input class="admin-app-input pkg-commission-percent" type="number" min="0" max="100" step="0.01" value="${formatAdminPackagePrice(pkg.affiliate_commission_percent ?? 0)}" placeholder="0"></td>
    <td class="col-pkg-actions-cell"><button type="button" class="admin-app-pkg-remove" title="Hapus paket" onclick="removeAdminApplicationPackageRow(this)">×</button></td>
  `;
  body.appendChild(row);
  bindAdminPackagePriceInput(row.querySelector('.pkg-price'));
  initializeAdminPackageCommissionRow(row, pkg);
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
      price: parseAdminPackagePriceInput(row.querySelector('.pkg-price')?.value || 0),
      discount_percent: Number(row.querySelector('.pkg-discount')?.value || 0),
      affiliate_commission_fixed: Number(row.querySelector('.pkg-commission-fixed')?.value || 0),
      affiliate_commission_percent: Number(row.querySelector('.pkg-commission-percent')?.value || 0),
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

    // Jangan fallback ke paket pertama (biasanya trial) jika package_id belum tersimpan.
    const defaultPackageId = selectedPackageId > 0 ? selectedPackageId : 0;
    const packageOptions = packages.map((pkg) => {
      const checked = defaultPackageId > 0 && Number(pkg.id) === defaultPackageId ? 'checked' : '';
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
    document.getElementById('adminClientEmailVerified').checked = !!client.email_verified;
    document.getElementById('adminClientWhatsapp').value = client.whatsapp || '';
    document.getElementById('adminClientStatus').value = client.status || 'active';
    passwordInput.required = false;
    passwordHint.textContent = '(kosongkan jika tidak diubah)';
    renderAdminClientApplicationOptions(client.applications || []);
  } else {
    title.textContent = 'Tambah Client';
    idInput.value = '';
    document.getElementById('adminClientStatus').value = 'active';
    document.getElementById('adminClientEmailVerified').checked = false;
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
    email_verified: !!document.getElementById('adminClientEmailVerified')?.checked,
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

function showAdminLandingAlert(message, type = 'success') {
  const el = document.getElementById('adminLandingAlert');
  if (!el) return;
  el.hidden = false;
  el.textContent = message;
  el.classList.toggle('is-error', type === 'error');
  el.classList.toggle('is-success', type !== 'error');
}

function showCmsAlert(message, type = 'success') {
  const liveEditOpen = document.getElementById('liveEditOverlay') && !document.getElementById('liveEditOverlay').hidden;
  if (liveEditOpen && typeof showLiveEditAlert === 'function') {
    showLiveEditAlert(message, type);
    return;
  }
  showAdminLandingAlert(message, type);
}

function switchAdminLandingPanel(panel, btn) {
  const root = document.getElementById('admin-landing-cms');
  if (!root) return;
  root.querySelectorAll('.admin-app-nav-item').forEach((el) => el.classList.toggle('active', el === btn));
  root.querySelectorAll('.admin-app-panel').forEach((el) => el.classList.toggle('active', el.dataset.panel === panel));
}

function adminLandingStatRowHtml(stat = {}) {
  return `<tr class="admin-cms-stat-row">
    <td><input class="admin-app-input cms-stat-value" type="text" value="${escapeHtml(stat.value || '')}"></td>
    <td><input class="admin-app-input cms-stat-label" type="text" value="${escapeHtml(stat.label || '')}"></td>
    <td style="text-align:center;"><input class="cms-stat-animate" type="checkbox"${stat.animate ? ' checked' : ''}></td>
    <td><button type="button" class="admin-cms-remove" onclick="this.closest('tr').remove()">Hapus</button></td>
  </tr>`;
}

function addAdminLandingStatRow(tbodyId) {
  const tbody = document.getElementById(tbodyId);
  if (!tbody) return;
  tbody.insertAdjacentHTML('beforeend', adminLandingStatRowHtml());
}

function collectAdminLandingStats(tbodyId) {
  const tbody = document.getElementById(tbodyId);
  if (!tbody) return [];
  return [...tbody.querySelectorAll('.admin-cms-stat-row')].map((row) => ({
    value: row.querySelector('.cms-stat-value')?.value?.trim() || '',
    label: row.querySelector('.cms-stat-label')?.value?.trim() || '',
    animate: !!row.querySelector('.cms-stat-animate')?.checked,
  })).filter((item) => item.value && item.label);
}

function adminLandingFeatureImageFieldHtml(item = {}) {
  const image = item.image || '';
  const preview = image
    ? `<img src="${escapeHtml(image)}" alt="Preview gambar fitur">`
    : '<span class="cms-feature-image-empty">Belum ada gambar</span>';

  return `<div class="form-group cms-feature-image-wrap">
    <label class="admin-app-label">Gambar</label>
    <input type="hidden" class="cms-feature-image" value="${escapeHtml(image)}">
    <div class="cms-feature-image-preview">${preview}</div>
    <div class="cms-feature-image-actions">
      <label class="btn btn-secondary cms-feature-image-pick">
        Pilih Gambar
        <input type="file" accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml" hidden class="cms-feature-image-file" onchange="uploadAdminLandingFeatureImage(this)">
      </label>
      <button type="button" class="admin-cms-remove cms-feature-image-clear" onclick="clearAdminLandingFeatureImage(this)"${image ? '' : ' hidden'}>Hapus</button>
    </div>
    <p class="cms-feature-image-hint">JPG, PNG, WebP, GIF, SVG. Maks. 5 MB.</p>
  </div>`;
}

function adminLandingFeatureCardHtml(item = {}) {
  return `<div class="admin-cms-card admin-cms-feature-row">
    <div class="admin-cms-card-head"><span>Fitur</span><button type="button" class="admin-cms-remove" onclick="this.closest('.admin-cms-card').remove()">Hapus</button></div>
    <div class="form-group"><label class="admin-app-label">Judul</label><input class="admin-app-input cms-feature-title" type="text" value="${escapeHtml(item.title || '')}"></div>
    <div class="form-group"><label class="admin-app-label">Deskripsi</label><textarea class="admin-app-input cms-feature-desc" rows="3">${escapeHtml(item.desc || '')}</textarea></div>
    ${adminLandingFeatureImageFieldHtml(item)}
  </div>`;
}

function addAdminLandingFeatureRow(item = {}) {
  document.getElementById('cmsFeaturesItems')?.insertAdjacentHTML('beforeend', adminLandingFeatureCardHtml(item));
}

function updateAdminLandingFeatureImagePreview(card, url) {
  const preview = card.querySelector('.cms-feature-image-preview');
  const hidden = card.querySelector('.cms-feature-image');
  const clearBtn = card.querySelector('.cms-feature-image-clear');
  if (!preview || !hidden) return;
  hidden.value = url || '';
  preview.innerHTML = url
    ? `<img src="${escapeHtml(url)}" alt="Preview gambar fitur">`
    : '<span class="cms-feature-image-empty">Belum ada gambar</span>';
  if (clearBtn) clearBtn.hidden = !url;
}

async function uploadAdminLandingFeatureImage(input) {
  const file = input.files?.[0];
  if (!file) return;

  const card = input.closest('.admin-cms-feature-row');
  const pickBtn = input.closest('.cms-feature-image-pick');

  const formData = new FormData();
  formData.append('image', file);

  if (pickBtn) {
    pickBtn.style.pointerEvents = 'none';
    pickBtn.style.opacity = '0.7';
  }

  try {
    const response = await fetch(adminLandingImageUploadUrl, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
      credentials: 'same-origin',
      body: formData,
    });
    const data = await response.json();
    if (!response.ok) {
      const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
      showCmsAlert(firstError || data.message || 'Gagal mengupload gambar.', 'error');
      return;
    }
    updateAdminLandingFeatureImagePreview(card, data.url || '');
  } catch {
    showCmsAlert('Gagal mengupload gambar.', 'error');
  } finally {
    input.value = '';
    if (pickBtn) {
      pickBtn.style.pointerEvents = '';
      pickBtn.style.opacity = '';
    }
  }
}

function clearAdminLandingFeatureImage(button) {
  const card = button.closest('.admin-cms-feature-row');
  const fileInput = card?.querySelector('.cms-feature-image-file');
  if (fileInput) fileInput.value = '';
  updateAdminLandingFeatureImagePreview(card, '');
}

function adminLandingFaqCardHtml(item = {}) {
  return `<div class="admin-cms-card admin-cms-faq-row">
    <div class="admin-cms-card-head"><span>FAQ</span><button type="button" class="admin-cms-remove" onclick="this.closest('.admin-cms-card').remove()">Hapus</button></div>
    <div class="form-group"><label class="admin-app-label">Pertanyaan</label><input class="admin-app-input cms-faq-question" type="text" value="${escapeHtml(item.question || '')}"></div>
    <div class="form-group"><label class="admin-app-label">Jawaban</label><textarea class="admin-app-input cms-faq-answer" rows="3">${escapeHtml(item.answer || '')}</textarea></div>
  </div>`;
}

function addAdminLandingFaqRow(item = {}) {
  document.getElementById('cmsFaqItems')?.insertAdjacentHTML('beforeend', adminLandingFaqCardHtml(item));
}

function adminLandingPricingCardHtml(item = {}) {
  const features = Array.isArray(item.features) ? item.features.join('\n') : '';
  return `<div class="admin-cms-card admin-cms-pricing-row">
    <div class="admin-cms-card-head"><span>Paket</span><button type="button" class="admin-cms-remove" onclick="this.closest('.admin-cms-card').remove()">Hapus</button></div>
    <div class="admin-app-form-grid">
      <div class="form-group"><label class="admin-app-label">Nama</label><input class="admin-app-input cms-pricing-name" type="text" value="${escapeHtml(item.name || '')}"></div>
      <div class="form-group"><label class="admin-app-label">Harga</label><input class="admin-app-input cms-pricing-price" type="text" value="${escapeHtml(item.price || '')}"></div>
      <div class="form-group"><label class="admin-app-label">Periode</label><input class="admin-app-input cms-pricing-period" type="text" value="${escapeHtml(item.period || '')}"></div>
      <div class="form-group" style="display:flex;align-items:end;padding-bottom:8px;"><label style="display:flex;gap:8px;align-items:center;font-size:12px;color:var(--text-2);"><input class="cms-pricing-popular" type="checkbox"${item.popular ? ' checked' : ''}> Populer</label></div>
    </div>
    <div class="form-group"><label class="admin-app-label">Fitur paket (satu per baris)</label><textarea class="admin-app-input cms-pricing-features" rows="4">${escapeHtml(features)}</textarea></div>
  </div>`;
}

function addAdminLandingPricingRow(item = {}) {
  document.getElementById('cmsPricingItems')?.insertAdjacentHTML('beforeend', adminLandingPricingCardHtml(item));
}

function fillAdminLandingSectionFields(section, key) {
  if (key === 'site') {
    document.getElementById('cmsSiteName').value = section.name || '';
    document.getElementById('cmsSiteTagline').value = section.tagline || '';
    return;
  }

  if (key === 'hero') {
    document.getElementById('cmsHeroTitle').value = section.title || '';
    document.getElementById('cmsHeroHighlight').value = section.title_highlight || '';
    document.getElementById('cmsHeroSuffix').value = section.title_suffix || '';
    document.getElementById('cmsHeroHighlight2').value = section.title_highlight_2 || '';
    document.getElementById('cmsHeroSubtitle').value = section.subtitle || '';
    document.getElementById('cmsHeroCtaPrimary').value = section.cta_primary || '';
    document.getElementById('cmsHeroCtaSecondary').value = section.cta_secondary || '';
    return;
  }

  if (key === 'features') {
    document.querySelector('.cms-section-title[data-section="features"]').value = section.title || '';
    const featuresContainer = document.getElementById('cmsFeaturesItems');
    featuresContainer.innerHTML = '';
    (section.items || []).forEach((item) => addAdminLandingFeatureRow(item));
    if (!section.items?.length) addAdminLandingFeatureRow();
    return;
  }

  if (['pricing', 'faq'].includes(key)) {
    const titleEl = document.querySelector(`.cms-section-title[data-section="${key}"]`);
    const subtitleEl = document.querySelector(`.cms-section-subtitle[data-section="${key}"]`);
    const badgeEl = document.querySelector(`.cms-section-badge[data-section="${key}"]`);
    if (badgeEl) badgeEl.value = section.badge || '';
    if (titleEl) titleEl.value = section.title || '';
    if (subtitleEl) subtitleEl.value = section.subtitle || '';
    const containerMap = {
      pricing: ['cmsPricingItems', addAdminLandingPricingRow],
      faq: ['cmsFaqItems', addAdminLandingFaqRow],
    };
    const [containerId, addRow] = containerMap[key];
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    (section.items || []).forEach((item) => addRow(item));
    if (!section.items?.length) addRow();
    return;
  }

  if (key === 'footer') {
    document.getElementById('cmsFooterDescription').value = section.description || '';
    document.getElementById('cmsFooterCopyright').value = section.copyright || '';
  }
}

function collectAdminLandingSectionPayload(key) {
  if (key === 'site') {
    return {
      name: document.getElementById('cmsSiteName')?.value?.trim() || '',
      tagline: document.getElementById('cmsSiteTagline')?.value?.trim() || '',
    };
  }

  if (key === 'hero') {
    return {
      title: document.getElementById('cmsHeroTitle')?.value?.trim() || '',
      title_highlight: document.getElementById('cmsHeroHighlight')?.value?.trim() || '',
      title_suffix: document.getElementById('cmsHeroSuffix')?.value?.trim() || '',
      title_highlight_2: document.getElementById('cmsHeroHighlight2')?.value?.trim() || '',
      subtitle: document.getElementById('cmsHeroSubtitle')?.value?.trim() || '',
      cta_primary: document.getElementById('cmsHeroCtaPrimary')?.value?.trim() || '',
      cta_secondary: document.getElementById('cmsHeroCtaSecondary')?.value?.trim() || '',
    };
  }

  if (key === 'features') {
    return {
      title: document.querySelector('.cms-section-title[data-section="features"]')?.value?.trim() || '',
      items: [...document.querySelectorAll('.admin-cms-feature-row')].map((row) => ({
        title: row.querySelector('.cms-feature-title')?.value?.trim() || '',
        desc: row.querySelector('.cms-feature-desc')?.value?.trim() || '',
        image: row.querySelector('.cms-feature-image')?.value?.trim() || '',
      })),
    };
  }

  if (key === 'pricing') {
    return {
      badge: document.querySelector('.cms-section-badge[data-section="pricing"]')?.value?.trim() || '',
      title: document.querySelector('.cms-section-title[data-section="pricing"]')?.value?.trim() || '',
      subtitle: document.querySelector('.cms-section-subtitle[data-section="pricing"]')?.value?.trim() || '',
      items: [...document.querySelectorAll('.admin-cms-pricing-row')].map((row) => ({
        name: row.querySelector('.cms-pricing-name')?.value?.trim() || '',
        price: row.querySelector('.cms-pricing-price')?.value?.trim() || '',
        period: row.querySelector('.cms-pricing-period')?.value?.trim() || '',
        popular: !!row.querySelector('.cms-pricing-popular')?.checked,
        features: (row.querySelector('.cms-pricing-features')?.value || '').split('\n').map((line) => line.trim()).filter(Boolean),
      })),
    };
  }

  if (key === 'faq') {
    return {
      title: document.querySelector('.cms-section-title[data-section="faq"]')?.value?.trim() || '',
      subtitle: document.querySelector('.cms-section-subtitle[data-section="faq"]')?.value?.trim() || '',
      items: [...document.querySelectorAll('.admin-cms-faq-row')].map((row) => ({
        question: row.querySelector('.cms-faq-question')?.value?.trim() || '',
        answer: row.querySelector('.cms-faq-answer')?.value?.trim() || '',
      })),
    };
  }

  if (key === 'footer') {
    return {
      description: document.getElementById('cmsFooterDescription')?.value?.trim() || '',
      copyright: document.getElementById('cmsFooterCopyright')?.value?.trim() || '',
    };
  }

  return {};
}

function renderAdminLandingCmsForms() {
  Object.entries(adminLandingCmsCache).forEach(([key, section]) => fillAdminLandingSectionFields(section, key));
}

async function refreshAdminLandingCms() {
  try {
    const response = await fetch(adminLandingContentUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();
    if (!response.ok) {
      showAdminLandingAlert(data.message || 'Gagal memuat CMS halaman utama.', 'error');
      return;
    }
    adminLandingCmsCache = data.sections || {};
    renderAdminLandingCmsForms();
  } catch {
    showAdminLandingAlert('Gagal memuat CMS halaman utama.', 'error');
  }
}

async function persistLandingSection(key, payload) {
  try {
    const response = await fetch(`${adminLandingContentUrl}/${encodeURIComponent(key)}`, {
      method: 'PUT',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
      credentials: 'same-origin',
      body: JSON.stringify({ payload }),
    });
    const data = await response.json();
    if (!response.ok) {
      const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
      return { ok: false, error: firstError || data.message || 'Gagal menyimpan konten.' };
    }
    return { ok: true, section: data.section || payload, message: data.message || 'Konten berhasil disimpan.' };
  } catch {
    return { ok: false, error: 'Gagal menyimpan konten.' };
  }
}

async function saveAdminLandingSection(key) {
  const payload = collectAdminLandingSectionPayload(key);
  const result = await persistLandingSection(key, payload);
  if (!result.ok) {
    showAdminLandingAlert(result.error, 'error');
    return;
  }
  adminLandingCmsCache[key] = result.section;
  showAdminLandingAlert(result.message);
}

function showAdminMailSettingsAlert(message, type = 'success') {
  const el = document.getElementById('adminMailSettingsAlert');
  if (!el) return;
  el.hidden = false;
  el.textContent = message;
  el.classList.toggle('is-error', type === 'error');
  el.classList.toggle('is-success', type !== 'error');
}

function switchAdminSettingsTab(tab, btn = null) {
  adminSettingsActiveTab = tab;

  document.querySelectorAll('[data-admin-settings-tab]').forEach((el) => {
    el.classList.toggle('active', btn ? el === btn : el.dataset.adminSettingsTab === tab);
  });

  const panels = {
    email: document.getElementById('adminSettingsEmailPanel'),
    commission: document.getElementById('adminSettingsCommissionPanel'),
    license: document.getElementById('adminSettingsLicensePanel'),
  };

  Object.entries(panels).forEach(([key, panel]) => {
    if (panel) panel.hidden = key !== tab;
  });

  if (tab === 'email') refreshAdminMailSettings();
}

function toggleMailSettingsSmtpFields() {
  const mailer = document.getElementById('mailSettingsMailer')?.value;
  const smtpInputs = document.querySelectorAll('.mail-settings-smtp-input');
  const smtpSection = document.getElementById('mailSettingsSmtpFields');
  const driverHint = document.getElementById('mailSettingsDriverHint');
  const isSmtp = mailer === 'smtp';

  smtpInputs.forEach((el) => {
    el.disabled = !isSmtp;
    el.closest('.form-group')?.classList.toggle('is-disabled', !isSmtp);
  });

  if (smtpSection) smtpSection.classList.toggle('is-muted', !isSmtp);

  if (driverHint) {
    driverHint.textContent = isSmtp
      ? 'Mode SMTP: email dikirim lewat server yang dikonfigurasi di bawah.'
      : mailer === 'sendmail'
        ? 'Mode Sendmail: menggunakan sendmail server lokal.'
        : 'Mode development: email tidak dikirim, hanya dicatat di storage/logs/laravel.log.';
  }
}

function applyGmailMailPreset() {
  document.getElementById('mailSettingsMailer').value = 'smtp';
  document.getElementById('mailSettingsHost').value = 'smtp.gmail.com';
  document.getElementById('mailSettingsPort').value = 587;
  document.getElementById('mailSettingsEncryption').value = 'tls';

  const username = document.getElementById('mailSettingsFromAddress')?.value?.trim()
    || document.getElementById('mailSettingsUsername')?.value?.trim();
  if (username) {
    document.getElementById('mailSettingsUsername').value = username;
    document.getElementById('mailSettingsFromAddress').value = username;
  }

  toggleMailSettingsSmtpFields();
  showAdminMailSettingsAlert('Preset Gmail diterapkan. Lengkapi Username, App Password, Email Pengirim, dan URL Aplikasi lalu Simpan.', 'success');
}

function fillAdminMailSettingsForm(settings = {}) {
  document.getElementById('mailSettingsMailer').value = settings.mailer || 'log';
  document.getElementById('mailSettingsAppUrl').value = settings.app_url || '';
  document.getElementById('mailSettingsHost').value = settings.host || '';
  document.getElementById('mailSettingsPort').value = settings.port ?? 587;
  document.getElementById('mailSettingsEncryption').value = settings.encryption || '';
  document.getElementById('mailSettingsUsername').value = settings.username || '';
  document.getElementById('mailSettingsPassword').value = '';
  document.getElementById('mailSettingsFromAddress').value = settings.from_address || '';
  document.getElementById('mailSettingsFromName').value = settings.from_name || '';
  document.getElementById('mailSettingsVerificationExpire').value = settings.verification_expire ?? 60;

  const hint = document.getElementById('mailSettingsPasswordHint');
  if (hint) {
    hint.textContent = settings.password_set ? '(sudah tersimpan — kosongkan jika tidak diubah)' : '';
  }

  const source = document.getElementById('adminMailSettingsSource');
  if (source) {
    source.textContent = settings.source === 'database' ? 'Dari database' : 'Dari .env default';
    source.className = `badge ${settings.source === 'database' ? 'badge-active' : 'badge-trial'}`;
  }

  toggleMailSettingsSmtpFields();
}

async function refreshAdminMailSettings() {
  const root = document.getElementById('admin-settings');
  if (!root) return;

  try {
    const response = await fetch(adminMailSettingsUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();
    if (!response.ok) {
      showAdminMailSettingsAlert(data.message || 'Gagal memuat pengaturan email.', 'error');
      return;
    }
    fillAdminMailSettingsForm(data.settings || {});
  } catch {
    showAdminMailSettingsAlert('Gagal memuat pengaturan email.', 'error');
  }
}

function collectAdminMailSettingsPayload() {
  return {
    mailer: document.getElementById('mailSettingsMailer')?.value || 'log',
    host: document.getElementById('mailSettingsHost')?.value?.trim() || '',
    port: Number(document.getElementById('mailSettingsPort')?.value || 587),
    encryption: document.getElementById('mailSettingsEncryption')?.value || '',
    username: document.getElementById('mailSettingsUsername')?.value?.trim() || '',
    password: document.getElementById('mailSettingsPassword')?.value || '',
    from_address: document.getElementById('mailSettingsFromAddress')?.value?.trim() || '',
    from_name: document.getElementById('mailSettingsFromName')?.value?.trim() || '',
    app_url: document.getElementById('mailSettingsAppUrl')?.value?.trim() || '',
    verification_expire: Number(document.getElementById('mailSettingsVerificationExpire')?.value || 60),
  };
}

async function saveAdminMailSettings() {
  const btn = document.getElementById('mailSettingsSaveBtn');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  btn.disabled = true;
  btn.textContent = 'Menyimpan...';

  try {
    const response = await fetch(adminMailSettingsUrl, {
      method: 'PUT',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
      body: JSON.stringify(collectAdminMailSettingsPayload()),
    });
    const data = await response.json();
    if (!response.ok) {
      const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
      showAdminMailSettingsAlert(firstError || data.message || 'Gagal menyimpan pengaturan email.', 'error');
      return;
    }
    fillAdminMailSettingsForm(data.settings || {});
    showAdminMailSettingsAlert(data.message || 'Pengaturan email berhasil disimpan.');
  } catch {
    showAdminMailSettingsAlert('Gagal menyimpan pengaturan email.', 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Simpan Pengaturan Email';
  }
}

async function sendAdminMailSettingsTest() {
  const btn = document.getElementById('mailSettingsTestBtn');
  const email = document.getElementById('mailSettingsTestEmail')?.value?.trim();
  if (!email) {
    showAdminMailSettingsAlert('Masukkan email tujuan tes.', 'error');
    return;
  }

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  btn.disabled = true;
  btn.textContent = 'Mengirim...';

  try {
    const response = await fetch(adminMailSettingsTestUrl, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
      body: JSON.stringify({ email }),
    });
    const data = await response.json();
    showAdminMailSettingsAlert(
      data.message || (response.ok ? 'Email tes terkirim.' : 'Gagal mengirim email tes.'),
      response.ok ? 'success' : 'error',
    );
  } catch {
    showAdminMailSettingsAlert('Gagal mengirim email tes.', 'error');
  } finally {
    btn.disabled = false;
    btn.textContent = 'Kirim Email Tes';
  }
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

  if (document.getElementById('appLanding')) {
    renderPricing();

    setTimeout(revealOnScroll, 100);

    window.addEventListener('scroll', () => {
      updateNav();
      revealOnScroll();
    }, { passive: true });
  }

  initAppState();
});

document.addEventListener('visibilitychange', () => {
  if (document.visibilityState !== 'visible') return;
  const licensePage = document.getElementById('client-license');
  if (licensePage?.classList.contains('active')) {
    loadClientLicense();
  }
});
