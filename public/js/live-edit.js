const LIVE_EDIT_TARGETS = [
  { key: 'site', selector: '#navbar', label: 'Brand / Umum' },
  { key: 'hero', selector: '#hero', label: 'Hero' },
  { key: 'workflow', selector: '#cara-kerja', label: 'Workflow' },
  { key: 'pricing', selector: '#harga', label: 'Harga' },
  { key: 'faq', selector: '#faq', label: 'FAQ' },
  { key: 'footer', selector: '.footer', label: 'Footer' },
];

let liveEditActiveKey = null;
let liveEditSections = {};

function liveEditFormHtml(key) {
  const forms = {
    site: `
      <div class="form-group"><label class="admin-app-label">Nama Situs</label><input class="admin-app-input" id="cmsSiteName" type="text"></div>
      <div class="form-group"><label class="admin-app-label">Tagline</label><input class="admin-app-input" id="cmsSiteTagline" type="text"></div>`,
    hero: `
      <div class="admin-app-form-grid">
        <div class="form-group"><label class="admin-app-label">Judul awal</label><input class="admin-app-input" id="cmsHeroTitle" type="text"></div>
        <div class="form-group"><label class="admin-app-label">Highlight 1</label><input class="admin-app-input" id="cmsHeroHighlight" type="text"></div>
      </div>
      <div class="admin-app-form-grid">
        <div class="form-group"><label class="admin-app-label">Teks lanjutan</label><input class="admin-app-input" id="cmsHeroSuffix" type="text"></div>
        <div class="form-group"><label class="admin-app-label">Highlight 2</label><input class="admin-app-input" id="cmsHeroHighlight2" type="text"></div>
      </div>
      <div class="form-group"><label class="admin-app-label">Subjudul</label><textarea class="admin-app-input" id="cmsHeroSubtitle" rows="3"></textarea></div>
      <div class="admin-app-form-grid">
        <div class="form-group"><label class="admin-app-label">Tombol Utama</label><input class="admin-app-input" id="cmsHeroCtaPrimary" type="text"></div>
        <div class="form-group"><label class="admin-app-label">Tombol Sekunder</label><input class="admin-app-input" id="cmsHeroCtaSecondary" type="text"></div>
      </div>`,
    pricing: `
      <div class="form-group"><label class="admin-app-label">Badge</label><input class="admin-app-input cms-section-badge" data-section="pricing" type="text"></div>
      <div class="form-group"><label class="admin-app-label">Judul</label><input class="admin-app-input cms-section-title" data-section="pricing" type="text"></div>
      <div class="form-group"><label class="admin-app-label">Subjudul</label><textarea class="admin-app-input cms-section-subtitle" data-section="pricing" rows="2"></textarea></div>
      <div class="admin-cms-repeat-head"><strong>Paket Harga</strong><button type="button" class="btn btn-secondary" style="padding:6px 12px;font-size:11px;" onclick="addAdminLandingPricingRow()">+ Paket</button></div>
      <div id="cmsPricingItems" class="admin-cms-items"></div>`,
    faq: `
      <div class="form-group"><label class="admin-app-label">Judul</label><input class="admin-app-input cms-section-title" data-section="faq" type="text"></div>
      <div class="form-group"><label class="admin-app-label">Subjudul</label><textarea class="admin-app-input cms-section-subtitle" data-section="faq" rows="2"></textarea></div>
      <div class="admin-cms-repeat-head"><strong>Pertanyaan</strong><button type="button" class="btn btn-secondary" style="padding:6px 12px;font-size:11px;" onclick="addAdminLandingFaqRow()">+ FAQ</button></div>
      <div id="cmsFaqItems" class="admin-cms-items"></div>`,
    footer: `
      <div class="form-group"><label class="admin-app-label">Deskripsi</label><textarea class="admin-app-input" id="cmsFooterDescription" rows="3"></textarea></div>
      <div class="form-group"><label class="admin-app-label">Copyright</label><input class="admin-app-input" id="cmsFooterCopyright" type="text"></div>`,
  };
  return forms[key] || '';
}

function showLiveEditAlert(message, type = 'success') {
  const el = document.getElementById('liveEditAlert');
  if (!el) return;
  el.hidden = false;
  el.textContent = message;
  el.classList.toggle('is-error', type === 'error');
  el.classList.toggle('is-success', type !== 'error');
}

function clearLiveEditAlert() {
  const el = document.getElementById('liveEditAlert');
  if (!el) return;
  el.hidden = true;
  el.textContent = '';
}

function openLiveEditModal(key) {
  liveEditActiveKey = key;
  const target = LIVE_EDIT_TARGETS.find((t) => t.key === key);
  document.getElementById('liveEditModalTitle').textContent = `Edit ${target?.label || key}`;
  document.getElementById('liveEditFormBody').innerHTML = liveEditFormHtml(key);
  fillAdminLandingSectionFields(liveEditSections[key] || {}, key);
  document.getElementById('liveEditOverlay').hidden = false;
}

function closeLiveEditModal() {
  liveEditActiveKey = null;
  document.getElementById('liveEditOverlay').hidden = true;
  clearLiveEditAlert();
}

function injectLiveEditButtons() {
  LIVE_EDIT_TARGETS.forEach(({ key, selector, label }) => {
    const el = document.querySelector(selector);
    if (!el) return;
    const wrap = document.createElement('div');
    wrap.className = 'live-edit-section-wrap';
    el.parentNode.insertBefore(wrap, el);
    wrap.appendChild(el);
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'live-edit-section-btn';
    btn.textContent = `Edit ${label}`;
    btn.onclick = () => openLiveEditModal(key);
    wrap.appendChild(btn);
  });
}

function updateSectionHeader(root, section) {
  if (!root) return;
  const header = root.querySelector('[style*="text-align:center"]') || root.querySelector('.container > div');
  if (!header) return;
  const badge = header.querySelector('.badge-label');
  const title = header.querySelector('.section-title');
  const sub = header.querySelector('.section-sub');
  if (badge && section.badge != null) badge.textContent = section.badge;
  if (title && section.title != null) title.textContent = section.title;
  if (sub && section.subtitle != null) sub.textContent = section.subtitle;
}

function applyLiveEditToPage(key, section) {
  if (!section) return;

  if (key === 'site') {
    document.title = `${section.name || 'Quick Spot Color'} — ${section.tagline || ''}`;
    if (window.SOFTSALE_DATA) window.SOFTSALE_DATA.site = section;
    return;
  }

  if (key === 'hero') {
    const hero = document.getElementById('hero');
    if (!hero) return;
    const h1 = hero.querySelector('h1');
    if (h1) {
      h1.innerHTML = `${escapeHtml(section.title || '')} <span class="text-accent">${escapeHtml(section.title_highlight || '')}</span> ${escapeHtml(section.title_suffix || '')} <span class="text-accent">${escapeHtml(section.title_highlight_2 || '')}</span>.`;
    }
    const sub = hero.querySelector('.hero-sub');
    if (sub) sub.textContent = section.subtitle || '';
    const primaryBtn = hero.querySelector('.hero-actions .btn-primary');
    if (primaryBtn) primaryBtn.textContent = section.cta_primary || '';
    const secondaryBtn = hero.querySelector('.hero-actions .btn-secondary');
    if (secondaryBtn) secondaryBtn.textContent = section.cta_secondary || '';
    return;
  }

  if (key === 'pricing') {
    updateSectionHeader(document.getElementById('harga'), section);
    if (window.SOFTSALE_DATA) window.SOFTSALE_DATA.plans = section.items || [];
    renderPricing();
    return;
  }

  if (key === 'faq') {
    updateSectionHeader(document.getElementById('faq'), section);
    return;
  }

  if (key === 'footer') {
    const desc = document.querySelector('.footer-desc');
    const bottom = document.querySelector('.footer-bottom');
    if (desc) desc.textContent = section.description || '';
    if (bottom) bottom.textContent = section.copyright || '';
    if (window.SOFTSALE_DATA) window.SOFTSALE_DATA.footer = section;
  }
}

async function saveLiveEditSection() {
  if (!liveEditActiveKey) return;
  clearLiveEditAlert();
  const btn = document.getElementById('liveEditSaveBtn');
  const key = liveEditActiveKey;
  const payload = collectAdminLandingSectionPayload(key);
  btn.disabled = true;
  btn.textContent = 'Menyimpan...';

  const result = await persistLandingSection(key, payload);
  btn.disabled = false;
  btn.textContent = 'Simpan';

  if (!result.ok) {
    showLiveEditAlert(result.error, 'error');
    return;
  }

  liveEditSections[key] = result.section;
  if (window.SOFTSALE_DATA?.cmsSections) {
    window.SOFTSALE_DATA.cmsSections[key] = result.section;
  }
  applyLiveEditToPage(key, result.section);
  showLiveEditAlert(result.message, 'success');
  setTimeout(closeLiveEditModal, 700);
}

function initLiveEditMode() {
  if (!window.SOFTSALE_DATA?.liveEdit) return;
  liveEditSections = { ...(window.SOFTSALE_DATA.cmsSections || {}) };
  document.body.classList.add('live-edit-mode');
  injectLiveEditButtons();
}

document.addEventListener('DOMContentLoaded', initLiveEditMode);

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && liveEditActiveKey) closeLiveEditModal();
});
