const LIVE_EDIT_TARGETS = [
  { key: 'site', selector: '#navbar', label: 'Brand / Umum' },
  { key: 'hero', selector: '#hero', label: 'Hero' },
  { key: 'features', selector: '#fitur', label: 'Fitur' },
  { key: 'pricing', selector: '#harga', label: 'Harga' },
  { key: 'testimonials', selector: '#testimonial', label: 'Testimonial' },
  { key: 'about', selector: '#tentang', label: 'Tentang' },
  { key: 'contact', selector: '#kontak', label: 'Kontak' },
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
      <div class="form-group"><label class="admin-app-label">Badge</label><input class="admin-app-input" id="cmsHeroBadge" type="text"></div>
      <div class="admin-app-form-grid">
        <div class="form-group"><label class="admin-app-label">Judul</label><input class="admin-app-input" id="cmsHeroTitle" type="text"></div>
        <div class="form-group"><label class="admin-app-label">Highlight (gradient)</label><input class="admin-app-input" id="cmsHeroHighlight" type="text"></div>
      </div>
      <div class="form-group"><label class="admin-app-label">Subjudul</label><textarea class="admin-app-input" id="cmsHeroSubtitle" rows="3"></textarea></div>
      <div class="admin-app-form-grid">
        <div class="form-group"><label class="admin-app-label">Tombol Utama</label><input class="admin-app-input" id="cmsHeroCtaPrimary" type="text"></div>
        <div class="form-group"><label class="admin-app-label">Tombol Sekunder</label><input class="admin-app-input" id="cmsHeroCtaSecondary" type="text"></div>
      </div>
      <div class="admin-cms-repeat-head"><strong>Statistik Hero</strong><button type="button" class="btn btn-secondary" style="padding:6px 12px;font-size:11px;" onclick="addAdminLandingStatRow('cmsHeroStatsBody')">+ Stat</button></div>
      <table class="admin-app-acc-table admin-cms-table"><thead><tr><th>Nilai</th><th>Label</th><th>Animasi</th><th></th></tr></thead><tbody id="cmsHeroStatsBody"></tbody></table>`,
    features: `
      <div class="form-group"><label class="admin-app-label">Badge</label><input class="admin-app-input cms-section-badge" data-section="features" type="text"></div>
      <div class="form-group"><label class="admin-app-label">Judul</label><input class="admin-app-input cms-section-title" data-section="features" type="text"></div>
      <div class="form-group"><label class="admin-app-label">Subjudul</label><textarea class="admin-app-input cms-section-subtitle" data-section="features" rows="2"></textarea></div>
      <div class="admin-cms-repeat-head"><strong>Item Fitur</strong><button type="button" class="btn btn-secondary" style="padding:6px 12px;font-size:11px;" onclick="addAdminLandingFeatureRow()">+ Fitur</button></div>
      <div id="cmsFeaturesItems" class="admin-cms-items"></div>`,
    pricing: `
      <div class="form-group"><label class="admin-app-label">Badge</label><input class="admin-app-input cms-section-badge" data-section="pricing" type="text"></div>
      <div class="form-group"><label class="admin-app-label">Judul</label><input class="admin-app-input cms-section-title" data-section="pricing" type="text"></div>
      <div class="form-group"><label class="admin-app-label">Subjudul</label><textarea class="admin-app-input cms-section-subtitle" data-section="pricing" rows="2"></textarea></div>
      <div class="admin-cms-repeat-head"><strong>Paket Harga</strong><button type="button" class="btn btn-secondary" style="padding:6px 12px;font-size:11px;" onclick="addAdminLandingPricingRow()">+ Paket</button></div>
      <div id="cmsPricingItems" class="admin-cms-items"></div>`,
    testimonials: `
      <div class="form-group"><label class="admin-app-label">Badge</label><input class="admin-app-input cms-section-badge" data-section="testimonials" type="text"></div>
      <div class="form-group"><label class="admin-app-label">Judul</label><input class="admin-app-input cms-section-title" data-section="testimonials" type="text"></div>
      <div class="form-group"><label class="admin-app-label">Subjudul</label><textarea class="admin-app-input cms-section-subtitle" data-section="testimonials" rows="2"></textarea></div>
      <div class="admin-cms-repeat-head"><strong>Testimonial</strong><button type="button" class="btn btn-secondary" style="padding:6px 12px;font-size:11px;" onclick="addAdminLandingTestimonialRow()">+ Testimonial</button></div>
      <div id="cmsTestimonialsItems" class="admin-cms-items"></div>`,
    about: `
      <div class="form-group"><label class="admin-app-label">Badge</label><input class="admin-app-input" id="cmsAboutBadge" type="text"></div>
      <div class="admin-app-form-grid">
        <div class="form-group"><label class="admin-app-label">Judul baris 1</label><input class="admin-app-input" id="cmsAboutTitle" type="text"></div>
        <div class="form-group"><label class="admin-app-label">Judul baris 2</label><input class="admin-app-input" id="cmsAboutTitleBreak" type="text"></div>
      </div>
      <div class="form-group"><label class="admin-app-label">Paragraf (pisah dengan baris kosong)</label><textarea class="admin-app-input" id="cmsAboutParagraphs" rows="4"></textarea></div>
      <div class="admin-cms-repeat-head"><strong>Statistik Tentang</strong><button type="button" class="btn btn-secondary" style="padding:6px 12px;font-size:11px;" onclick="addAdminLandingStatRow('cmsAboutStatsBody')">+ Stat</button></div>
      <table class="admin-app-acc-table admin-cms-table"><thead><tr><th>Nilai</th><th>Label</th><th>Animasi</th><th></th></tr></thead><tbody id="cmsAboutStatsBody"></tbody></table>
      <div class="admin-cms-repeat-head" style="margin-top:18px;"><strong>Nilai Perusahaan</strong><button type="button" class="btn btn-secondary" style="padding:6px 12px;font-size:11px;" onclick="addAdminLandingValueRow()">+ Nilai</button></div>
      <div id="cmsAboutValues" class="admin-cms-items"></div>`,
    contact: `
      <div class="form-group"><label class="admin-app-label">Badge</label><input class="admin-app-input cms-section-badge" data-section="contact" type="text"></div>
      <div class="form-group"><label class="admin-app-label">Judul</label><input class="admin-app-input cms-section-title" data-section="contact" type="text"></div>
      <div class="form-group"><label class="admin-app-label">Subjudul</label><textarea class="admin-app-input cms-section-subtitle" data-section="contact" rows="2"></textarea></div>
      <div class="admin-cms-repeat-head"><strong>Kontak</strong><button type="button" class="btn btn-secondary" style="padding:6px 12px;font-size:11px;" onclick="addAdminLandingContactRow()">+ Kontak</button></div>
      <div id="cmsContactItems" class="admin-cms-items"></div>`,
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

function injectLiveEditButtons() {
  LIVE_EDIT_TARGETS.forEach(({ key, selector, label }) => {
    const target = document.querySelector(selector);
    if (!target || target.querySelector('.live-edit-btn')) return;
    target.classList.add('live-edit-target');
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'live-edit-btn';
    btn.textContent = 'Edit';
    btn.setAttribute('aria-label', `Edit ${label}`);
    btn.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      openLiveEditModal(key, label);
    });
    target.appendChild(btn);
  });
}

function openLiveEditModal(key, label) {
  liveEditActiveKey = key;
  clearLiveEditAlert();
  document.getElementById('liveEditModalTitle').textContent = `Edit ${label}`;
  const body = document.getElementById('liveEditFormBody');
  body.innerHTML = liveEditFormHtml(key);
  fillAdminLandingSectionFields(liveEditSections[key] || {}, key);
  document.getElementById('liveEditOverlay').hidden = false;
  document.body.classList.add('live-edit-modal-open');
}

function closeLiveEditModal() {
  liveEditActiveKey = null;
  document.getElementById('liveEditOverlay').hidden = true;
  document.body.classList.remove('live-edit-modal-open');
  document.getElementById('liveEditFormBody').innerHTML = '';
  clearLiveEditAlert();
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

function rebuildHeroStats(stats) {
  const container = document.querySelector('#hero .hero-stats');
  if (!container) return;
  container.innerHTML = (stats || []).map((stat) => `
    <div>
      ${stat.animate
        ? `<div class="hero-stat-n" data-count="${escapeHtml(String(stat.value))}">0</div>`
        : `<div class="hero-stat-n">${escapeHtml(String(stat.value))}</div>`}
      <div class="hero-stat-l">${escapeHtml(stat.label || '')}</div>
    </div>
  `).join('');
  if (typeof counterDone !== 'undefined') counterDone = {};
  animateCounters();
}

function rebuildAboutStats(stats) {
  const container = document.querySelector('#tentang .about-stats');
  if (!container) return;
  container.innerHTML = (stats || []).map((stat) => `
    <div>
      ${stat.animate
        ? `<div class="about-stat-n" data-count="${escapeHtml(String(stat.value))}">0</div>`
        : `<div class="about-stat-n">${escapeHtml(String(stat.value))}</div>`}
      <div class="about-stat-l">${escapeHtml(stat.label || '')}</div>
    </div>
  `).join('');
  if (typeof counterDone !== 'undefined') counterDone = {};
  animateCounters();
}

function applyLiveEditToPage(key, section) {
  if (!section) return;

  if (key === 'site') {
    document.querySelectorAll('#navbar .nav-logo, .footer-brand').forEach((el) => {
      el.textContent = section.name || '';
    });
    document.title = `${section.name || 'SoftSale'} — ${section.tagline || ''}`;
    if (window.SOFTSALE_DATA) {
      window.SOFTSALE_DATA.site = section;
    }
    return;
  }

  if (key === 'hero') {
    const hero = document.getElementById('hero');
    if (!hero) return;
    const badge = hero.querySelector('.badge-label');
    if (badge) badge.textContent = section.badge || '';
    const h1 = hero.querySelector('h1');
    if (h1) {
      h1.innerHTML = `${escapeHtml(section.title || '')} <span class="gradient-text">${escapeHtml(section.title_highlight || '')}</span>`;
    }
    const sub = hero.querySelector('.hero-sub');
    if (sub) sub.textContent = section.subtitle || '';
    const primaryBtn = hero.querySelector('.hero-actions .btn-primary');
    if (primaryBtn) {
      const svg = primaryBtn.querySelector('svg');
      primaryBtn.textContent = `${section.cta_primary || ''} `;
      if (svg) primaryBtn.appendChild(svg);
    }
    let secondaryBtn = hero.querySelector('.hero-actions .btn-secondary');
    if (section.cta_secondary) {
      if (!secondaryBtn) {
        const actions = hero.querySelector('.hero-actions');
        secondaryBtn = document.createElement('button');
        secondaryBtn.className = 'btn btn-secondary btn-lg';
        actions?.appendChild(secondaryBtn);
      }
      secondaryBtn.textContent = section.cta_secondary;
      secondaryBtn.hidden = false;
    } else if (secondaryBtn) {
      secondaryBtn.remove();
    }
    rebuildHeroStats(section.stats || []);
    return;
  }

  if (key === 'features') {
    updateSectionHeader(document.getElementById('fitur'), section);
    if (window.SOFTSALE_DATA) window.SOFTSALE_DATA.features = section.items || [];
    renderFeatures();
    return;
  }

  if (key === 'pricing') {
    updateSectionHeader(document.getElementById('harga'), section);
    if (window.SOFTSALE_DATA) window.SOFTSALE_DATA.plans = section.items || [];
    renderPricing();
    return;
  }

  if (key === 'testimonials') {
    updateSectionHeader(document.getElementById('testimonial'), section);
    if (window.SOFTSALE_DATA) window.SOFTSALE_DATA.testimonials = section.items || [];
    renderTestimonials();
    return;
  }

  if (key === 'about') {
    const root = document.getElementById('tentang');
    if (!root) return;
    const left = root.querySelector('.about-left');
    if (left) {
      const badge = left.querySelector('.badge-label');
      if (badge) badge.textContent = section.badge || '';
      const title = left.querySelector('.section-title');
      if (title) {
        title.innerHTML = `${escapeHtml(section.title || '')}<br>${escapeHtml(section.title_break || '')}`;
      }
      left.querySelectorAll('p').forEach((p) => p.remove());
      const statsEl = left.querySelector('.about-stats');
      (section.paragraphs || []).forEach((paragraph) => {
        const p = document.createElement('p');
        p.textContent = paragraph;
        left.insertBefore(p, statsEl);
      });
      rebuildAboutStats(section.stats || []);
    }
    if (window.SOFTSALE_DATA) window.SOFTSALE_DATA.aboutValues = section.values || [];
    renderAboutValues();
    return;
  }

  if (key === 'contact') {
    updateSectionHeader(document.getElementById('kontak'), section);
    if (window.SOFTSALE_DATA) window.SOFTSALE_DATA.contacts = section.items || [];
    renderContacts();
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
