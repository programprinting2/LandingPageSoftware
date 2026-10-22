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
  loginAdminUrl = '/login/admin',
  adminMeUrl = '/api/admin/me',
  adminUsersUrl = '/api/admin/users',
  adminUsersRoleUrl = '/api/admin/users',
  adminUsersAffiliateUrl = '/api/admin/users',
  adminUsersDestroyUrl = '/api/admin/users',
  logoutUrl = '/logout',
  clientMeUrl = '/api/client/me',
  clientAffiliateUrl = '/api/client/affiliate',
  clientAffiliateEnrollUrl = '/api/client/affiliate/enroll',
  clientLicenseUrl = '/api/client/license',
  clientCheckoutUrl = '/api/client/checkout',
  clientCommercePlansUrl = '/api/client/commerce/plans',
  clientTrialActivateUrl = '/api/client/trial/activate',
  clientCheckoutStatusUrl = '/api/client/checkout',
  clientCheckoutSimulateUrl = '/api/client/checkout',
  clientLicenseDeactivateUrl = '/api/client/license/deactivate',
  clientTransactionsUrl = '/api/client/transactions',
  adminClientsUrl = '/api/admin/clients',
  adminAffiliatesUrl = '/api/admin/affiliates',
  adminCommissionsUrl = '/api/admin/commissions',
  adminApplicationsUrl = '/api/admin/applications',
  adminApplicationLogoUploadUrl = '/api/admin/applications/upload-logo',
  adminLicensesUrl = '/api/admin/licenses',
  adminTransactionsUrl = '/api/admin/transactions',
  adminPaymentOrdersUrl = '/api/admin/payment-orders',
  adminLandingContentUrl = '/api/admin/landing-content',
  adminLandingImageUploadUrl = '/api/admin/landing-content/upload-image',
  adminHomepageSettingsUrl = '/api/admin/homepage-settings',
  adminHubContentUrl = '/api/admin/hub-content',
  adminMailSettingsUrl = '/api/admin/mail-settings',
  adminMailSettingsTestUrl = '/api/admin/mail-settings/test',
  application: landingApplication = null,
  initialClient: bootInitialClient = null,
  initialClientLicense: bootInitialClientLicense = null,
  googleAuthRedirectUrl = '/auth/google/redirect',
  googleLoginEnabled = false,
  postOAuthIntent: bootPostOAuthIntent = null,
  flashAuthError: bootFlashAuthError = '',
  flashAuthSuccess: bootFlashAuthSuccess = '',
} = window.SOFTSALE_DATA || {};

let appDialogResolver = null;

const APP_ROUTE_KEY = 'softsale_route';
const DEMO_PORTAL_KEY = 'softsale_demo_portal';
const LANDING_PENDING_CHECKOUT_KEY = 'softsale_pending_checkout_package';
const LANDING_INTENT_KEY = 'softsale_landing_intent';
const LANDING_REFERRAL_KEY = 'softsale_referral_code';
let landingCheckoutPollTimer = null;
let landingActiveCheckoutOrderId = null;
let adminClientsCache = [];
let adminUsersCache = [];
let adminSession = null;
let adminAffiliatesCache = [];
let adminCommissionsCache = [];
let clientSession = null;
let adminClientModalMode = 'client';
let adminClientUserRoleOriginal = '';
let clientBillingPendingCache = [];
let landingCommercePlansPromise = null;
let clientAffiliatePage = 1;
const CLIENT_AFFILIATE_PER_PAGE = 10;
let adminLicensesApplicationFilter = '';
let adminLicensesSearchQuery = '';
let adminLicensesSearchTimer = null;
let adminApplicationsCache = [];
let adminLicensesCache = [];
let adminTransactionsCache = [];
let adminPaymentOrdersCache = [];
let adminPaymentOrdersSearchTimer = null;
let adminTransactionsActiveTab = 'ledger';
let adminSettingsActiveTab = 'email';
let adminLandingCmsCache = {};
let adminLandingCmsApplicationId = null;
let adminHomepageSettingsCache = {};
let adminHubContentCache = {};
let adminApplicationsListCache = [];
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
function landingPlansList() {
  return window.SOFTSALE_DATA?.plans ?? PLANS ?? [];
}

function landingPlansHaveServerCtas() {
  return landingPlansList().some((p) => typeof p.cta_action === 'string');
}

async function readJsonResponse(response) {
  const text = await response.text();
  if (!text) return { ok: response.ok, data: {} };
  try {
    return { ok: response.ok, data: JSON.parse(text) };
  } catch {
    return { ok: false, data: { message: 'Respons server tidak valid.' }, parseError: true };
  }
}

function isClientLoggedInOnLanding() {
  return !!(clientSession || bootInitialClient);
}

function landingApplicationSlug() {
  return landingApplication?.slug || 'spot-color';
}

function captureLandingReferralFromUrl() {
  try {
    const ref = new URLSearchParams(window.location.search).get('ref');
    if (ref) sessionStorage.setItem(LANDING_REFERRAL_KEY, ref.trim());
  } catch {
    // ignore
  }
}

function landingReferralCode() {
  try {
    return sessionStorage.getItem(LANDING_REFERRAL_KEY) || '';
  } catch {
    return '';
  }
}

function landingPlanCtaAction(plan) {
  if (plan?.cta_action) return plan.cta_action;
  if (isClientLoggedInOnLanding()) {
    if (plan?.is_trial || (plan?.price_amount ?? 0) <= 0) return 'check_license';
    return 'checkout';
  }
  if (plan?.is_trial || (plan?.price_amount ?? 0) <= 0) return 'trial';
  return 'checkout';
}

function landingPlanButtonLabel(plan) {
  if (plan?.cta_label) return plan.cta_label;
  if (isClientLoggedInOnLanding()) {
    if (plan?.is_trial || (plan?.price_amount ?? 0) <= 0) return 'Lihat Lisensi';
    return `Beli ${plan.name || ''}`.trim();
  }
  if (plan?.is_trial || (plan?.price_amount ?? 0) <= 0) return 'Coba Gratis';
  return `Beli ${plan.name || ''}`.trim();
}

function setLandingIntent(type, packageId) {
  try {
    sessionStorage.setItem(
      LANDING_INTENT_KEY,
      JSON.stringify({ type, package_id: packageId ? Number(packageId) : 0 }),
    );
    sessionStorage.removeItem(LANDING_PENDING_CHECKOUT_KEY);
  } catch {
    // ignore
  }
}

function consumeLandingIntent() {
  try {
    const legacy = sessionStorage.getItem(LANDING_PENDING_CHECKOUT_KEY);
    if (legacy) {
      sessionStorage.removeItem(LANDING_PENDING_CHECKOUT_KEY);
      return { type: 'checkout', package_id: Number(legacy) };
    }
    const raw = sessionStorage.getItem(LANDING_INTENT_KEY);
    if (!raw) return null;
    sessionStorage.removeItem(LANDING_INTENT_KEY);
    const parsed = JSON.parse(raw);
    if (!parsed || !parsed.type) return null;
    return parsed;
  } catch {
    return null;
  }
}

function goToClientLicense() {
  window.location.href = `${clientDashboardUrl}#/license`;
}

async function refreshLandingCommercePlans(options = {}) {
  const { force = false } = options;
  if (!isClientLoggedInOnLanding() || !document.getElementById('pricingGrid')) return;

  if (!force && landingPlansHaveServerCtas()) return;

  if (landingCommercePlansPromise) {
    await landingCommercePlansPromise;
    return;
  }

  landingCommercePlansPromise = (async () => {
    try {
      const url = `${clientCommercePlansUrl}?application_slug=${encodeURIComponent(landingApplicationSlug())}`;
      const response = await fetch(url, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
      });
      if (!response.ok) return;
      const { data } = await readJsonResponse(response);
      if (!Array.isArray(data.plans)) return;
      window.SOFTSALE_DATA = window.SOFTSALE_DATA || {};
      window.SOFTSALE_DATA.plans = data.plans;
      renderPricing();
    } catch {
      // ignore
    } finally {
      landingCommercePlansPromise = null;
    }
  })();

  await landingCommercePlansPromise;
}

async function activateClientTrial() {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  try {
    const response = await fetch(clientTrialActivateUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
      body: JSON.stringify({ application_slug: landingApplicationSlug() }),
    });
    const data = await response.json();
    if (!response.ok) {
      showAuthAlert(data.message || 'Trial gagal.', 'error');
      return false;
    }
    if (data.payment) {
      closeModal();
      renderCheckoutPayment(data.payment, data.message);
      return true;
    }
    await refreshLandingCommercePlans();
    closeModal();
    showAuthAlert(data.message || 'Trial berhasil diaktifkan.', 'success');
    return true;
  } catch {
    showAuthAlert('Gagal mengaktifkan trial. Periksa koneksi lalu coba lagi.', 'error');
    return false;
  }
}

async function resumeLandingIntentAfterAuth() {
  const intent = consumeLandingIntent();
  if (!intent || !isClientLoggedInOnLanding()) return false;

  if (intent.type === 'trial') {
    await activateClientTrial();
    return true;
  }
  if (intent.type === 'checkout' && Number(intent.package_id) > 0) {
    closeModal();
    await startClientCheckout(Number(intent.package_id));
    return true;
  }
  return false;
}

function renderPricing() {
  const grid = document.getElementById('pricingGrid');
  if (!grid) return;
  const items = landingPlansList();
  grid.innerHTML = items.map((p, i) => {
    const packageId = Number(p.package_id ?? 0);
    const onclick = packageId
      ? `handleLandingPlanSelect(${packageId})`
      : "handleLandingPlanSelect(0)";
    const action = landingPlanCtaAction(p);
    const enabled = p.cta_enabled !== false && action !== 'none';
    return `
    <div class="price-card ${p.popular ? 'popular' : ''}">
      <h3>${escapeHtml(p.name || '')}</h3>
      <div class="price-amount-wrap">
        <span class="price-amount">${escapeHtml(p.price || '')}</span>
        <span class="price-period">${escapeHtml(p.period || '')}</span>
      </div>
      <div class="price-features">
        ${(p.features || []).map((f) => `<div class="price-feat">${checkSVG}<span>${escapeHtml(f)}</span></div>`).join('')}
      </div>
      <button type="button" class="btn ${p.popular ? 'btn-primary' : 'btn-secondary'} btn-full landing-plan-cta" data-package-id="${packageId}" onclick="${onclick}"${enabled ? '' : ' disabled'}>
        ${escapeHtml(landingPlanButtonLabel(p))}
      </button>
    </div>`;
  }).join('');
  syncLandingHeroPrimaryLabel();
}

function syncLandingHeroPrimaryLabel() {
  const btn = document.querySelector('.hero-actions .btn-primary.btn-lg');
  if (!btn) return;
  if (!btn.dataset.heroCtaDefault) {
    btn.dataset.heroCtaDefault = btn.textContent.trim();
  }
  if (!isClientLoggedInOnLanding()) {
    btn.textContent = btn.dataset.heroCtaDefault;
    return;
  }
  const trialPlan = landingPlansList().find(
    (p) => p.is_trial || (p?.price_amount ?? 0) <= 0,
  );
  if (trialPlan) {
    btn.textContent = landingPlanButtonLabel(trialPlan);
  }
}

function handleLandingHeroCta() {
  if (!document.getElementById('pricingGrid')) {
    openModal('register');
    return;
  }
  const trialPlan = landingPlansList().find(
    (p) => p.is_trial || landingPlanCtaAction(p) === 'trial',
  );
  if (isClientLoggedInOnLanding()) {
    if (trialPlan) {
      const trialAction = landingPlanCtaAction(trialPlan);
      if (trialAction === 'trial' && trialPlan.cta_enabled !== false) {
        activateClientTrial();
        return;
      }
      if (trialAction === 'check_license') {
        goToClientLicense();
        return;
      }
    }
    scrollToSection('harga');
    return;
  }
  setLandingIntent('trial', trialPlan ? Number(trialPlan.package_id) : 0);
  openModal('register');
}

function handleLandingPlanAction(plan) {
  if (!plan) return;
  const action = landingPlanCtaAction(plan);
  const packageId = Number(plan.package_id ?? 0);

  if (action === 'none' || plan.cta_enabled === false) return;

  if (action === 'check_license') {
    goToClientLicense();
    return;
  }

  if (!isClientLoggedInOnLanding()) {
    if (action === 'checkout') {
      setLandingIntent('checkout', packageId);
      openModal('login');
      return;
    }
    if (action === 'trial') {
      setLandingIntent('trial', packageId);
      openModal('register');
      return;
    }
    if (action === 'register') {
      openModal('register');
      return;
    }
    openModal('register');
    return;
  }

  if (action === 'trial') {
    if (plan.cta_enabled === false || action === 'none') return;
    activateClientTrial();
    return;
  }
  if (action === 'checkout' && packageId > 0) {
    startClientCheckout(packageId);
  }
}

function handleLandingPlanSelect(packageId) {
  const id = Number(packageId);
  const plan = landingPlansList().find((p) => Number(p.package_id) === id);

  if (!id || !plan) {
    if (isClientLoggedInOnLanding()) {
      scrollToSection('harga');
      return;
    }
    openModal('register');
    return;
  }

  handleLandingPlanAction(plan);
}

function showCheckoutAlert(message, type = 'warning') {
  const alert = document.getElementById('checkoutAlert');
  if (!alert) return;
  alert.style.display = 'block';
  alert.className = `auth-alert auth-alert-${type}`;
  alert.textContent = message;
}

function clearCheckoutAlert() {
  const alert = document.getElementById('checkoutAlert');
  if (!alert) return;
  alert.style.display = 'none';
  alert.textContent = '';
}

function closeCheckoutModal() {
  if (landingCheckoutPollTimer) {
    clearInterval(landingCheckoutPollTimer);
    landingCheckoutPollTimer = null;
  }
  landingActiveCheckoutOrderId = null;
  const modal = document.getElementById('checkoutModal');
  if (modal) {
    modal.classList.remove('open');
    modal.setAttribute('hidden', '');
    modal.setAttribute('aria-hidden', 'true');
  }
  if (!document.getElementById('authModal')?.classList.contains('open')) {
    document.body.style.overflow = '';
  }
}

function openCheckoutModal() {
  const modal = document.getElementById('checkoutModal');
  if (!modal) return;
  modal.removeAttribute('hidden');
  modal.setAttribute('aria-hidden', 'false');
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
  clearCheckoutAlert();
  document.getElementById('checkoutPaidSuccess')?.setAttribute('hidden', '');
  document.getElementById('checkoutPaymentActions')?.removeAttribute('hidden');
}

function renderCheckoutPayment(payment, message) {
  openCheckoutModal();
  const subtitle = document.getElementById('checkoutModalSubtitle');
  if (subtitle) subtitle.textContent = message || 'Selesaikan pembayaran QRIS untuk mengaktifkan paket.';

  const pkgName = document.getElementById('checkoutPackageName');
  const amount = document.getElementById('checkoutAmountLabel');
  const expires = document.getElementById('checkoutExpiresLabel');
  if (pkgName) pkgName.textContent = payment?.package?.name || '—';
  if (amount) amount.textContent = payment?.amount_label || '—';
  if (expires) expires.textContent = payment?.expires_at_label || '—';

  const link = document.getElementById('checkoutPaymentLink');
  if (link) {
    const url = payment?.payment_page_url;
    if (url) {
      link.href = url;
      link.removeAttribute('hidden');
    } else {
      link.setAttribute('hidden', '');
    }
  }

  const simBtn = document.getElementById('checkoutSimulateBtn');
  if (simBtn) {
    if (payment?.simulate_available) {
      simBtn.removeAttribute('hidden');
    } else {
      simBtn.setAttribute('hidden', '');
    }
  }

  landingActiveCheckoutOrderId = payment?.order_id || null;
  if (landingActiveCheckoutOrderId) {
    pollClientCheckoutStatus(landingActiveCheckoutOrderId);
  }
}

function pollClientCheckoutStatus(orderId) {
  if (landingCheckoutPollTimer) clearInterval(landingCheckoutPollTimer);
  landingCheckoutPollTimer = setInterval(() => {
    refreshClientCheckoutStatus(orderId, { quiet: true });
  }, 4000);
}

async function refreshClientCheckoutStatus(orderId, options = {}) {
  const { quiet = false } = options;
  try {
    const response = await fetch(`${clientCheckoutStatusUrl}/${encodeURIComponent(orderId)}`, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();
    const payment = data.payment;
    if (payment?.status === 'paid') {
      if (landingCheckoutPollTimer) {
        clearInterval(landingCheckoutPollTimer);
        landingCheckoutPollTimer = null;
      }
      document.getElementById('checkoutPaymentActions')?.setAttribute('hidden', '');
      document.getElementById('checkoutPaidSuccess')?.removeAttribute('hidden');
      if (!quiet) showCheckoutAlert('Pembayaran berhasil.', 'success');
    }
  } catch {
    if (!quiet) showCheckoutAlert('Gagal memeriksa status pembayaran.', 'error');
  }
}

async function simulateClientCheckout() {
  if (!landingActiveCheckoutOrderId) return;
  const btn = document.getElementById('checkoutSimulateBtn');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Memproses…';
  }
  try {
    const response = await fetch(`${clientCheckoutSimulateUrl}/${encodeURIComponent(landingActiveCheckoutOrderId)}/simulate`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
    });
    const data = await response.json();
    if (!response.ok) {
      showCheckoutAlert(data.message || 'Simulasi gagal.', 'error');
      return;
    }
    await refreshClientCheckoutStatus(landingActiveCheckoutOrderId);
  } catch {
    showCheckoutAlert('Gagal simulasi pembayaran.', 'error');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Simulasikan pembayaran (sandbox)';
    }
  }
}

async function startClientCheckout(packageId) {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  clearCheckoutAlert();
  openCheckoutModal();
  const subtitle = document.getElementById('checkoutModalSubtitle');
  if (subtitle) subtitle.textContent = 'Menyiapkan pembayaran…';

  try {
    const response = await fetch(clientCheckoutUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        package_id: packageId,
        application_slug: landingApplicationSlug(),
        referral_code: landingReferralCode() || undefined,
      }),
    });
    const { ok, data } = await readJsonResponse(response);
    if (!ok) {
      showCheckoutAlert(data.message || 'Checkout gagal.', 'error');
      const subtitle = document.getElementById('checkoutModalSubtitle');
      if (subtitle) subtitle.textContent = 'Pembayaran tidak dapat dibuat';
      return;
    }
    renderCheckoutPayment(data.payment, data.message);
  } catch {
    showCheckoutAlert('Gagal membuat checkout. Periksa koneksi lalu coba lagi.', 'error');
  }
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
const sectionIds = ['cara-kerja', 'harga', 'faq'];

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
function initGoogleAuthUi() {
  const wrap = document.getElementById('authGoogleWrap');
  if (wrap) wrap.hidden = !googleLoginEnabled;
}

function startGoogleAuth() {
  if (!googleLoginEnabled || !googleAuthRedirectUrl) return;
  const params = new URLSearchParams();
  params.set('return', `${window.location.pathname}${window.location.search}`);
  try {
    const raw = sessionStorage.getItem(LANDING_INTENT_KEY);
    if (raw) {
      const intent = JSON.parse(raw);
      if (intent?.type) params.set('intent', intent.type);
      if (intent?.package_id) params.set('package_id', String(intent.package_id));
    }
  } catch {
    // ignore
  }
  window.location.href = `${googleAuthRedirectUrl}?${params.toString()}`;
}

function openModal(tab = 'login') {
  const modal = document.getElementById('authModal');
  if (!modal) return;
  modal.removeAttribute('hidden');
  modal.setAttribute('aria-hidden', 'false');
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
  clearAuthAlert();
  switchTab(tab);
}
function closeModal() {
  const modal = document.getElementById('authModal');
  if (!modal) return;
  modal.classList.remove('open');
  modal.setAttribute('hidden', '');
  modal.setAttribute('aria-hidden', 'true');
  if (!document.getElementById('checkoutModal')?.classList.contains('open')
    && !document.getElementById('appDialogModal')?.classList.contains('open')) {
    document.body.style.overflow = '';
  }
}
function switchTab(tab) {
  document.getElementById('tabLogin').classList.toggle('active', tab === 'login');
  document.getElementById('tabRegister').classList.toggle('active', tab === 'register');
  document.getElementById('loginForm').style.display = tab === 'login' ? 'block' : 'none';
  document.getElementById('registerForm').style.display = tab === 'register' ? 'block' : 'none';
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

function markClientDashboardReady() {
  document.getElementById('dashClient')?.classList.add('is-ready');
}

function isPublicMarketingMode() {
  return appMode === 'landing' || appMode === 'hub';
}

function goToClientDashboard() {
  window.location.href = clientDashboardUrl;
}

function updatePublicAuthUI(client) {
  const loggedIn = !!client;

  document.querySelectorAll('[data-auth-guest]').forEach((el) => {
    el.hidden = loggedIn;
  });
  document.querySelectorAll('[data-auth-user]').forEach((el) => {
    el.hidden = !loggedIn;
  });

  if (document.getElementById('pricingGrid')) {
    renderPricing();
  }
}

function updateClientDashboard(client) {
  if (!client) return;

  clientSession = client;
  markClientDashboardReady();
  if (isPublicMarketingMode()) {
    updatePublicAuthUI(client);
  }

  const headerTitle = document.getElementById('clientOverviewTitle')
    || document.getElementById('client-overview')?.querySelector('.dash-header h1');
  const displayName = client.name || client.company_name || 'Client';
  if (headerTitle) headerTitle.textContent = `Selamat Datang, ${displayName}`;

  const nameInput = document.getElementById('clientSettingsName');
  const emailInput = document.getElementById('clientSettingsEmail');
  const waInput = document.getElementById('clientSettingsWhatsapp');
  if (nameInput) nameInput.value = displayName;
  if (emailInput) emailInput.value = client.email;
  if (waInput) waInput.value = client.whatsapp;

  if (client.affiliate?.active) {
    const refEl = document.getElementById('clientRefLinkText');
    if (refEl && client.affiliate.referral_url) refEl.textContent = client.affiliate.referral_url;
  }

  if (client.can_enroll_affiliate && document.getElementById('client-affiliate-overview')?.classList.contains('active')) {
    setClientAffiliateOverviewMode('enroll');
  }
}

function setClientAffiliateOverviewMode(mode) {
  const enroll = document.getElementById('clientAffiliateEnrollCard');
  const dashboard = document.getElementById('clientAffiliateDashboard');
  const subtitle = document.getElementById('clientAffiliateOverviewSubtitle');
  if (enroll) enroll.hidden = mode !== 'enroll';
  if (dashboard) dashboard.hidden = mode !== 'dashboard';
  if (subtitle) {
    subtitle.textContent = mode === 'enroll'
      ? 'Aktifkan untuk mendapatkan link referral'
      : 'Pantau performa referral Anda';
  }
}

function copyClientRefLink() {
  const text = document.getElementById('clientRefLinkText')?.textContent?.trim();
  if (!text || text === '—') return;
  navigator.clipboard.writeText(text).then(() => {
    const btn = document.getElementById('clientCopyBtnText');
    if (btn) {
      btn.textContent = 'Tersalin!';
      setTimeout(() => { btn.textContent = 'Salin'; }, 2000);
    }
  }).catch(() => {});
}

function affiliateStatusBadge(status) {
  const map = {
    paid: 'badge-paid',
    pending: 'badge-pending',
    active: 'badge-active',
  };
  return map[status] || 'badge-pending';
}

function renderClientAffiliatePagination(pagination) {
  const wrap = document.getElementById('clientAffiliatePagination');
  if (!wrap) return;

  if (!pagination || pagination.last_page <= 1) {
    wrap.hidden = true;
    wrap.innerHTML = '';
    return;
  }

  wrap.hidden = false;
  const { current_page: current, last_page: last } = pagination;

  let pages = '';
  for (let p = 1; p <= last; p += 1) {
    if (last > 7 && p > 2 && p < last - 1 && Math.abs(p - current) > 1) {
      if (p === 3 || p === last - 2) pages += '<span class="dash-pagination-ellipsis">…</span>';
      continue;
    }
    pages += `<button type="button" class="dash-pagination-btn${p === current ? ' active' : ''}" onclick="loadClientAffiliate(${p})">${p}</button>`;
  }

  wrap.innerHTML = `
    <button type="button" class="dash-pagination-btn" ${current <= 1 ? 'disabled' : ''} onclick="loadClientAffiliate(${current - 1})">Sebelumnya</button>
    <div class="dash-pagination-pages">${pages}</div>
    <button type="button" class="dash-pagination-btn" ${current >= last ? 'disabled' : ''} onclick="loadClientAffiliate(${current + 1})">Berikutnya</button>`;
}

async function loadClientAffiliate(page = clientAffiliatePage) {
  clientAffiliatePage = Math.max(1, page);
  const referralsBody = document.getElementById('clientAffiliateReferralsBody');
  const metaEl = document.getElementById('clientAffiliateTableMeta');
  if (!referralsBody && !document.getElementById('clientAffiliateEnrollCard')) return;

  const loading = '<tr><td colspan="8" style="text-align:center;color:var(--text-3);padding:24px;">Memuat…</td></tr>';
  if (referralsBody) referralsBody.innerHTML = loading;
  if (metaEl) metaEl.textContent = '';

  try {
    const url = `${clientAffiliateUrl}?page=${clientAffiliatePage}&per_page=${CLIENT_AFFILIATE_PER_PAGE}`;
    const response = await fetch(url, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (!response.ok) {
      const msg = data.message || 'Gagal memuat data affiliate.';
      setClientAffiliateOverviewMode('enroll');
      if (referralsBody) referralsBody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:var(--text-3);padding:24px;">${escapeHtml(msg)}</td></tr>`;
      renderClientAffiliatePagination(null);
      return;
    }

    if (!data.enabled) {
      setClientAffiliateOverviewMode('enroll');
      const errEl = document.getElementById('clientAffiliateEnrollError');
      if (errEl) errEl.hidden = true;
      renderClientAffiliatePagination(null);
      return;
    }

    setClientAffiliateOverviewMode('dashboard');

    const summary = data.summary || {};
    const salesEl = document.getElementById('clientAffiliateStatSales');
    const totalEl = document.getElementById('clientAffiliateStatTotal');
    const pendingEl = document.getElementById('clientAffiliateStatPending');
    if (salesEl) salesEl.textContent = String(summary.sales_count ?? 0);
    if (totalEl) totalEl.textContent = summary.total_commission_label || '—';
    if (pendingEl) pendingEl.textContent = summary.pending_commission_label || '—';

    const refEl = document.getElementById('clientRefLinkText');
    if (refEl && summary.referral_url) refEl.textContent = summary.referral_url;

    const rows = (data.referrals || []).map((row) => `
      <tr>
        <td class="td-bold">${escapeHtml(row.customer_name || '—')}</td>
        <td>${escapeHtml(row.customer_email || '—')}</td>
        <td>${escapeHtml(row.application_name || '—')}</td>
        <td>${escapeHtml(row.package_name || '—')}</td>
        <td>${escapeHtml(row.amount_label || '—')}</td>
        <td class="${row.status === 'paid' ? 'td-green' : 'td-amber'}">${escapeHtml(row.commission_label || '—')}</td>
        <td>${escapeHtml(row.date || '—')}</td>
        <td><span class="badge ${affiliateStatusBadge(row.status)}">${escapeHtml(row.status_label || row.status || '—')}</span></td>
      </tr>`).join('');

    const empty = '<tr><td colspan="8" style="text-align:center;color:var(--text-3);padding:24px;">Belum ada referral.</td></tr>';
    if (referralsBody) referralsBody.innerHTML = rows || empty;

    const p = data.pagination;
    if (metaEl && p?.total) {
      metaEl.textContent = `Menampilkan ${p.from ?? 0}–${p.to ?? 0} dari ${p.total} referral`;
    }
    renderClientAffiliatePagination(p);
  } catch {
    setClientAffiliateOverviewMode('enroll');
    if (referralsBody) referralsBody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-3);padding:24px;">Gagal memuat.</td></tr>';
    renderClientAffiliatePagination(null);
  }
}

async function enrollClientAffiliate() {
  const btn = document.getElementById('clientAffiliateEnrollBtn');
  const errEl = document.getElementById('clientAffiliateEnrollError');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Mengaktifkan…';
  }
  if (errEl) errEl.hidden = true;

  try {
    const response = await fetch(clientAffiliateEnrollUrl, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (!response.ok) {
      if (errEl) {
        errEl.hidden = false;
        errEl.textContent = data.message || 'Gagal mengaktifkan affiliate.';
      }
      return;
    }

    await restoreClientSession();
    clientAffiliatePage = 1;
    await loadClientAffiliate(1);
  } catch {
    if (errEl) {
      errEl.hidden = false;
      errEl.textContent = 'Tidak dapat menghubungi server.';
    }
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Aktifkan sekarang';
    }
  }
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

function clientPurchaseStatusBadge(status) {
  const map = {
    active: ['Aktif', 'badge-active'],
    trial: ['Trial', 'badge-trial'],
    expired: ['Expired', 'badge-expired'],
    inactive: ['Nonaktif', 'badge-expired'],
  };
  return map[status] || [status || '—', 'badge-pending'];
}

function clientLicenseIsExpired(expiresAt) {
  const days = clientDaysRemaining(expiresAt);
  if (days === '—') return false;
  return Number(days) <= 0;
}

function clientLicenseStatusForDisplay(app) {
  if (app?.is_expired || clientLicenseIsExpired(app?.expires_at)) {
    return 'expired';
  }
  return app?.license_status || app?.purchase_status || '';
}

function formatClientDateOnly(iso) {
  if (!iso) return '—';
  try {
    return new Date(iso).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    });
  } catch {
    return '—';
  }
}

function clientLicenseDaysLabel(expiresAt) {
  const days = clientDaysRemaining(expiresAt);
  if (days === '—') return '—';
  const n = Number(days);
  if (n <= 0) return 'Sudah kedaluwarsa';
  return `${n} hari lagi`;
}

function renderClientLicenseDeviceRow(appName, device) {
  const ip = escapeHtml(device.ip || '—');
  const host = escapeHtml(device.hostname || device.computer || '—');
  const platform = escapeHtml(device.platform || '—');
  const location = escapeHtml(device.location || '—');
  const appLabel = escapeHtml(appName || '—');

  return `<tr>
    <td class="td-bold">${host}</td>
    <td class="td-mono">${ip}</td>
    <td>${platform}</td>
    <td>${location}</td>
    <td>${formatLicenseDate(device.activated_at)}</td>
    <td>
      <button type="button" class="btn btn-secondary client-license-deactivate" data-ip="${ip}" data-host="${host}" onclick="deactivateClientDevice(${Number(device.id)}, this)">Lepas perangkat</button>
    </td>
  </tr>`;
}

function renderClientLicenseAppCard(app) {
  const [statusLabel, badgeClass] = clientPurchaseStatusBadge(clientLicenseStatusForDisplay(app));
  const packageName = escapeHtml(app.package?.name || '—');
  const appName = escapeHtml(app.name || '—');
  const maxDevices = Number(app.devices_max ?? app.package?.devices_quota ?? 0);
  const activeDevices = Number(
    app.devices_active ?? (app.devices ? app.devices.length : 0),
  );
  const quotaLabel = maxDevices
    ? `${activeDevices} / ${maxDevices} aktif`
    : `${activeDevices} aktif`;
  const durationDays = app.package?.duration_days
    ? `${app.package.duration_days} hari`
    : '—';

  const devices = app.devices || [];
  const deviceBody = devices.length
    ? devices.map((device) => renderClientLicenseDeviceRow(app.name, device)).join('')
    : `<tr><td colspan="6" style="text-align:center;color:var(--text-3);padding:20px;">Belum ada perangkat aktif. Aktivasi dari aplikasi desktop: Settings → Lisensi → Activate.</td></tr>`;

  return `<article class="client-license-app-card">
    <header class="client-license-app-head">
      <div>
        <div class="client-license-app-title-row">
          <h2 class="client-license-app-title">${appName}</h2>
          <span class="client-license-app-package">${packageName}</span>
        </div>
      </div>
      <span class="badge ${badgeClass}">${escapeHtml(statusLabel)}</span>
    </header>
    <div class="client-license-app-meta">
      <div class="client-license-meta-item">
        <div class="client-license-meta-label">Dibeli / mulai</div>
        <div class="client-license-meta-value">${formatClientDateOnly(app.purchased_at)}</div>
      </div>
      <div class="client-license-meta-item">
        <div class="client-license-meta-label">Berlaku hingga</div>
        <div class="client-license-meta-value">${formatClientDateOnly(app.expires_at)}</div>
      </div>
      <div class="client-license-meta-item">
        <div class="client-license-meta-label">Durasi paket</div>
        <div class="client-license-meta-value">${escapeHtml(durationDays)}</div>
      </div>
      <div class="client-license-meta-item">
        <div class="client-license-meta-label">Sisa masa aktif</div>
        <div class="client-license-meta-value">${escapeHtml(clientLicenseDaysLabel(app.expires_at))}</div>
      </div>
      <div class="client-license-meta-item">
        <div class="client-license-meta-label">Kuota perangkat</div>
        <div class="client-license-meta-value">${escapeHtml(quotaLabel)}</div>
      </div>
    </div>
    <section class="client-license-devices">
      <div class="client-license-devices-head">Perangkat teraktivasi</div>
      <div class="dash-table-wrap">
        <table class="dash-table">
          <thead>
            <tr>
              <th>Komputer</th>
              <th>IP</th>
              <th>Platform</th>
              <th>Lokasi</th>
              <th>Diaktifkan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>${deviceBody}</tbody>
        </table>
      </div>
    </section>
  </article>`;
}

function renderClientLicenseApplications(applications) {
  const container = document.getElementById('clientLicenseApps');
  if (!container) return;

  if (!applications.length) {
    container.innerHTML = `<div class="client-license-empty dash-form-card">
      Belum ada aplikasi yang ditetapkan ke akun Anda. Hubungi admin atau selesaikan pembelian dari halaman harga.
    </div>`;
    return;
  }

  const sorted = [...applications].sort((a, b) => {
    const aExpired = clientLicenseStatusForDisplay(a) === 'expired' ? 0 : 1;
    const bExpired = clientLicenseStatusForDisplay(b) === 'expired' ? 0 : 1;
    return aExpired - bExpired;
  });

  container.innerHTML = sorted.map((app) => renderClientLicenseAppCard(app)).join('');
}

function clientDaysRemaining(expiresAt) {
  if (!expiresAt) return '—';
  const end = new Date(expiresAt);
  if (Number.isNaN(end.getTime())) return '—';
  const days = Math.ceil((end.getTime() - Date.now()) / 86400000);
  if (days < 0) return '0';
  return String(days);
}

function clientOverviewActiveLicenseCount(applications) {
  return (applications || []).filter((app) => clientLicenseStatusForDisplay(app) !== 'expired').length;
}

function clientOverviewExpiredLicenseCount(applications) {
  return (applications || []).filter((app) => clientLicenseStatusForDisplay(app) === 'expired').length;
}

function clientOverviewAppAvatarHtml(app) {
  const logoUrl = String(app?.logo_url || '').trim();
  const initial = escapeHtml((app?.name || 'A').trim().charAt(0).toUpperCase());
  if (logoUrl) {
    return `<div class="client-overview-app-avatar client-overview-app-avatar--logo"><img src="${escapeHtml(logoUrl)}" alt="" loading="lazy" decoding="async"></div>`;
  }
  return `<div class="client-overview-app-avatar">${initial}</div>`;
}

function renderClientOverviewAppCard(app) {
  const [statusLabel, badgeClass] = clientPurchaseStatusBadge(clientLicenseStatusForDisplay(app));
  const maxDevices = Number(app.devices_max ?? app.package?.devices_quota ?? 0);
  const activeDevices = Number(app.devices_active ?? (app.devices ? app.devices.length : 0));
  const deviceLabel = maxDevices
    ? `${activeDevices} / ${maxDevices} perangkat`
    : `${activeDevices} perangkat`;
  const appName = escapeHtml(app.name || 'Aplikasi');
  const packageName = escapeHtml(app.package?.name || '—');
  const expiresLabel = formatClientDateOnly(app.expires_at);
  const sisaLabel = clientLicenseStatusForDisplay(app) === 'expired'
    ? 'Expired'
    : clientLicenseDaysLabel(app.expires_at);

  return `<article class="client-overview-app-card" role="button" tabindex="0" onclick="switchDashPage('client','license',document.querySelector('#dashClient .dash-nav-item[onclick*=license]'))" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
    <div class="client-overview-app-card-accent" aria-hidden="true"></div>
    <div class="client-overview-app-card-inner">
      <header class="client-overview-app-card-head">
        ${clientOverviewAppAvatarHtml(app)}
        <span class="badge ${badgeClass}">${escapeHtml(statusLabel)}</span>
      </header>
      <h4 class="client-overview-app-name">${appName}</h4>
      <p class="client-overview-app-package">${packageName}</p>
      <ul class="client-overview-app-meta">
        <li><span>Berlaku</span><strong>${expiresLabel}</strong></li>
        <li><span>Sisa</span><strong>${escapeHtml(sisaLabel)}</strong></li>
        <li><span>Perangkat</span><strong>${escapeHtml(deviceLabel)}</strong></li>
      </ul>
    </div>
  </article>`;
}

function renderClientOverviewDashboard(licensePayload) {
  const applications = licensePayload?.applications || [];
  const activeDevices = applications.reduce(
    (sum, app) => sum + Number(app.devices_active ?? (app.devices ? app.devices.length : 0)),
    0,
  );
  const activeLicenses = clientOverviewActiveLicenseCount(applications);
  const expiredLicenses = clientOverviewExpiredLicenseCount(applications);

  const statActive = document.getElementById('clientOverviewStatActive');
  const statActiveHint = document.getElementById('clientOverviewStatActiveHint');
  const statDevices = document.getElementById('clientOverviewStatDevices');
  const statExpired = document.getElementById('clientOverviewStatExpired');
  const appsGrid = document.getElementById('clientOverviewAppsGrid');

  if (statActive) statActive.textContent = applications.length ? String(activeLicenses) : '0';
  if (statActiveHint) {
    if (!applications.length) {
      statActiveHint.textContent = 'Belum ada aplikasi';
    } else if (applications.length > 1) {
      statActiveHint.textContent = `dari ${applications.length} aplikasi`;
    } else {
      statActiveHint.textContent = '';
    }
  }
  if (statDevices) statDevices.textContent = String(activeDevices);
  if (statExpired) statExpired.textContent = applications.length ? String(expiredLicenses) : '0';

  if (appsGrid) {
    if (!applications.length) {
      appsGrid.innerHTML = `<div class="client-overview-empty dash-form-card">Belum ada aplikasi pada akun ini. Hubungi admin atau selesaikan pembelian dari halaman harga.</div>`;
    } else {
      appsGrid.innerHTML = applications.map((app) => renderClientOverviewAppCard(app)).join('');
    }
  }
}

function hydrateClientOverviewFromBoot() {
  if (appMode !== 'client' || !bootInitialClientLicense) return;
  if (!document.getElementById('client-overview')?.classList.contains('active')) return;
  renderClientOverviewDashboard(bootInitialClientLicense);
}

async function loadClientOverview() {
  const overview = document.getElementById('client-overview');
  if (!overview?.classList.contains('active')) return;

  const appsGrid = document.getElementById('clientOverviewAppsGrid');
  const hadCards = !!appsGrid?.querySelector('.client-overview-app-card');
  if (appsGrid && !hadCards) {
    appsGrid.innerHTML = '<div class="client-overview-loading">Memuat…</div>';
  }

  try {
    const response = await fetch(clientLicenseUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    if (!response.ok) {
      if (appsGrid && !hadCards) {
        appsGrid.innerHTML = '<div class="client-overview-empty dash-form-card">Gagal memuat data lisensi. Muat ulang halaman.</div>';
      }
      return;
    }
    const data = await response.json();
    renderClientOverviewDashboard(data.license || {});
  } catch {
    if (appsGrid && !hadCards) {
      appsGrid.innerHTML = '<div class="client-overview-empty dash-form-card">Gagal memuat data lisensi. Periksa koneksi lalu muat ulang.</div>';
    }
  }
}

async function loadClientLicense(options = {}) {
  const { background = false } = options;
  const appsEl = document.getElementById('clientLicenseApps');
  if (!appsEl) return;

  const hadContent = !!appsEl.querySelector('.client-license-app-card, .client-license-empty');

  if (!background) {
    appsEl.innerHTML = '<div class="client-license-loading">Memuat…</div>';
  }

  try {
    const response = await fetch(clientLicenseUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (!response.ok) {
      if (!background) {
        appsEl.innerHTML = '<div class="client-license-empty">Tidak ada data lisensi.</div>';
      }
      return;
    }

    const license = data.license || {};
    const applications = license.applications || [];

    renderClientLicenseApplications(applications);
  } catch {
    if (!background) {
      appsEl.innerHTML = '<div class="client-license-empty">Gagal memuat.</div>';
    } else if (!hadContent) {
      appsEl.innerHTML = '<div class="client-license-empty">Gagal memuat.</div>';
    }
  }
}

function openPendingClientCheckout(order) {
  if (!order?.order_id) return;
  renderCheckoutPayment(
    {
      order_id: order.order_id,
      package: { name: order.package_name || '—' },
      amount_label: order.amount_label,
      expires_at_label: order.expires_at_label,
      payment_page_url: order.payment_page_url,
      simulate_available: order.simulate_available,
    },
    'Selesaikan pembayaran yang masih pending.',
  );
}

function openPendingClientCheckoutByIndex(index) {
  const order = clientBillingPendingCache[Number(index)];
  if (order) openPendingClientCheckout(order);
}

function renderClientBillingPendingOrders(orders) {
  const wrap = document.getElementById('clientBillingPending');
  if (!wrap) return;

  clientBillingPendingCache = orders;

  if (!orders.length) {
    wrap.hidden = true;
    wrap.innerHTML = '';
    return;
  }

  wrap.hidden = false;
  wrap.innerHTML = `
    <div class="dash-form-card client-billing-pending">
      <h3>Pembayaran belum selesai</h3>
      <ul class="client-billing-pending-list">
        ${orders.map((order, index) => `
          <li class="client-billing-pending-item">
            <div>
              <strong>${escapeHtml(order.application_name || 'Aplikasi')} — ${escapeHtml(order.package_name || 'Paket')}</strong>
              <span class="client-billing-pending-meta">${escapeHtml(order.amount_label || '')}${order.expires_at_label ? ` · Kedaluwarsa ${escapeHtml(order.expires_at_label)}` : ''}</span>
            </div>
            <button type="button" class="btn btn-primary btn-sm" onclick="openPendingClientCheckoutByIndex(${index})">Lanjutkan bayar</button>
          </li>
        `).join('')}
      </ul>
    </div>`;
}

async function loadClientBilling() {
  const tbody = document.getElementById('clientBillingTableBody');
  const countEl = document.getElementById('clientBillingCount');
  if (!tbody) return;

  if (countEl) countEl.textContent = 'Memuat…';
  tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-3);padding:28px;">Memuat…</td></tr>';
  renderClientBillingPendingOrders([]);

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

    renderClientBillingPendingOrders(data.pending_orders || []);

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

    modal.removeAttribute('hidden');
    modal.setAttribute('aria-hidden', 'false');
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
  });
}

function closeAppDialog(result) {
  const modal = document.getElementById('appDialogModal');
  if (modal) {
    modal.classList.remove('open');
    modal.setAttribute('hidden', '');
    modal.setAttribute('aria-hidden', 'true');
  }
  if (!document.getElementById('authModal')?.classList.contains('open')
    && !document.getElementById('checkoutModal')?.classList.contains('open')) {
    document.body.style.overflow = '';
  }
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

async function restoreClientSession(options = {}) {
  const redirectToDashboard = options.redirectToDashboard ?? appMode === 'client';

  if (bootInitialClient) {
    updateClientDashboard(bootInitialClient);
  }

  try {
    const response = await fetch(clientMeUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (data.logged_in && data.client) {
      updateClientDashboard(data.client);
      if (redirectToDashboard) {
        showClientDashboard(false);
      }
      return true;
    }
  } catch {
    // Tetap tampilkan landing jika session tidak ada.
  }

  if (isPublicMarketingMode()) {
    updatePublicAuthUI(null);
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
  if (fromHash?.portal === portal) {
    return normalizeAdminPage(fromHash.page);
  }

  const saved = getSavedRoute();
  if (saved?.portal === portal) {
    return normalizeAdminPage(saved.page);
  }

  return 'overview';
}

function normalizeAdminPage(page) {
  if (page === 'clients') return 'users';
  if (page === 'affiliate-referrals') return 'affiliate-overview';
  return page;
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

function dashNavPageFromOnclick(onclick) {
  const match = (onclick || '').match(/switchDashPage\s*\(\s*['"][^'"]+['"]\s*,\s*['"]([^'"]+)['"]/);
  return match ? match[1] : null;
}

function activateSidebarNav(portal, page) {
  const dashMap = { client: 'dashClient', admin: 'dashAdmin', affiliate: 'dashAffiliate' };
  const dash = document.getElementById(dashMap[portal]);
  if (!dash) return;

  dash.querySelectorAll('.dash-nav-item').forEach((btn) => {
    const navPage = dashNavPageFromOnclick(btn.getAttribute('onclick') || '');
    btn.classList.toggle('active', navPage === page);
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

  if (portal === 'client' && page === 'overview') loadClientOverview();
  if (portal === 'client' && page === 'license') loadClientLicense();
  if (portal === 'client' && page === 'billing') loadClientBilling();
  if (portal === 'client' && page === 'affiliate-overview') loadClientAffiliate(clientAffiliatePage);
  if (portal === 'admin' && (page === 'users' || page === 'clients')) refreshAdminUsersTable();
  if (portal === 'admin' && page === 'affiliates') refreshAdminAffiliatesTable();
  if (portal === 'admin' && page === 'commissions') refreshAdminCommissionsTable();
  if (portal === 'admin' && page === 'applications') refreshAdminApplicationsTable();
  if (portal === 'admin' && page === 'licenses') {
    populateAdminLicenseApplicationFilter().then(() => {
      bindAdminLicenseSearchInput();
      refreshAdminLicensesTable();
    });
  }
  if (portal === 'admin' && page === 'transactions') {
    refreshAdminTransactionsTable();
    if (adminTransactionsActiveTab === 'invoices') refreshAdminPaymentOrdersTable();
  }
  if (portal === 'admin' && page === 'overview') renderRevenueBars();
  if (portal === 'admin' && page === 'homepage') refreshAdminHomepage();
  if (portal === 'admin' && page === 'landing-cms') {
    populateAdminLandingApplicationSelect().then(() => refreshAdminLandingCms());
  }
  if (portal === 'admin' && page === 'settings') {
    switchAdminSettingsTab(adminSettingsActiveTab);
    if (adminSettingsActiveTab === 'email') refreshAdminMailSettings();
  }
}

function finishAppBoot() {
  document.documentElement.classList.remove('app-booting');
  const splash = document.getElementById('appBootSplash');
  if (splash) {
    splash.classList.add('is-hidden');
    splash.setAttribute('aria-busy', 'false');
  }
}

async function initAppState() {
  try {
    if (isPublicMarketingMode()) {
      captureLandingReferralFromUrl();
      initGoogleAuthUi();
      if (bootPostOAuthIntent?.type) {
        setLandingIntent(bootPostOAuthIntent.type, Number(bootPostOAuthIntent.package_id) || 0);
      }
      if (bootFlashAuthError) {
        openModal('login');
        showAuthAlert(bootFlashAuthError, 'error');
      } else if (bootFlashAuthSuccess) {
        showAuthAlert(bootFlashAuthSuccess, 'success');
      }
      if (bootInitialClient) {
        updatePublicAuthUI(bootInitialClient);
        clientSession = bootInitialClient;
      }
      const loggedIn = clientSession || (await restoreClientSession({ redirectToDashboard: false }));
      if (loggedIn) {
        await refreshLandingCommercePlans({ force: true });
        await resumeLandingIntentAfterAuth();
      }

      const params = new URLSearchParams(window.location.search);
      const wantsLogin = params.get('login');
      if (wantsLogin && !clientSession) {
        openModal('login');
      }
      if (params.get('verified') === 'already') {
        if (!clientSession) openModal('login');
        showAuthAlert('Email sudah diverifikasi. Silakan login.', 'success');
      }
      return;
    }

    if (appMode === 'client') {
      hydrateClientOverviewFromBoot();
      const clientLoggedIn = await restoreClientSession();
      if (!clientLoggedIn) {
        window.location.href = `${landingUrl}?login=client`;
        return;
      }
      applyDashboardRoute('client', resolveDashboardPage('client'));
      return;
    }

    if (appMode === 'affiliate') {
      window.location.replace(`${clientDashboardUrl}#/affiliate-overview`);
      return;
    }

    if (appMode === 'admin') {
      const adminLoggedIn = await restoreAdminSession();
      if (!adminLoggedIn) {
        showAdminLoginGate(true);
        return;
      }
      showAdminLoginGate(false);
      applyDashboardRoute('admin', resolveDashboardPage('admin'));
    }
  } finally {
    finishAppBoot();
  }
}

function showClientDashboard(closeModalOnShow = true) {
  if (closeModalOnShow) closeModal();
  if (isPublicMarketingMode()) {
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
  if (isPublicMarketingMode()) {
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
    closeModal();
    if (isPublicMarketingMode()) {
      await refreshLandingCommercePlans({ force: true });
      const resumed = await resumeLandingIntentAfterAuth();
      if (resumed) return;
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

  const form = document.getElementById('registerForm');
  const btn = document.getElementById('registerBtn');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const registerName = document.getElementById('registerName')?.value.trim();
  const payload = {
    name: registerName,
    company_name: registerName,
    email: document.getElementById('registerEmail')?.value.trim(),
    whatsapp: document.getElementById('registerWhatsapp')?.value.trim() || '',
    password: document.getElementById('registerPassword')?.value,
  };
  if (landingApplication?.id) {
    payload.application_id = landingApplication.id;
  }

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

    const { ok, data } = await readJsonResponse(response);

    if (!ok) {
      const firstError = data.errors
        ? Object.values(data.errors).flat()[0]
        : (data.message || 'Pendaftaran gagal.');
      showAuthAlert(firstError);
      return;
    }

    form.reset();

    if (data.logged_in && data.client) {
      updateClientDashboard(data.client);
      closeModal();
      if (isPublicMarketingMode()) {
        await refreshLandingCommercePlans({ force: true });
        const resumed = await resumeLandingIntentAfterAuth();
        if (!resumed) {
          showAuthAlert(data.message, 'success');
          if (data.requires_verification) {
            showVerificationResend(data.email || payload.email);
          }
        }
        return;
      }
      showClientDashboard(false);
      applyDashboardRoute('client', 'overview');
      return;
    }

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

  adminSession = null;

  if (appMode === 'admin') {
    window.location.href = adminDashboardUrl;
    return;
  }

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
  const dashMap = { client: 'dashClient', admin: 'dashAdmin', affiliate: 'dashAffiliate' };
  const dash = document.getElementById(dashMap[portal]);

  if (dash) {
    dash.querySelectorAll('.dash-nav-item').forEach((b) => b.classList.remove('active'));
  }

  if (btn) {
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

function adminAffiliateStatusCell(user) {
  if (!user.client_id) return '—';
  if (user.affiliate_enabled) {
    return '<span class="badge badge-active">Aktif</span>';
  }
  return '<span class="badge badge-expired">Nonaktif</span>';
}

function adminUserApplicationsCell(user) {
  const apps = user.applications || [];
  if (!apps.length) return '—';
  const label = apps.length === 1 ? '1 aplikasi' : `${apps.length} aplikasi`;
  return `<button type="button" class="btn btn-secondary" style="padding:2px 10px;font-size:11px;" onclick="showAdminUserApplications(${user.user_id})">${label}</button>`;
}

function parseAdminUserApplicationsFromRow(userId) {
  const row = document.querySelector(`#adminUsersTableBody tr[data-user-id="${userId}"]`);
  if (!row?.dataset?.applicationNames) return [];
  try {
    return JSON.parse(decodeURIComponent(row.dataset.applicationNames));
  } catch {
    return [];
  }
}

async function showAdminUserApplications(userId) {
  const user = adminUsersCache.find((item) => Number(item.user_id) === Number(userId));
  let apps = user?.applications || [];

  if (!apps.length) {
    apps = parseAdminUserApplicationsFromRow(userId);
  }

  const title = user?.name
    ? `Aplikasi — ${user.name}`
    : 'Aplikasi client';

  const message = apps.length
    ? apps.map((name) => `• ${name}`).join('\n')
    : 'Belum ada aplikasi terpasang.';

  await openAppDialog({
    type: 'alert',
    title,
    message,
    okText: 'Tutup',
  });
}

function adminUserRoleBadge(role) {
  const map = {
    super_admin: 'badge-active',
    admin: 'badge-pending',
    affiliate: 'badge-pending',
    client: 'badge-active',
  };
  return map[role] || 'badge-pending';
}

function renderAdminUsersRows(users) {
  const tbody = document.getElementById('adminUsersTableBody');
  const countEl = document.getElementById('adminUsersCount');
  if (!tbody) return;

  adminUsersCache = users;

  if (countEl) {
    countEl.textContent = `${users.length} user terdaftar`;
  }

  if (!users.length) {
    tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:var(--text-3);padding:28px;">Belum ada user terdaftar.</td></tr>';
    return;
  }

  tbody.innerHTML = users.map((user) => {
    const roleBadge = adminUserRoleBadge(user.role);
    const clientStatus = user.status
      ? `<span class="badge ${adminClientStatusBadge(user.status)}">${escapeHtml(adminClientStatusLabel(user.status))}</span>`
      : '—';
    const appsCell = adminUserApplicationsCell(user);
    const appNamesAttr = (user.applications || []).length
      ? ` data-application-names="${encodeURIComponent(JSON.stringify(user.applications))}"`
      : '';
    const editNoClientBtn = !user.client_id && (user.role === 'admin' || user.role === 'super_admin')
      ? `<button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;margin-right:4px;" onclick="openAdminUserEditModal(${user.user_id})">Edit</button>`
      : '';
    const clientActions = user.client_id
      ? `<button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;" onclick="openAdminClientModal(${user.client_id})">Edit</button>`
      : `${editNoClientBtn}<button type="button" class="btn btn-secondary" style="padding:4px 10px;font-size:11px;color:var(--rose);margin-right:4px;" onclick="deleteAdminUser(${user.user_id})">Hapus Akun</button>`;
    const affiliateCell = adminAffiliateStatusCell(user);
    return `<tr data-user-id="${user.user_id}" data-client-id="${user.client_id || ''}"${appNamesAttr}>
      <td class="td-bold">${escapeHtml(user.name || '—')}</td>
      <td>${escapeHtml(user.email || '—')}${user.email_verified ? ' <span class="badge badge-active" style="font-size:10px;padding:2px 6px;margin-left:4px;">Verified</span>' : ''}</td>
      <td><span class="badge ${roleBadge}">${escapeHtml(user.role_label || user.role)}</span></td>
      <td>${appsCell}</td>
      <td>${escapeHtml(user.whatsapp || '—')}</td>
      <td>${clientStatus}</td>
      <td>${affiliateCell}</td>
      <td>${escapeHtml(user.created_at || '—')}</td>
      <td class="admin-users-actions-cell">${clientActions}</td>
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

async function refreshAdminClientsCache() {
  try {
    const response = await fetch(adminClientsUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();
    if (response.ok) {
      adminClientsCache = data.clients || [];
    }
  } catch {
    // ignore
  }
}

async function refreshAdminUsersTable(options = {}) {
  const { force = false, cacheOnly = false } = options;
  const tbody = document.getElementById('adminUsersTableBody');
  if (!tbody) return;

  const hasServerRows = !!tbody.querySelector('tr[data-user-id]');
  if (!force && hasServerRows && !cacheOnly) {
    await refreshAdminUsersTable({ cacheOnly: true });
    return;
  }

  try {
    const response = await fetch(adminUsersUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (!response.ok) {
      return;
    }

    adminUsersCache = data.users || [];
    if (cacheOnly) {
      await refreshAdminClientsCache();
      return;
    }

    renderAdminUsersRows(adminUsersCache);
    await refreshAdminClientsCache();
  } catch {
    // Biarkan data server-side jika request gagal.
  }
}

async function deleteAdminUser(userId) {
  const confirmed = await openAppDialog({
    type: 'confirm',
    title: 'Hapus User',
    message: 'User dan data affiliate terkait akan dihapus permanen. Lanjutkan?',
    okText: 'Hapus',
    danger: true,
  });
  if (!confirmed) return;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  try {
    const response = await fetch(`${adminUsersDestroyUrl}/${userId}`, {
      method: 'DELETE',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
    });
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      await showAppAlert(data.message || Object.values(data.errors || {}).flat()[0] || 'Gagal menghapus user.');
      return;
    }

    await refreshAdminUsersTable({ force: true });
  } catch {
    await showAppAlert('Terjadi kesalahan jaringan.');
  }
}

async function saveAdminUserAffiliate(userId) {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const enabled = !!document.getElementById('adminClientAffiliateEnabled')?.checked;
  const code = document.getElementById('adminClientAffiliateCode')?.value.trim();
  const status = document.getElementById('adminClientAffiliateStatus')?.value || 'active';

  const payload = { enabled, status };
  if (code) payload.code = code;

  const response = await fetch(`${adminUsersAffiliateUrl}/${userId}/affiliate`, {
    method: 'PATCH',
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
    const msg = data.message || Object.values(data.errors || {}).flat()[0] || 'Gagal menyimpan affiliate.';
    throw new Error(msg);
  }

  return data;
}

function syncAdminClientRoleField(userId, isEdit) {
  const group = document.getElementById('adminClientUserRoleGroup');
  const select = document.getElementById('adminClientUserRole');
  if (!group || !select) return;

  const show = adminSession?.is_super_admin === true && isEdit && Boolean(userId);
  group.hidden = !show;

  if (!show) {
    adminClientUserRoleOriginal = '';
    return;
  }

  const user = adminUsersCache.find((item) => Number(item.user_id) === Number(userId));
  const role = user?.role || 'client';
  select.value = role;
  adminClientUserRoleOriginal = role;
}

function setAdminClientModalLayout(mode) {
  adminClientModalMode = mode;
  const modal = document.getElementById('adminClientModal');
  if (!modal) return;

  const isUserOnly = mode === 'user';
  modal.classList.toggle('is-user-only-edit', isUserOnly);

  const waGroup = document.getElementById('adminClientWhatsapp')?.closest('.form-group');
  const passwordGroup = document.getElementById('adminClientPassword')?.closest('.form-group');
  const verifyRow = document.querySelector('.admin-client-email-row');
  const nameInput = document.getElementById('adminClientName');
  const emailInput = document.getElementById('adminClientEmail');

  if (waGroup) waGroup.hidden = isUserOnly;
  if (passwordGroup) passwordGroup.hidden = isUserOnly;
  const verifyLabel = verifyRow?.querySelector('label');
  if (verifyLabel) verifyLabel.hidden = isUserOnly;

  if (nameInput) {
    if (isUserOnly) nameInput.setAttribute('readonly', 'readonly');
    else nameInput.removeAttribute('readonly');
  }
  if (emailInput) {
    if (isUserOnly) emailInput.setAttribute('readonly', 'readonly');
    else emailInput.removeAttribute('readonly');
  }

  const side = modal.querySelector('.admin-app-side');
  if (side) side.hidden = isUserOnly;

  modal.querySelectorAll('.admin-app-nav-item[data-panel="applications"], .admin-app-nav-item[data-panel="affiliate"]').forEach((el) => {
    el.hidden = isUserOnly;
  });
}

function openAdminUserEditModal(userId) {
  const user = adminUsersCache.find((item) => Number(item.user_id) === Number(userId));
  if (!user) return;

  const modal = document.getElementById('adminClientModal');
  const form = document.getElementById('adminClientForm');
  const title = document.getElementById('adminClientModalTitle');
  const idInput = document.getElementById('adminClientId');
  const userIdInput = document.getElementById('adminClientUserId');
  if (!modal || !form) return;

  clearAdminClientAlert();
  form.reset();
  setAdminClientModalLayout('user');
  switchAdminClientPanel('general', modal.querySelector('.admin-app-nav-item[data-panel="general"]'));

  title.textContent = 'Edit User';
  if (idInput) idInput.value = '';
  if (userIdInput) userIdInput.value = user.user_id;

  document.getElementById('adminClientName').value = user.name || '';
  document.getElementById('adminClientEmail').value = user.email || '';
  document.getElementById('adminClientEmailVerified').checked = !!user.email_verified;

  syncAdminClientRoleField(user.user_id, true);
  syncAdminClientAccountZone(false);

  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function fillAdminClientAffiliateFields(userId) {
  const user = adminUsersCache.find((item) => Number(item.user_id) === Number(userId));
  const enabledEl = document.getElementById('adminClientAffiliateEnabled');
  const codeEl = document.getElementById('adminClientAffiliateCode');
  const statusEl = document.getElementById('adminClientAffiliateStatus');
  const hintEl = document.getElementById('adminClientAffiliateLinkHint');
  if (!enabledEl) return;

  const enabled = user?.affiliate_enabled === true;
  enabledEl.checked = enabled;
  if (codeEl) codeEl.value = user?.affiliate_code || '';
  if (statusEl) statusEl.value = user?.affiliate_status || 'active';
  if (hintEl) {
    if (user?.affiliate_referral_url) {
      hintEl.hidden = false;
      hintEl.textContent = `Link: ${user.affiliate_referral_url}`;
    } else {
      hintEl.hidden = true;
      hintEl.textContent = '';
    }
  }
}

async function patchAdminUserRole(userId, role) {
  if (!adminSession?.is_super_admin) {
    return { ok: false, message: 'Hanya super admin yang dapat mengubah role.' };
  }

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  try {
    const response = await fetch(`${adminUsersRoleUrl}/${userId}/role`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
      body: JSON.stringify({ role }),
    });
    const data = await response.json();

    if (!response.ok) {
      const msg = data.errors?.role?.[0] || data.message || 'Gagal mengubah role.';
      return { ok: false, message: msg };
    }

    if (Number(data.user?.user_id) === Number(adminSession?.id)) {
      adminSession = { ...adminSession, role: data.user.role, is_super_admin: data.user.role === 'super_admin' };
    }

    return { ok: true, data };
  } catch {
    return { ok: false, message: 'Gagal mengubah role.' };
  }
}

function showAdminLoginGate(show) {
  const gate = document.getElementById('adminLoginGate');
  const shell = document.getElementById('adminDashboardShell');
  if (gate) gate.hidden = !show;
  if (shell) shell.hidden = show;
}

function showAdminLoginAlert(message, type = 'error') {
  const alert = document.getElementById('adminLoginAlert');
  if (!alert) return;
  alert.style.display = 'block';
  alert.textContent = message;
  if (type === 'success') {
    alert.style.background = 'rgba(16,185,129,0.12)';
    alert.style.border = '1px solid rgba(16,185,129,0.25)';
    alert.style.color = 'var(--green)';
  } else {
    alert.style.background = 'rgba(240,104,104,0.12)';
    alert.style.border = '1px solid rgba(240,104,104,0.25)';
    alert.style.color = 'var(--rose)';
  }
}

function clearAdminLoginAlert() {
  const alert = document.getElementById('adminLoginAlert');
  if (alert) {
    alert.style.display = 'none';
    alert.textContent = '';
  }
}

async function restoreAdminSession() {
  try {
    const response = await fetch(adminMeUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (data.logged_in && data.admin) {
      adminSession = {
        id: data.admin.id,
        name: data.admin.name,
        email: data.admin.email,
        role: data.admin.role,
        is_super_admin: data.admin.is_super_admin,
      };
      return true;
    }
  } catch {
    // ignore
  }

  adminSession = null;
  return false;
}

async function submitAdminLogin(event) {
  event.preventDefault();
  clearAdminLoginAlert();

  const btn = document.getElementById('adminLoginBtn');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  const payload = {
    email: document.getElementById('adminLoginEmail')?.value.trim(),
    password: document.getElementById('adminLoginPassword')?.value,
  };

  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Memproses...';
  }

  try {
    const response = await fetch(loginAdminUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
      body: JSON.stringify(payload),
    });
    const data = await response.json();

    if (!response.ok) {
      showAdminLoginAlert(data.message || 'Login gagal.');
      return;
    }

    adminSession = {
      id: data.admin.id,
      name: data.admin.name,
      email: data.admin.email,
      role: data.admin.role,
      is_super_admin: data.admin.is_super_admin,
    };

    showAdminLoginGate(false);
    applyDashboardRoute('admin', resolveDashboardPage('admin'));
  } catch {
    showAdminLoginAlert('Terjadi kesalahan jaringan.');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Masuk Admin';
    }
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

async function refreshAdminAffiliatesTable(options = {}) {
  const { force = false, cacheOnly = false } = options;
  const tbody = document.getElementById('adminAffiliatesTableBody');
  if (!tbody) return;

  const hasServerRows = !!tbody.querySelector('tr[data-affiliate-id]');
  if (!force && hasServerRows && !cacheOnly) {
    await refreshAdminAffiliatesTable({ cacheOnly: true });
    return;
  }

  try {
    const response = await fetch(adminAffiliatesUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();

    if (!response.ok) {
      return;
    }

    adminAffiliatesCache = data.affiliates || [];
    if (cacheOnly) {
      return;
    }

    renderAdminAffiliatesRows(adminAffiliatesCache);
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
    await refreshAdminAffiliatesTable({ force: true });
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
    await refreshAdminAffiliatesTable({ force: true });
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
    await refreshAdminAffiliatesTable({ force: true });
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
    await refreshAdminAffiliatesTable({ force: true });
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

    await refreshAdminAffiliatesTable({ force: true });
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

    const logoThumb = app.logo_url
      ? `<img class="admin-apps-table-logo" src="${escapeHtml(app.logo_url)}" alt="" width="28" height="28">`
      : '<span class="admin-apps-table-logo admin-apps-table-logo--empty" aria-hidden="true"></span>';

    return `<tr class="admin-app-main-row" data-application-id="${app.id}">
      <td class="td-bold admin-apps-name-cell">${logoThumb}<span>${escapeHtml(app.name || '—')}</span></td>
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
    const emptyMsg = adminLicensesSearchQuery || adminLicensesApplicationFilter
      ? 'Tidak ada lisensi yang cocok dengan filter/pencarian.'
      : 'Belum ada lisensi. Assign aplikasi ke client terlebih dahulu.';
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--text-3);padding:28px;">${emptyMsg}</td></tr>`;
    return;
  }

  tbody.innerHTML = licenses.map((license) => {
    const badge = adminLicenseStatusBadge(license.status);
    const label = license.status ? license.status.charAt(0).toUpperCase() + license.status.slice(1) : '—';
    const statusKey = (license.status || '').toLowerCase();
    const isExpiredLicense = !!license.is_expired || statusKey === 'expired';
    const isActiveLicense = !isExpiredLicense && (statusKey === 'active' || statusKey === 'trial');
    const devicesActive = Number(license.devices_active ?? 0);
    const renewBtn = `<button type="button" class="btn btn-secondary admin-license-action-btn" onclick="renewAdminLicense(${license.id})"${isExpiredLicense ? '' : ' disabled'}>Renew</button>`;
    const revokeBtn = `<button type="button" class="btn btn-secondary admin-license-action-btn admin-license-revoke-btn" onclick="revokeAdminLicense(${license.id})"${isActiveLicense ? '' : ' disabled'}>Revoke</button>`;
    const releaseBtn = `<button type="button" class="btn btn-secondary admin-license-action-btn" onclick="releaseAdminLicenseDevices(${license.id})"${devicesActive > 0 ? '' : ' disabled'} title="${devicesActive > 0 ? `${devicesActive} perangkat terdaftar` : 'Tidak ada perangkat'}">Lepas perangkat</button>`;
    const actionCell = `<div class="admin-license-actions">${renewBtn}${revokeBtn}${releaseBtn}</div>`;

    return `<tr data-license-id="${license.id}">
      <td class="td-mono">${escapeHtml(license.license_key || '—')}</td>
      <td class="td-bold">${escapeHtml(license.client_name || '—')}</td>
      <td>${escapeHtml(license.application_name || '—')}</td>
      <td>${escapeHtml(license.package_name || '—')}${license.devices_max ? ` <span style="color:var(--text-3);font-size:11px;">(${Number(license.devices_max)} pc)</span>` : ''}</td>
      <td><span class="badge ${badge}">${escapeHtml(label)}</span></td>
      <td>${escapeHtml(license.expires_at || '—')}</td>
      <td>${actionCell}</td>
    </tr>`;
  }).join('');
}

async function populateAdminLicenseApplicationFilter() {
  const select = document.getElementById('adminLicenseApplicationFilter');
  if (!select) return;

  if (!adminApplicationsCache.length) {
    await refreshAdminApplicationsTable(true);
  }

  const current = adminLicensesApplicationFilter || select.value || '';
  select.innerHTML = '<option value="">Semua aplikasi</option>'
    + adminApplicationsCache
      .filter((app) => app.is_active !== false)
      .map((app) => `<option value="${app.id}">${escapeHtml(app.name)}</option>`)
      .join('');
  select.value = current;
  adminLicensesApplicationFilter = current;
}

function onAdminLicenseApplicationFilterChange() {
  adminLicensesApplicationFilter = document.getElementById('adminLicenseApplicationFilter')?.value || '';
  refreshAdminLicensesTable();
}

function bindAdminLicenseSearchInput() {
  const input = document.getElementById('adminLicenseSearch');
  if (!input || input.dataset.bound === '1') return;
  input.dataset.bound = '1';
  input.value = adminLicensesSearchQuery;

  input.addEventListener('input', () => {
    clearTimeout(adminLicensesSearchTimer);
    adminLicensesSearchTimer = setTimeout(() => {
      adminLicensesSearchQuery = input.value.trim();
      refreshAdminLicensesTable();
    }, 300);
  });
}

function buildAdminLicensesApiUrl() {
  const params = new URLSearchParams();
  if (adminLicensesApplicationFilter) {
    params.set('application_id', adminLicensesApplicationFilter);
  }
  if (adminLicensesSearchQuery) {
    params.set('q', adminLicensesSearchQuery);
  }
  const qs = params.toString();
  return qs ? `${adminLicensesUrl}?${qs}` : adminLicensesUrl;
}

async function refreshAdminLicensesTable() {
  const tbody = document.getElementById('adminLicensesTableBody');
  if (!tbody) return;

  bindAdminLicenseSearchInput();

  try {
    const response = await fetch(buildAdminLicensesApiUrl(), {
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
    message: `Aktifkan kembali lisensi expired ${licenseKey}? Masa berlaku dihitung ulang dari paket saat ini (tanpa pembayaran client).`,
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
    title: 'Revoke Lisensi',
    message: `Yakin ingin revoke lisensi ${licenseKey}? Masa berlaku berakhir dan semua perangkat untuk aplikasi ini akan dihapus.`,
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
    await showAppAlert(data.message || 'Lisensi berhasil di-revoke.', 'success');
  } catch {
    await showAppAlert('Terjadi kesalahan jaringan. Silakan coba lagi.');
  }
}

async function releaseAdminLicenseDevices(licenseId) {
  const license = adminLicensesCache.find((item) => item.id === licenseId);
  const licenseKey = license?.license_key || `#${licenseId}`;
  const count = Number(license?.devices_active ?? 0);

  if (count <= 0) return;

  const confirmed = await openAppDialog({
    type: 'confirm',
    title: 'Lepas perangkat',
    message: `Lepas ${count} perangkat terdaftar untuk lisensi ${licenseKey}? Status lisensi tidak berubah; client perlu Activate lagi jika lisensi masih aktif.`,
    okText: 'Lepas perangkat',
    danger: true,
  });
  if (!confirmed) return;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  try {
    const response = await fetch(`${adminLicensesUrl}/${licenseId}/release-devices`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
    });
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      await showAppAlert(data.message || 'Gagal melepas perangkat.');
      return;
    }

    await refreshAdminLicensesTable();
    await refreshAdminApplicationsTable(true);
    await showAppAlert(data.message || 'Perangkat berhasil dilepas.', 'success');
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
    return `${formatAdminPackageCommissionPercent(percent)}% (${formatAdminPackagePriceDisplay(amount)})`;
  }

  if (fixed > 0) {
    return formatAdminPackagePriceDisplay(fixed);
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
  if (fixedEl && typeof fixed !== 'undefined') fixedEl.value = formatAdminPackagePriceDisplay(fixed);
  if (percentEl && typeof percent !== 'undefined') percentEl.value = formatAdminPackageCommissionPercent(percent);
  if (source) row.dataset.commissionSource = source;
  delete row.dataset.commissionUpdating;
}

function syncAdminPackageCommissionFromFixed(row) {
  const price = getAdminPackageRowPrice(row);
  const fixed = Math.max(0, parseAdminPackagePriceInput(row.querySelector('.pkg-commission-fixed')?.value || 0));
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

let adminPackageFeaturesTargetRow = null;

function inferAdminPackageFeatures(name) {
  const n = String(name || '').trim().toLowerCase();
  if (n.includes('trial')) return { parallel_max: 1, feature_uv: false };
  if (/\bpro\b/.test(n)) return { parallel_max: 2, feature_uv: false };
  if (n.includes('ultimate') || n.includes('platinum')) return { parallel_max: null, feature_uv: true };
  return { parallel_max: null, feature_uv: true };
}

function readAdminPackageRowFeatures(row) {
  const raw = row?.dataset?.parallelMax;
  return {
    parallel_max: raw === '' || raw === undefined ? null : Number(raw),
    feature_uv: row?.dataset?.featureUv !== '0',
  };
}

function writeAdminPackageRowFeatures(row, { parallel_max, feature_uv }) {
  if (!row) return;
  row.dataset.parallelMax = parallel_max == null || Number.isNaN(parallel_max) ? '' : String(parallel_max);
  row.dataset.featureUv = feature_uv ? '1' : '0';
}

function initAdminPackageRowFeatures(row, pkg = {}) {
  const hasStored =
    Object.prototype.hasOwnProperty.call(pkg, 'parallel_max')
    || Object.prototype.hasOwnProperty.call(pkg, 'feature_uv');
  if (hasStored) {
    writeAdminPackageRowFeatures(row, {
      parallel_max: pkg.parallel_max ?? null,
      feature_uv: pkg.feature_uv !== false,
    });
  } else {
    writeAdminPackageRowFeatures(row, inferAdminPackageFeatures(pkg.name));
  }
}

function bindAdminPackageFeaturesModalControls() {
  const modal = document.getElementById('adminPackageFeaturesModal');
  if (!modal || modal.dataset.bound === '1') return;
  modal.dataset.bound = '1';

  const maxInput = document.getElementById('adminPkgParallelMaxInput');
  const limitedRadio = document.querySelector('input[name="adminPkgParallelMode"][value="limited"]');

  maxInput?.addEventListener('focus', () => {
    if (limitedRadio) limitedRadio.checked = true;
  });

  modal.querySelectorAll('input[name="adminPkgParallelMode"]').forEach((el) => {
    el.addEventListener('change', () => syncAdminPackageFeaturesParallelInputState());
  });
}

function syncAdminPackageFeaturesParallelInputState() {
  const limited = document.querySelector('input[name="adminPkgParallelMode"][value="limited"]')?.checked;
  const maxInput = document.getElementById('adminPkgParallelMaxInput');
  if (maxInput) maxInput.disabled = !limited;
}

function openAdminPackageFeaturesModal(btn) {
  bindAdminPackageFeaturesModalControls();
  const row = btn?.closest('tr.admin-app-package-row');
  if (!row) return;
  adminPackageFeaturesTargetRow = row;
  const modal = document.getElementById('adminPackageFeaturesModal');
  const subtitle = document.getElementById('adminPackageFeaturesSubtitle');
  const name = row.querySelector('.pkg-name')?.value?.trim() || 'Paket';
  if (subtitle) subtitle.textContent = `Batas fitur untuk “${name}” di aplikasi desktop`;

  const { parallel_max, feature_uv } = readAdminPackageRowFeatures(row);
  const unlimited = document.querySelector('input[name="adminPkgParallelMode"][value="unlimited"]');
  const limited = document.querySelector('input[name="adminPkgParallelMode"][value="limited"]');
  const off = document.querySelector('input[name="adminPkgParallelMode"][value="off"]');
  const maxInput = document.getElementById('adminPkgParallelMaxInput');
  const uvEl = document.getElementById('adminPkgFeatureUv');

  if (parallel_max === 1) off.checked = true;
  else if (parallel_max != null && parallel_max > 1) {
    limited.checked = true;
    if (maxInput) maxInput.value = String(parallel_max);
  } else if (unlimited) unlimited.checked = true;

  if (uvEl) uvEl.checked = !!feature_uv;
  syncAdminPackageFeaturesParallelInputState();
  if (modal) modal.hidden = false;
}

function closeAdminPackageFeaturesModal() {
  const modal = document.getElementById('adminPackageFeaturesModal');
  if (modal) modal.hidden = true;
  adminPackageFeaturesTargetRow = null;
}

function saveAdminPackageFeaturesModal() {
  const row = adminPackageFeaturesTargetRow;
  if (!row) {
    closeAdminPackageFeaturesModal();
    return;
  }
  const mode = document.querySelector('input[name="adminPkgParallelMode"]:checked')?.value || 'unlimited';
  const maxInput = document.getElementById('adminPkgParallelMaxInput');
  const uvEl = document.getElementById('adminPkgFeatureUv');
  let parallel_max = null;
  if (mode === 'off') parallel_max = 1;
  else if (mode === 'limited') {
    parallel_max = Math.max(2, Math.min(99, Number(maxInput?.value || 2)));
    if (maxInput) maxInput.value = String(parallel_max);
  }
  writeAdminPackageRowFeatures(row, {
    parallel_max,
    feature_uv: !!uvEl?.checked,
  });
  closeAdminPackageFeaturesModal();
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
    <td class="pkg-days-cell">
      <div class="pkg-days-wrap${isUnlimited ? ' is-unlimited' : ''}">
        <input class="admin-app-input pkg-days" type="number" min="1" max="36500" value="${daysValue}" ${isUnlimited ? 'disabled' : ''}>
        <span class="pkg-unlimited-label" title="Unlimited">∞</span>
        <button type="button" class="pkg-unlimited-btn${isUnlimited ? ' is-on' : ''}" onclick="toggleAdminPackageUnlimited(this)" title="Unlimited (∞)">∞</button>
      </div>
    </td>
    <td class="pkg-price-cell"><input class="admin-app-input pkg-price" type="text" inputmode="numeric" value="${formatAdminPackagePriceDisplay(pkg.price ?? 0)}" required></td>
    <td class="pkg-discount-cell"><input class="admin-app-input pkg-discount" type="number" min="0" max="100" step="0.01" value="${formatAdminPackageCommissionPercent(pkg.discount_percent ?? 0)}" placeholder="0"></td>
    <td class="pkg-commission-fixed-cell"><input class="admin-app-input pkg-commission-fixed" type="text" inputmode="numeric" value="${formatAdminPackagePriceDisplay(pkg.affiliate_commission_fixed ?? 0)}" placeholder="Rp 0"></td>
    <td class="pkg-commission-percent-cell"><input class="admin-app-input pkg-commission-percent" type="number" min="0" max="100" step="0.01" value="${formatAdminPackageCommissionPercent(pkg.affiliate_commission_percent ?? 0)}" placeholder="0"></td>
    <td class="col-pkg-actions-cell">
      <div class="admin-app-pkg-actions">
        <button type="button" class="admin-app-pkg-features" title="Fitur paket" onclick="openAdminPackageFeaturesModal(this)">Fitur</button>
        <button type="button" class="admin-app-pkg-remove" title="Hapus paket" onclick="removeAdminApplicationPackageRow(this)">×</button>
      </div>
    </td>
  `;
  body.appendChild(row);
  bindAdminPackagePriceInput(row.querySelector('.pkg-price'));
  bindAdminPackagePriceInput(row.querySelector('.pkg-commission-fixed'));
  initAdminPackageRowFeatures(row, pkg);
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
      affiliate_commission_fixed: parseAdminPackagePriceInput(row.querySelector('.pkg-commission-fixed')?.value || 0),
      affiliate_commission_percent: Number(row.querySelector('.pkg-commission-percent')?.value || 0),
      sort_order: index,
      ...readAdminPackageRowFeatures(row),
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

function updateAdminApplicationLogoPreview(url) {
  const preview = document.getElementById('adminApplicationLogoPreview');
  const hidden = document.getElementById('adminApplicationLogoUrl');
  const clearBtn = document.getElementById('adminApplicationLogoClearBtn');
  if (!preview || !hidden) return;
  hidden.value = url || '';
  preview.innerHTML = url
    ? `<img src="${escapeHtml(url)}" alt="Logo aplikasi">`
    : '<span class="admin-app-logo-empty">Belum ada logo</span>';
  if (clearBtn) clearBtn.hidden = !url;
}

function clearAdminApplicationLogo() {
  updateAdminApplicationLogoPreview('');
  const fileInput = document.getElementById('adminApplicationLogoFile');
  if (fileInput) fileInput.value = '';
}

async function uploadAdminApplicationLogo(input) {
  const file = input.files?.[0];
  if (!file) return;

  const pick = input.closest('.admin-app-logo-pick');
  const formData = new FormData();
  formData.append('image', file);

  if (pick) {
    pick.style.pointerEvents = 'none';
    pick.style.opacity = '0.7';
  }

  try {
    const response = await fetch(adminApplicationLogoUploadUrl, {
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
      showAdminApplicationAlert(firstError || data.message || 'Gagal mengupload logo.');
      return;
    }
    updateAdminApplicationLogoPreview(data.url || '');
  } catch {
    showAdminApplicationAlert('Gagal mengupload logo.');
  } finally {
    input.value = '';
    if (pick) {
      pick.style.pointerEvents = '';
      pick.style.opacity = '';
    }
  }
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
    updateAdminApplicationLogoPreview(app.logo_url || '');
    renderAdminApplicationPackages(app.packages || []);
  } else {
    title.textContent = 'Tambah Aplikasi';
    idInput.value = '';
    document.getElementById('adminApplicationDevicesMax').value = 3;
    document.getElementById('adminApplicationSortOrder').value = 0;
    document.getElementById('adminApplicationIsActive').checked = true;
    updateAdminApplicationLogoPreview('');
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
    logo_url: document.getElementById('adminApplicationLogoUrl')?.value.trim() || null,
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
  const row = document.querySelector(`#adminUsersTableBody tr[data-client-id="${clientId}"]`);
  if (!row || !row.cells?.length) return null;

  return {
    id: Number(clientId),
    name: row.cells[0].textContent.trim(),
    email: row.cells[1].textContent.trim().replace(/\s*Verified\s*$/, '').trim(),
    whatsapp: row.cells[4].textContent.trim(),
    status: row.cells[5].querySelector('.badge')?.textContent.trim().toLowerCase() || 'active',
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
    let pkgInput = card.querySelector('.admin-client-pkg-option input[type="radio"]:checked');
    if (!pkgInput) {
      pkgInput = card.querySelector('.admin-client-pkg-option input[type="radio"]');
      if (pkgInput) pkgInput.checked = true;
    }
    const packageId = Number(pkgInput?.value);
    if (applicationId > 0 && packageId > 0) {
      items.push({ application_id: applicationId, package_id: packageId });
    }
  });
  return items;
}

function validateAdminClientForm(isEdit) {
  const name = document.getElementById('adminClientName')?.value.trim();
  const email = document.getElementById('adminClientEmail')?.value.trim();
  const whatsapp = document.getElementById('adminClientWhatsapp')?.value.trim();
  const password = document.getElementById('adminClientPassword')?.value;
  const modal = document.getElementById('adminClientModal');

  if (!name) {
    return { valid: false, panel: 'general', message: 'Nama akun wajib diisi.' };
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

function syncAdminClientAccountZone(isEdit, status = 'active') {
  const zone = document.getElementById('adminClientAccountZone');
  const statusInput = document.getElementById('adminClientStatus');
  if (!zone || !statusInput) return;

  zone.hidden = !isEdit || adminClientModalMode === 'user';
  if (!isEdit || adminClientModalMode === 'user') {
    statusInput.value = 'active';
    return;
  }

  const normalized = status === 'inactive' ? 'inactive' : 'active';
  statusInput.value = normalized;

  const badge = document.getElementById('adminClientStatusBadge');
  const toggleBtn = document.getElementById('adminClientToggleStatusBtn');
  if (badge) {
    badge.textContent = adminClientStatusLabel(normalized);
    badge.className = `badge ${adminClientStatusBadge(normalized)}`;
  }
  if (toggleBtn) {
    toggleBtn.textContent = normalized === 'active' ? 'Nonaktifkan akun' : 'Aktifkan akun';
  }
}

function toggleAdminClientAccountStatus() {
  const statusInput = document.getElementById('adminClientStatus');
  if (!statusInput) return;
  const next = statusInput.value === 'inactive' ? 'active' : 'inactive';
  syncAdminClientAccountZone(true, next);
}

async function deleteAdminClientFromModal() {
  const clientId = document.getElementById('adminClientId')?.value;
  if (!clientId) return;
  await deleteAdminClient(clientId);
  closeAdminClientModal();
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

    // Jika client sudah punya aplikasi tapi package_id hilang/tidak valid, pilih paket pertama agar simpan tidak gagal.
    const defaultPackageId = selectedPackageId > 0
      ? selectedPackageId
      : (isChecked && packages.length ? Number(packages[0].id) : 0);
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

async function openAdminClientModal(clientId = null) {
  const modal = document.getElementById('adminClientModal');
  const form = document.getElementById('adminClientForm');
  const title = document.getElementById('adminClientModalTitle');
  const idInput = document.getElementById('adminClientId');
  const passwordInput = document.getElementById('adminClientPassword');
  const passwordHint = document.getElementById('adminClientPasswordHint');
  if (!modal || !form) return;

  clearAdminClientAlert();
  form.reset();
  setAdminClientModalLayout('client');
  modal.querySelectorAll('.admin-app-nav-item.has-error').forEach((el) => el.classList.remove('has-error'));
  switchAdminClientPanel('general', modal.querySelector('.admin-app-nav-item[data-panel="general"]'));

  let client = clientId
    ? adminClientsCache.find((item) => Number(item.id) === Number(clientId))
    : null;

  if (clientId && (!client || !client.applications?.length)) {
    await refreshAdminClientsCache();
    client = adminClientsCache.find((item) => Number(item.id) === Number(clientId)) || client;
  }

  if (clientId && !client) {
    client = getAdminClientFromDom(clientId);
  }

  if (client) {
    title.textContent = 'Edit Client';
    idInput.value = client.id;
    const userIdInput = document.getElementById('adminClientUserId');
    if (userIdInput) userIdInput.value = client.user_id || '';
    document.getElementById('adminClientName').value = client.name || '';
    document.getElementById('adminClientEmail').value = client.email || '';
    document.getElementById('adminClientEmailVerified').checked = !!client.email_verified;
    document.getElementById('adminClientWhatsapp').value = client.whatsapp || '';
    syncAdminClientAccountZone(true, client.status || 'active');
    passwordInput.required = false;
    passwordHint.textContent = '(kosongkan jika tidak diubah)';
    await renderAdminClientApplicationOptions(client.applications || []);
    fillAdminClientAffiliateFields(client.user_id);
    syncAdminClientRoleField(client.user_id, true);
  } else {
    title.textContent = 'Tambah Client';
    idInput.value = '';
    const userIdInput = document.getElementById('adminClientUserId');
    if (userIdInput) userIdInput.value = '';
    syncAdminClientRoleField(null, false);
    const enabledEl = document.getElementById('adminClientAffiliateEnabled');
    if (enabledEl) enabledEl.checked = false;
    syncAdminClientAccountZone(false);
    document.getElementById('adminClientEmailVerified').checked = false;
    passwordInput.required = true;
    passwordHint.textContent = '(wajib)';
    await renderAdminClientApplicationOptions([]);
  }

  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeAdminClientModal() {
  const modal = document.getElementById('adminClientModal');
  if (!modal) return;
  modal.classList.remove('open');
  setAdminClientModalLayout('client');
  document.body.style.overflow = '';
  clearAdminClientAlert();
  switchAdminClientPanel('general', modal.querySelector('.admin-app-nav-item[data-panel="general"]'));
}

async function submitAdminClientForm(event) {
  event.preventDefault();
  clearAdminClientAlert();

  const btn = document.getElementById('adminClientSubmitBtn');
  const clientId = document.getElementById('adminClientId')?.value;
  const userIdOnly = document.getElementById('adminClientUserId')?.value;

  if (adminClientModalMode === 'user' && userIdOnly) {
    const role = document.getElementById('adminClientUserRole')?.value;
    if (role && role !== adminClientUserRoleOriginal) {
      if (btn) {
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';
      }
      const result = await patchAdminUserRole(userIdOnly, role);
      if (!result.ok) {
        showAdminClientAlert(result.message || 'Gagal mengubah role.');
        if (btn) {
          btn.disabled = false;
          btn.textContent = 'Simpan';
        }
        return;
      }
      showAdminClientAlert('Role berhasil diperbarui.', 'success');
      await refreshAdminUsersTable({ force: true });
      setTimeout(() => closeAdminClientModal(), 400);
      if (btn) {
        btn.disabled = false;
        btn.textContent = 'Simpan';
      }
    } else {
      closeAdminClientModal();
    }
    return;
  }

  const isEdit = Boolean(clientId);
  const validation = validateAdminClientForm(isEdit);

  if (!validation.valid) {
    focusAdminClientPanel(validation.panel);
    showAdminClientAlert(validation.message);
    document.getElementById('adminClientAlert')?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    return;
  }

  const clientName = document.getElementById('adminClientName')?.value.trim();
  const payload = {
    name: clientName,
    company_name: clientName,
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

    const resolvedClientId = clientId || data.client?.id;
    const userId = document.getElementById('adminClientUserId')?.value
      || data.client?.user_id
      || adminUsersCache.find((u) => Number(u.client_id) === Number(resolvedClientId))?.user_id;

    const affiliateTouched = document.getElementById('adminClientAffiliateEnabled');
    if (userId && affiliateTouched) {
      try {
        await saveAdminUserAffiliate(userId);
      } catch (affiliateError) {
        showAdminClientAlert(affiliateError.message || 'Client tersimpan, tetapi affiliate gagal diperbarui.');
        await refreshAdminUsersTable({ force: true });
        return;
      }
    }

    const roleSelect = document.getElementById('adminClientUserRole');
    const newRole = roleSelect?.value;
    if (userId && newRole && newRole !== adminClientUserRoleOriginal) {
      const roleResult = await patchAdminUserRole(userId, newRole);
      if (!roleResult.ok) {
        showAdminClientAlert(roleResult.message || 'Client tersimpan, tetapi role gagal diperbarui.');
        await refreshAdminUsersTable({ force: true });
        return;
      }
    }

    showAdminClientAlert(data.message, 'success');
    await refreshAdminUsersTable({ force: true });

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

    await refreshAdminUsersTable({ force: true });
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

async function fetchAdminApplicationsList() {
  if (adminApplicationsListCache.length) {
    return adminApplicationsListCache;
  }
  try {
    const response = await fetch(adminApplicationsUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();
    if (!response.ok) {
      return [];
    }
    adminApplicationsListCache = (data.applications || []).filter((app) => app.is_active !== false);
    return adminApplicationsListCache;
  } catch {
    return [];
  }
}

function updateAdminLandingPreviewLinks() {
  const preview = document.getElementById('adminLandingPreviewLink');
  const liveEdit = document.getElementById('adminLandingLiveEditLink');
  if (!preview || !liveEdit) return;
  const app = adminApplicationsListCache.find((item) => Number(item.id) === Number(adminLandingCmsApplicationId));
  const base = app?.slug ? `/apps/${app.slug}` : landingUrl;
  preview.href = base;
  liveEdit.href = `${base}?live_edit=1`;
}

async function populateAdminLandingApplicationSelect() {
  const select = document.getElementById('adminLandingApplicationSelect');
  if (!select) return;
  const apps = await fetchAdminApplicationsList();
  select.innerHTML = apps.map((app) => `<option value="${app.id}">${escapeHtml(app.name)}</option>`).join('');
  if (!apps.length) {
    select.innerHTML = '<option value="">—</option>';
    return;
  }
  if (!adminLandingCmsApplicationId) {
    adminLandingCmsApplicationId = Number(apps[0].id);
  }
  select.value = String(adminLandingCmsApplicationId);
  updateAdminLandingPreviewLinks();
}

function onAdminLandingApplicationChange() {
  const select = document.getElementById('adminLandingApplicationSelect');
  adminLandingCmsApplicationId = select?.value ? Number(select.value) : null;
  updateAdminLandingPreviewLinks();
  refreshAdminLandingCms();
}

function showAdminHomepageAlert(message, type = 'success') {
  const el = document.getElementById('adminHomepageAlert');
  if (!el) return;
  el.hidden = false;
  el.textContent = message;
  el.classList.toggle('is-error', type === 'error');
  el.classList.toggle('is-success', type !== 'error');
}

function syncHomepageModePanels() {
  const mode = document.querySelector('input[name="homepageMode"]:checked')?.value || 'application';
  const appField = document.getElementById('homepageApplicationField');
  const hubPanel = document.getElementById('homepageHubPanel');
  const landingPanel = document.getElementById('homepageLandingPanel');
  if (appField) appField.hidden = mode !== 'application';
  if (hubPanel) hubPanel.hidden = mode !== 'default';
  if (landingPanel) landingPanel.hidden = mode !== 'application';

  if (mode === 'default') {
    refreshAdminHubContent();
  }

  const select = document.getElementById('homepageApplicationSelect');
  const app = adminApplicationsListCache.find((item) => Number(item.id) === Number(select?.value));
  const summary = document.getElementById('homepageLandingSummary');
  if (summary && app) {
    summary.textContent = `Homepage = landing ${app.name} (/apps/${app.slug} atau / jika dipilih di switcher).`;
  }
}

function fillAdminHubContentForms() {
  const site = adminHubContentCache.hub_site || {};
  const hero = adminHubContentCache.hub_hero || {};
  const catalog = adminHubContentCache.hub_catalog || {};
  const footer = adminHubContentCache.hub_footer || {};

  const set = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.value = val ?? '';
  };

  set('hubSiteBrand', site.brand);
  set('hubSiteTagline', site.tagline);
  set('hubSiteNavLogin', site.nav_login);
  set('hubSiteNavRegister', site.nav_register);
  set('hubHeroBadge', hero.badge);
  set('hubHeroTitle', hero.title);
  set('hubHeroSubtitle', hero.subtitle);
  set('hubCatalogTitle', catalog.title);
  set('hubCatalogSubtitle', catalog.subtitle);
  set('hubCatalogEmpty', catalog.empty_text);
  set('hubFooterDescription', footer.description);
  set('hubFooterProductsTitle', footer.products_title);
  set('hubFooterAccountTitle', footer.account_title);
  set('hubFooterCopyright', footer.copyright);
}

async function refreshAdminHubContent() {
  if (!adminHubContentUrl) return;
  try {
    const response = await fetch(adminHubContentUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();
    if (!response.ok) {
      showAdminHomepageAlert(data.message || 'Gagal memuat konten portal.', 'error');
      return;
    }
    adminHubContentCache = data.sections || {};
    fillAdminHubContentForms();
  } catch {
    showAdminHomepageAlert('Gagal memuat konten portal.', 'error');
  }
}

function collectAdminHubSectionPayload(key) {
  if (key === 'hub_site') {
    return {
      brand: document.getElementById('hubSiteBrand')?.value?.trim() || '',
      tagline: document.getElementById('hubSiteTagline')?.value?.trim() || '',
      nav_login: document.getElementById('hubSiteNavLogin')?.value?.trim() || '',
      nav_register: document.getElementById('hubSiteNavRegister')?.value?.trim() || '',
    };
  }
  if (key === 'hub_hero') {
    return {
      badge: document.getElementById('hubHeroBadge')?.value?.trim() || '',
      title: document.getElementById('hubHeroTitle')?.value?.trim() || '',
      subtitle: document.getElementById('hubHeroSubtitle')?.value?.trim() || '',
    };
  }
  if (key === 'hub_catalog') {
    return {
      title: document.getElementById('hubCatalogTitle')?.value?.trim() || '',
      subtitle: document.getElementById('hubCatalogSubtitle')?.value?.trim() || '',
      empty_text: document.getElementById('hubCatalogEmpty')?.value?.trim() || '',
    };
  }
  if (key === 'hub_footer') {
    return {
      description: document.getElementById('hubFooterDescription')?.value?.trim() || '',
      products_title: document.getElementById('hubFooterProductsTitle')?.value?.trim() || '',
      account_title: document.getElementById('hubFooterAccountTitle')?.value?.trim() || '',
      copyright: document.getElementById('hubFooterCopyright')?.value?.trim() || '',
    };
  }
  return {};
}

async function saveAdminHubSection(key) {
  const payload = collectAdminHubSectionPayload(key);
  try {
    const response = await fetch(`${adminHubContentUrl}/${encodeURIComponent(key)}`, {
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
      showAdminHomepageAlert(firstError || data.message || 'Gagal menyimpan.', 'error');
      return;
    }
    adminHubContentCache[key] = data.section || payload;
    showAdminHomepageAlert(data.message || 'Konten portal disimpan.');
  } catch {
    showAdminHomepageAlert('Gagal menyimpan konten portal.', 'error');
  }
}

async function refreshAdminHomepage() {
  const apps = await fetchAdminApplicationsList();
  const appSelect = document.getElementById('homepageApplicationSelect');
  if (appSelect) {
    appSelect.innerHTML = apps.map((app) => `<option value="${app.id}">${escapeHtml(app.name)}</option>`).join('');
  }

  try {
    const response = await fetch(adminHomepageSettingsUrl, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();
    if (!response.ok) {
      showAdminHomepageAlert(data.message || 'Gagal memuat pengaturan homepage.', 'error');
      return;
    }
    adminHomepageSettingsCache = data.settings || {};
    const mode = adminHomepageSettingsCache.mode === 'default' ? 'default' : 'application';
    const defaultRadio = document.getElementById('homepageModeDefault');
    const appRadio = document.getElementById('homepageModeApplication');
    if (defaultRadio) defaultRadio.checked = mode === 'default';
    if (appRadio) appRadio.checked = mode === 'application';
    if (appSelect && adminHomepageSettingsCache.application_id) {
      appSelect.value = String(adminHomepageSettingsCache.application_id);
    }
    syncHomepageModePanels();
  } catch {
    showAdminHomepageAlert('Gagal memuat pengaturan homepage.', 'error');
  }

  document.querySelectorAll('input[name="homepageMode"]').forEach((input) => {
    input.onchange = () => syncHomepageModePanels();
  });
  document.getElementById('homepageApplicationSelect')?.addEventListener('change', syncHomepageModePanels);
}

async function saveAdminHomepageSettings() {
  const mode = document.querySelector('input[name="homepageMode"]:checked')?.value || 'application';
  const applicationId = document.getElementById('homepageApplicationSelect')?.value || null;
  const btn = document.getElementById('homepageSettingsSaveBtn');
  if (btn) {
    btn.disabled = true;
  }
  try {
    const response = await fetch(adminHomepageSettingsUrl, {
      method: 'PUT',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        mode,
        application_id: mode === 'application' ? Number(applicationId) : null,
      }),
    });
    const data = await response.json();
    if (!response.ok) {
      showAdminHomepageAlert(data.message || 'Gagal menyimpan homepage.', 'error');
      return;
    }
    adminHomepageSettingsCache = data.settings || {};
    showAdminHomepageAlert(data.message || 'Pengaturan homepage disimpan.');
    syncHomepageModePanels();
  } catch {
    showAdminHomepageAlert('Gagal menyimpan homepage.', 'error');
  } finally {
    if (btn) btn.disabled = false;
  }
}

function openAdminLandingFromHomepage() {
  const appId = document.getElementById('homepageApplicationSelect')?.value;
  if (appId) {
    adminLandingCmsApplicationId = Number(appId);
  }
  switchDashPage('admin', 'landing-cms');
}

async function refreshAdminLandingCms() {
  try {
    await populateAdminLandingApplicationSelect();
    const query = adminLandingCmsApplicationId ? `?application_id=${adminLandingCmsApplicationId}` : '';
    const response = await fetch(`${adminLandingContentUrl}${query}`, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const data = await response.json();
    if (!response.ok) {
      showAdminLandingAlert(data.message || 'Gagal memuat CMS landing.', 'error');
      return;
    }
    adminLandingCmsCache = data.sections || {};
    if (data.application_id) {
      adminLandingCmsApplicationId = Number(data.application_id);
      const select = document.getElementById('adminLandingApplicationSelect');
      if (select) select.value = String(adminLandingCmsApplicationId);
    }
    renderAdminLandingCmsForms();
  } catch {
    showAdminLandingAlert('Gagal memuat CMS landing.', 'error');
  }
}

async function persistLandingSection(key, payload) {
  try {
    const query = adminLandingCmsApplicationId ? `?application_id=${adminLandingCmsApplicationId}` : '';
    const response = await fetch(`${adminLandingContentUrl}/${encodeURIComponent(key)}${query}`, {
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

    revealOnScroll();

    window.addEventListener('scroll', () => {
      updateNav();
      revealOnScroll();
    }, { passive: true });
  }

  initAppState();
});

let clientLicenseTabHiddenAt = 0;

document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'hidden') {
    clientLicenseTabHiddenAt = Date.now();
    return;
  }
  if (document.visibilityState !== 'visible') return;

  const licensePage = document.getElementById('client-license');
  if (!licensePage?.classList.contains('active')) return;

  const hiddenMs = clientLicenseTabHiddenAt ? Date.now() - clientLicenseTabHiddenAt : 0;
  if (hiddenMs < 20000) return;

  loadClientLicense({ background: true });
});
