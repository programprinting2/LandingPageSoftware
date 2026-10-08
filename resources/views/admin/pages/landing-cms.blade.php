    <div id="admin-landing-cms" class="dash-page">
      <div class="dash-header" style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
        <div>
          <h1>Halaman Utama</h1>
          <p>Kelola konten landing page — hero, workflow, fitur, harga, FAQ, dan footer.</p>
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
            <button type="button" class="admin-app-nav-item" data-panel="faq" onclick="switchAdminLandingPanel('faq', this)">FAQ</button>
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
              </div>
            </div>
            <div class="admin-cms-actions"><button type="button" class="btn btn-primary" onclick="saveAdminLandingSection('hero')">Simpan Hero</button></div>
          </section>

          <section class="admin-app-panel" data-panel="features">
            <div class="admin-app-panel-head"><h4>Fitur</h4><p>Judul section, deskripsi, dan gambar setiap fitur</p></div>
            <div class="admin-app-panel-body">
              <div class="form-group"><label class="admin-app-label">Judul Section</label><input class="admin-app-input cms-section-title" data-section="features" type="text"></div>
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

          <section class="admin-app-panel" data-panel="faq">
            <div class="admin-app-panel-head"><h4>FAQ</h4><p>Pertanyaan umum</p></div>
            <div class="admin-app-panel-body">
              <div class="form-group"><label class="admin-app-label">Judul</label><input class="admin-app-input cms-section-title" data-section="faq" type="text"></div>
              <div class="form-group"><label class="admin-app-label">Subjudul</label><textarea class="admin-app-input cms-section-subtitle" data-section="faq" rows="2"></textarea></div>
              <div class="admin-cms-repeat-head"><strong>Pertanyaan</strong><button type="button" class="btn btn-secondary" style="padding:6px 12px;font-size:11px;" onclick="addAdminLandingFaqRow()">+ FAQ</button></div>
              <div id="cmsFaqItems" class="admin-cms-items"></div>
            </div>
            <div class="admin-cms-actions"><button type="button" class="btn btn-primary" onclick="saveAdminLandingSection('faq')">Simpan FAQ</button></div>
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
