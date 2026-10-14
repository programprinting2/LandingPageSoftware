    <div id="admin-homepage" class="dash-page">
      <div class="dash-header">
        <h1>Home Page</h1>
        <p>Tentukan apakah <code>/</code> menampilkan portal default atau landing aplikasi.</p>
      </div>

      <div id="adminHomepageAlert" class="admin-app-alert" hidden></div>

      <div class="dash-form-card admin-settings-card">
        <div class="form-group">
          <label class="admin-app-label">Homepage publik (<code>/</code>)</label>
          <div class="admin-homepage-mode" style="display:flex;flex-direction:column;gap:10px;margin-top:8px;">
            <label style="display:flex;align-items:center;gap:10px;font-size:14px;cursor:pointer;">
              <input type="radio" name="homepageMode" value="default" id="homepageModeDefault">
              <span><strong>Default</strong> — portal umum (katalog produk)</span>
            </label>
            <label style="display:flex;align-items:center;gap:10px;font-size:14px;cursor:pointer;">
              <input type="radio" name="homepageMode" value="application" id="homepageModeApplication">
              <span><strong>Landing aplikasi</strong> — tampilkan landing produk sebagai homepage</span>
            </label>
          </div>
        </div>

        <div class="form-group" id="homepageApplicationField" hidden>
          <label class="admin-app-label" for="homepageApplicationSelect">Aplikasi</label>
          <select class="admin-app-input" id="homepageApplicationSelect"></select>
        </div>

        <div class="admin-settings-actions">
          <button type="button" class="btn btn-primary" id="homepageSettingsSaveBtn" onclick="saveAdminHomepageSettings()">Simpan mode homepage</button>
          <a href="{{ route('landing') }}" target="_blank" rel="noopener" class="btn btn-secondary">Preview /</a>
        </div>
      </div>

      <div id="homepageHubPanel" hidden>
        <div class="dash-form-card admin-settings-card">
          <div class="admin-settings-panel-head" style="margin-bottom:20px;">
            <div>
              <h3>Header &amp; brand</h3>
              <p>Logo/teks di navbar portal dan tombol masuk/daftar.</p>
            </div>
          </div>
          <div class="admin-app-form-grid">
            <div class="form-group"><label class="admin-app-label">Nama brand</label><input class="admin-app-input" id="hubSiteBrand" type="text"></div>
            <div class="form-group"><label class="admin-app-label">Tagline (di bawah brand)</label><input class="admin-app-input" id="hubSiteTagline" type="text"></div>
            <div class="form-group"><label class="admin-app-label">Tombol Masuk</label><input class="admin-app-input" id="hubSiteNavLogin" type="text"></div>
            <div class="form-group"><label class="admin-app-label">Tombol Daftar</label><input class="admin-app-input" id="hubSiteNavRegister" type="text"></div>
          </div>
          <div class="admin-settings-actions"><button type="button" class="btn btn-primary" onclick="saveAdminHubSection('hub_site')">Simpan Header</button></div>
        </div>

        <div class="dash-form-card admin-settings-card">
          <div class="admin-settings-panel-head" style="margin-bottom:20px;">
            <div>
              <h3>Hero portal</h3>
              <p>Bagian utama di atas daftar produk.</p>
            </div>
          </div>
          <div class="form-group"><label class="admin-app-label">Badge</label><input class="admin-app-input" id="hubHeroBadge" type="text"></div>
          <div class="form-group"><label class="admin-app-label">Judul</label><input class="admin-app-input" id="hubHeroTitle" type="text"></div>
          <div class="form-group"><label class="admin-app-label">Subjudul</label><textarea class="admin-app-input" id="hubHeroSubtitle" rows="3"></textarea></div>
          <div class="admin-settings-actions"><button type="button" class="btn btn-primary" onclick="saveAdminHubSection('hub_hero')">Simpan Hero</button></div>
        </div>

        <div class="dash-form-card admin-settings-card">
          <div class="admin-settings-panel-head" style="margin-bottom:20px;">
            <div>
              <h3>Section katalog</h3>
              <p>Judul di atas kartu aplikasi (kartu diisi otomatis dari Master Aplikasi).</p>
            </div>
          </div>
          <div class="form-group"><label class="admin-app-label">Judul section</label><input class="admin-app-input" id="hubCatalogTitle" type="text"></div>
          <div class="form-group"><label class="admin-app-label">Subjudul</label><textarea class="admin-app-input" id="hubCatalogSubtitle" rows="2"></textarea></div>
          <div class="form-group"><label class="admin-app-label">Teks jika belum ada aplikasi</label><input class="admin-app-input" id="hubCatalogEmpty" type="text"></div>
          <div class="admin-settings-actions"><button type="button" class="btn btn-primary" onclick="saveAdminHubSection('hub_catalog')">Simpan Katalog</button></div>
        </div>

        <div class="dash-form-card admin-settings-card">
          <div class="admin-settings-panel-head" style="margin-bottom:20px;">
            <div>
              <h3>Footer</h3>
              <p>Deskripsi, kolom judul, dan copyright.</p>
            </div>
          </div>
          <div class="form-group"><label class="admin-app-label">Deskripsi</label><textarea class="admin-app-input" id="hubFooterDescription" rows="3"></textarea></div>
          <div class="admin-app-form-grid">
            <div class="form-group"><label class="admin-app-label">Judul kolom Produk</label><input class="admin-app-input" id="hubFooterProductsTitle" type="text"></div>
            <div class="form-group"><label class="admin-app-label">Judul kolom Akun</label><input class="admin-app-input" id="hubFooterAccountTitle" type="text"></div>
          </div>
          <div class="form-group"><label class="admin-app-label">Copyright</label><input class="admin-app-input" id="hubFooterCopyright" type="text"></div>
          <div class="admin-settings-actions"><button type="button" class="btn btn-primary" onclick="saveAdminHubSection('hub_footer')">Simpan Footer</button></div>
        </div>
      </div>

      <div class="dash-form-card admin-settings-card" id="homepageLandingPanel" hidden>
        <div class="admin-settings-panel-head">
          <div>
            <h3>Landing sebagai homepage</h3>
            <p id="homepageLandingSummary">Homepage = landing aplikasi terpilih.</p>
          </div>
        </div>
        <p style="font-size:13px;color:#64748b;margin:0 0 12px;">Edit hero, FAQ, overlay harga, dll. di menu <strong>Landing Aplikasi</strong>.</p>
        <button type="button" class="btn btn-secondary" id="homepageEditLandingBtn" onclick="openAdminLandingFromHomepage()">Buka Landing Aplikasi</button>
      </div>
    </div>
