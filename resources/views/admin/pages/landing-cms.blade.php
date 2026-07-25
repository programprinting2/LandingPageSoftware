    <div id="admin-landing-cms" class="dash-page">
      <div class="dash-header" style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
        <div>
          <h1>Halaman Utama</h1>
          <p>Kelola konten landing page — hero, fitur, harga, testimonial, tentang, kontak, dan footer.</p>
        </div>
        <div style="display:flex;gap:10px;flex-shrink:0;flex-wrap:wrap;">
          <a href="{{ route('landing', ['live_edit' => 1]) }}" target="_blank" rel="noopener" class="btn btn-primary">Edit Live</a>
          <a href="{{ route('landing') }}" target="_blank" rel="noopener" class="btn btn-secondary">Preview Situs</a>
        </div>
      </div>

      <div id="adminLandingAlert" class="admin-app-alert" hidden></div>

      <div class="admin-app-shell admin-cms-shell">
        <aside class="admin-app-side">
          <nav class="admin-app-nav" id="adminLandingNav">
            <button type="button" class="admin-app-nav-item active" data-panel="site" onclick="switchAdminLandingPanel('site', this)">Umum</button>
            <button type="button" class="admin-app-nav-item" data-panel="hero" onclick="switchAdminLandingPanel('hero', this)">Hero</button>
            <button type="button" class="admin-app-nav-item" data-panel="features" onclick="switchAdminLandingPanel('features', this)">Fitur</button>
            <button type="button" class="admin-app-nav-item" data-panel="pricing" onclick="switchAdminLandingPanel('pricing', this)">Harga</button>
            <button type="button" class="admin-app-nav-item" data-panel="testimonials" onclick="switchAdminLandingPanel('testimonials', this)">Testimonial</button>
            <button type="button" class="admin-app-nav-item" data-panel="about" onclick="switchAdminLandingPanel('about', this)">Tentang</button>
            <button type="button" class="admin-app-nav-item" data-panel="contact" onclick="switchAdminLandingPanel('contact', this)">Kontak</button>
            <button type="button" class="admin-app-nav-item" data-panel="footer" onclick="switchAdminLandingPanel('footer', this)">Footer</button>
          </nav>
        </aside>

        <div class="admin-app-main admin-cms-main">
          <section class="admin-app-panel active" data-panel="site">
            <div class="admin-app-panel-head"><h4>Umum</h4><p>Nama brand dan tagline situs</p></div>
            <div class="admin-app-panel-body">
              <div class="form-group"><label class="admin-app-label">Nama Situs</label><input class="admin-app-input" id="cmsSiteName" type="text"></div>
              <div class="form-group"><label class="admin-app-label">Tagline</label><input class="admin-app-input" id="cmsSiteTagline" type="text"></div>
            </div>
            <div class="admin-cms-actions"><button type="button" class="btn btn-primary" onclick="saveAdminLandingSection('site')">Simpan Umum</button></div>
          </section>

          <section class="admin-app-panel" data-panel="hero">
            <div class="admin-app-panel-head"><h4>Hero</h4><p>Bagian utama landing page</p></div>
            <div class="admin-app-panel-body">
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
              <table class="admin-app-acc-table admin-cms-table"><thead><tr><th>Nilai</th><th>Label</th><th>Animasi</th><th></th></tr></thead><tbody id="cmsHeroStatsBody"></tbody></table>
            </div>
            <div class="admin-cms-actions"><button type="button" class="btn btn-primary" onclick="saveAdminLandingSection('hero')">Simpan Hero</button></div>
          </section>

          <section class="admin-app-panel" data-panel="features">
            <div class="admin-app-panel-head"><h4>Fitur</h4><p>Section header dan daftar fitur</p></div>
            <div class="admin-app-panel-body">
              <div class="form-group"><label class="admin-app-label">Badge</label><input class="admin-app-input cms-section-badge" data-section="features" type="text"></div>
              <div class="form-group"><label class="admin-app-label">Judul</label><input class="admin-app-input cms-section-title" data-section="features" type="text"></div>
              <div class="form-group"><label class="admin-app-label">Subjudul</label><textarea class="admin-app-input cms-section-subtitle" data-section="features" rows="2"></textarea></div>
              <div class="admin-cms-repeat-head"><strong>Item Fitur</strong><button type="button" class="btn btn-secondary" style="padding:6px 12px;font-size:11px;" onclick="addAdminLandingFeatureRow()">+ Fitur</button></div>
              <div id="cmsFeaturesItems" class="admin-cms-items"></div>
            </div>
            <div class="admin-cms-actions"><button type="button" class="btn btn-primary" onclick="saveAdminLandingSection('features')">Simpan Fitur</button></div>
          </section>

          <section class="admin-app-panel" data-panel="pricing">
            <div class="admin-app-panel-head"><h4>Harga</h4><p>Section header dan paket harga tampilan</p></div>
            <div class="admin-app-panel-body">
              <div class="form-group"><label class="admin-app-label">Badge</label><input class="admin-app-input cms-section-badge" data-section="pricing" type="text"></div>
              <div class="form-group"><label class="admin-app-label">Judul</label><input class="admin-app-input cms-section-title" data-section="pricing" type="text"></div>
              <div class="form-group"><label class="admin-app-label">Subjudul</label><textarea class="admin-app-input cms-section-subtitle" data-section="pricing" rows="2"></textarea></div>
              <div class="admin-cms-repeat-head"><strong>Paket Harga</strong><button type="button" class="btn btn-secondary" style="padding:6px 12px;font-size:11px;" onclick="addAdminLandingPricingRow()">+ Paket</button></div>
              <div id="cmsPricingItems" class="admin-cms-items"></div>
            </div>
            <div class="admin-cms-actions"><button type="button" class="btn btn-primary" onclick="saveAdminLandingSection('pricing')">Simpan Harga</button></div>
          </section>

          <section class="admin-app-panel" data-panel="testimonials">
            <div class="admin-app-panel-head"><h4>Testimonial</h4><p>Ulasan dari client</p></div>
            <div class="admin-app-panel-body">
              <div class="form-group"><label class="admin-app-label">Badge</label><input class="admin-app-input cms-section-badge" data-section="testimonials" type="text"></div>
              <div class="form-group"><label class="admin-app-label">Judul</label><input class="admin-app-input cms-section-title" data-section="testimonials" type="text"></div>
              <div class="form-group"><label class="admin-app-label">Subjudul</label><textarea class="admin-app-input cms-section-subtitle" data-section="testimonials" rows="2"></textarea></div>
              <div class="admin-cms-repeat-head"><strong>Testimonial</strong><button type="button" class="btn btn-secondary" style="padding:6px 12px;font-size:11px;" onclick="addAdminLandingTestimonialRow()">+ Testimonial</button></div>
              <div id="cmsTestimonialsItems" class="admin-cms-items"></div>
            </div>
            <div class="admin-cms-actions"><button type="button" class="btn btn-primary" onclick="saveAdminLandingSection('testimonials')">Simpan Testimonial</button></div>
          </section>

          <section class="admin-app-panel" data-panel="about">
            <div class="admin-app-panel-head"><h4>Tentang</h4><p>Profil perusahaan dan nilai-nilai</p></div>
            <div class="admin-app-panel-body">
              <div class="form-group"><label class="admin-app-label">Badge</label><input class="admin-app-input" id="cmsAboutBadge" type="text"></div>
              <div class="admin-app-form-grid">
                <div class="form-group"><label class="admin-app-label">Judul baris 1</label><input class="admin-app-input" id="cmsAboutTitle" type="text"></div>
                <div class="form-group"><label class="admin-app-label">Judul baris 2</label><input class="admin-app-input" id="cmsAboutTitleBreak" type="text"></div>
              </div>
              <div class="form-group"><label class="admin-app-label">Paragraf (satu per baris)</label><textarea class="admin-app-input" id="cmsAboutParagraphs" rows="4"></textarea></div>
              <div class="admin-cms-repeat-head"><strong>Statistik Tentang</strong><button type="button" class="btn btn-secondary" style="padding:6px 12px;font-size:11px;" onclick="addAdminLandingStatRow('cmsAboutStatsBody')">+ Stat</button></div>
              <table class="admin-app-acc-table admin-cms-table"><thead><tr><th>Nilai</th><th>Label</th><th>Animasi</th><th></th></tr></thead><tbody id="cmsAboutStatsBody"></tbody></table>
              <div class="admin-cms-repeat-head" style="margin-top:18px;"><strong>Nilai Perusahaan</strong><button type="button" class="btn btn-secondary" style="padding:6px 12px;font-size:11px;" onclick="addAdminLandingValueRow()">+ Nilai</button></div>
              <div id="cmsAboutValues" class="admin-cms-items"></div>
            </div>
            <div class="admin-cms-actions"><button type="button" class="btn btn-primary" onclick="saveAdminLandingSection('about')">Simpan Tentang</button></div>
          </section>

          <section class="admin-app-panel" data-panel="contact">
            <div class="admin-app-panel-head"><h4>Kontak</h4><p>Informasi kontak yang ditampilkan</p></div>
            <div class="admin-app-panel-body">
              <div class="form-group"><label class="admin-app-label">Badge</label><input class="admin-app-input cms-section-badge" data-section="contact" type="text"></div>
              <div class="form-group"><label class="admin-app-label">Judul</label><input class="admin-app-input cms-section-title" data-section="contact" type="text"></div>
              <div class="form-group"><label class="admin-app-label">Subjudul</label><textarea class="admin-app-input cms-section-subtitle" data-section="contact" rows="2"></textarea></div>
              <div class="admin-cms-repeat-head"><strong>Kontak</strong><button type="button" class="btn btn-secondary" style="padding:6px 12px;font-size:11px;" onclick="addAdminLandingContactRow()">+ Kontak</button></div>
              <div id="cmsContactItems" class="admin-cms-items"></div>
            </div>
            <div class="admin-cms-actions"><button type="button" class="btn btn-primary" onclick="saveAdminLandingSection('contact')">Simpan Kontak</button></div>
          </section>

          <section class="admin-app-panel" data-panel="footer">
            <div class="admin-app-panel-head"><h4>Footer</h4><p>Deskripsi brand dan copyright</p></div>
            <div class="admin-app-panel-body">
              <div class="form-group"><label class="admin-app-label">Deskripsi</label><textarea class="admin-app-input" id="cmsFooterDescription" rows="3"></textarea></div>
              <div class="form-group"><label class="admin-app-label">Copyright</label><input class="admin-app-input" id="cmsFooterCopyright" type="text"></div>
            </div>
            <div class="admin-cms-actions"><button type="button" class="btn btn-primary" onclick="saveAdminLandingSection('footer')">Simpan Footer</button></div>
          </section>
        </div>
      </div>
    </div>
