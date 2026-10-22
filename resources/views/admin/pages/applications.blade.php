    <div id="admin-applications" class="dash-page">
      <div class="dash-header"><h1>Master Aplikasi</h1><p>Kelola aplikasi yang dijual beserta paket harganya</p></div>
      <div class="dash-table-wrap">
        <div class="dash-table-header">
          <h3 id="adminApplicationsCount">Memuat…</h3>
          <button type="button" class="btn btn-primary" style="padding:8px 16px;font-size:12px;" onclick="openAdminApplicationModal()">+ Tambah Aplikasi</button>
        </div>
        <table class="dash-table admin-apps-table">
          <thead>
            <tr>
              <th>Nama</th>
              <th>Slug</th>
              <th>Paket</th>
              <th>Client</th>
              <th>Aktivasi</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="adminApplicationsTableBody">
            <tr><td colspan="7" style="text-align:center;color:var(--text-3);padding:28px;">Memuat…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal-overlay admin-app-overlay" id="adminApplicationModal">
      <div class="admin-app-modal">
        <div class="admin-app-modal-header">
          <div>
            <h3 id="adminApplicationModalTitle">Tambah Aplikasi</h3>
            <p>Kelola informasi aplikasi dan paket harga</p>
          </div>
          <button type="button" class="admin-app-modal-close" onclick="closeAdminApplicationModal()" aria-label="Tutup">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
          </button>
        </div>

        <div id="adminApplicationAlert" class="admin-app-alert" hidden></div>

        <form id="adminApplicationForm" onsubmit="submitAdminApplicationForm(event)">
          <input type="hidden" id="adminApplicationId" value="">

          <div class="admin-app-shell">
            <aside class="admin-app-side">
              <nav class="admin-app-nav">
                <button type="button" class="admin-app-nav-item active" data-panel="general" onclick="switchAdminApplicationPanel('general', this)">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
                  Informasi Umum
                </button>
                <button type="button" class="admin-app-nav-item" data-panel="packages" onclick="switchAdminApplicationPanel('packages', this)">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                  Detail Paket
                </button>
              </nav>
            </aside>

            <div class="admin-app-main">
              <section class="admin-app-panel active" data-panel="general">
                <div class="admin-app-panel-head">
                  <h4>Informasi Umum</h4>
                  <p>Data dasar aplikasi yang dijual</p>
                </div>
                <div class="admin-app-panel-body">
                  <div class="form-group">
                    <label class="admin-app-label" for="adminApplicationName">Nama Aplikasi</label>
                    <input class="admin-app-input" id="adminApplicationName" type="text" required placeholder="Contoh: Spot Color">
                  </div>
                  <div class="form-group">
                    <label class="admin-app-label" for="adminApplicationSlug">Slug <span class="admin-app-label-hint">(opsional, auto dari nama)</span></label>
                    <input class="admin-app-input" id="adminApplicationSlug" type="text" placeholder="spot-color">
                  </div>
                  <div class="form-group">
                    <label class="admin-app-label" for="adminApplicationDescription">Deskripsi</label>
                    <textarea class="admin-app-input" id="adminApplicationDescription" rows="3" placeholder="Ringkasan aplikasi"></textarea>
                  </div>
                  <div class="form-group">
                    <label class="admin-app-label">Logo aplikasi</label>
                    <input type="hidden" id="adminApplicationLogoUrl" value="">
                    <div class="admin-app-logo-field">
                      <div id="adminApplicationLogoPreview" class="admin-app-logo-preview">
                        <span class="admin-app-logo-empty">Belum ada logo</span>
                      </div>
                      <div class="admin-app-logo-actions">
                        <label class="admin-app-btn-outline admin-app-logo-pick">
                          Unggah logo
                          <input type="file" id="adminApplicationLogoFile" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml" hidden onchange="uploadAdminApplicationLogo(this)">
                        </label>
                        <button type="button" class="admin-app-btn-outline" id="adminApplicationLogoClearBtn" hidden onclick="clearAdminApplicationLogo()">Hapus</button>
                      </div>
                    </div>
                    <p class="admin-app-label-hint" style="margin-top:8px;">PNG/JPG/WebP/SVG, maks. 5 MB. Disimpan di <code>/images/applications/</code>.</p>
                  </div>
                  <div class="admin-app-form-grid">
                    <div class="form-group">
                      <label class="admin-app-label" for="adminApplicationDevicesMax">Limit default</label>
                      <input class="admin-app-input" id="adminApplicationDevicesMax" type="number" min="1" max="99" value="3" required>
                    </div>
                    <div class="form-group">
                      <label class="admin-app-label" for="adminApplicationSortOrder">Urutan tampil</label>
                      <input class="admin-app-input" id="adminApplicationSortOrder" type="number" min="0" max="9999" value="0">
                    </div>
                  </div>
                  <div class="admin-app-toggle-row">
                    <label class="admin-app-toggle">
                      <input id="adminApplicationIsActive" type="checkbox" checked>
                      <span class="admin-app-toggle-ui"></span>
                    </label>
                    <div>
                      <div class="admin-app-toggle-title">Aplikasi aktif</div>
                      <div class="admin-app-toggle-desc">Dapat dijual dan diaktivasi oleh client</div>
                    </div>
                  </div>
                </div>
              </section>

              <section class="admin-app-panel" data-panel="packages">
                <div class="admin-app-panel-head admin-app-panel-head-row">
                  <div>
                    <h4>Detail Paket</h4>
                    <p>Trial, Basic, Pro, Platinum — isi bebas. Komisi Rp dan % saling hitung otomatis dari harga paket.</p>
                  </div>
                  <button type="button" class="admin-app-btn-outline" onclick="addAdminApplicationPackageRow()">+ Tambah Paket</button>
                </div>
                <div class="admin-app-panel-body">
                  <div class="admin-app-packages-table-wrap">
                    <table class="admin-app-packages-table">
                      <colgroup>
                        <col class="col-pkg-name">
                        <col class="col-pkg-quota">
                        <col class="col-pkg-days">
                        <col class="col-pkg-price">
                        <col class="col-pkg-discount">
                        <col class="col-pkg-commission-fixed">
                        <col class="col-pkg-commission-percent">
                        <col class="col-pkg-actions">
                      </colgroup>
                      <thead>
                        <tr>
                          <th>Nama Paket</th>
                          <th class="col-pkg-quota-head">Limit</th>
                          <th class="col-pkg-days-head">Hari</th>
                          <th class="col-pkg-price-head">Harga</th>
                          <th class="col-pkg-discount-head">Discount %</th>
                          <th class="col-pkg-commission-fixed-head">Komisi Rp</th>
                          <th class="col-pkg-commission-percent-head">Komisi %</th>
                          <th class="col-pkg-actions-head"></th>
                        </tr>
                      </thead>
                      <tbody id="adminApplicationPackagesBody"></tbody>
                    </table>
                  </div>
                  <div id="adminApplicationPackagesEmpty" class="admin-app-packages-empty">Belum ada paket. Klik “Tambah Paket”.</div>
                </div>
              </section>
            </div>
          </div>

          <div class="admin-pkg-features-overlay" id="adminPackageFeaturesModal" hidden>
            <div class="admin-pkg-features-dialog" role="dialog" aria-modal="true" aria-labelledby="adminPackageFeaturesTitle">
              <div class="admin-pkg-features-head">
                <div>
                  <h4 id="adminPackageFeaturesTitle">Fitur paket</h4>
                  <p id="adminPackageFeaturesSubtitle">Batas fitur di aplikasi desktop (SpotColor)</p>
                </div>
                <button type="button" class="admin-app-modal-close" onclick="closeAdminPackageFeaturesModal()" aria-label="Tutup">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
              </div>
              <div class="admin-pkg-features-body">
                <section class="admin-pkg-features-section" aria-labelledby="adminPkgParallelHeading">
                  <h5 class="admin-pkg-features-section-title" id="adminPkgParallelHeading">Proses paralel</h5>
                  <div class="admin-pkg-features-options">
                    <label class="admin-pkg-features-option">
                      <input type="radio" name="adminPkgParallelMode" value="off">
                      <span class="admin-pkg-features-option-text">
                        <span class="admin-pkg-features-option-label">Tidak tersedia</span>
                        <span class="admin-pkg-features-option-desc">1 file per waktu, tanpa proses paralel</span>
                      </span>
                    </label>
                    <label class="admin-pkg-features-option admin-pkg-features-option--limited">
                      <input type="radio" name="adminPkgParallelMode" value="limited">
                      <span class="admin-pkg-features-option-text">
                        <span class="admin-pkg-features-option-label">Maksimum</span>
                        <span class="admin-pkg-features-option-desc">Batasi jumlah file yang diproses bersamaan</span>
                      </span>
                      <span class="admin-pkg-parallel-max-wrap" onclick="event.preventDefault(); event.stopPropagation();">
                        <input class="admin-app-input admin-pkg-parallel-max" id="adminPkgParallelMaxInput" type="number" min="2" max="99" value="2" aria-label="Maksimum file paralel">
                        <span class="admin-pkg-parallel-max-suffix">file</span>
                      </span>
                    </label>
                    <label class="admin-pkg-features-option">
                      <input type="radio" name="adminPkgParallelMode" value="unlimited">
                      <span class="admin-pkg-features-option-text">
                        <span class="admin-pkg-features-option-label">Unlimited</span>
                        <span class="admin-pkg-features-option-desc">Input bebas di app (batas teknis 8 file)</span>
                      </span>
                    </label>
                  </div>
                </section>
                <section class="admin-pkg-features-section admin-pkg-features-section--uv" aria-labelledby="adminPkgUvHeading">
                  <h5 class="admin-pkg-features-section-title" id="adminPkgUvHeading">Preset aplikasi</h5>
                  <label class="admin-pkg-features-toggle-row">
                    <span class="admin-pkg-features-toggle-copy">
                      <span class="admin-pkg-features-option-label">Mode UV</span>
                      <span class="admin-pkg-features-option-desc">Preset UV (Base Putih + Varnish) di Quick Spot Color</span>
                    </span>
                    <span class="admin-pkg-features-toggle">
                      <input type="checkbox" id="adminPkgFeatureUv">
                      <span class="admin-pkg-features-toggle-ui" aria-hidden="true"></span>
                    </span>
                  </label>
                </section>
              </div>
              <div class="admin-pkg-features-foot">
                <button type="button" class="admin-app-btn-cancel" onclick="closeAdminPackageFeaturesModal()">Batal</button>
                <button type="button" class="admin-app-btn-save" onclick="saveAdminPackageFeaturesModal()">Simpan</button>
              </div>
            </div>
          </div>

          <div class="admin-app-footer">
            <button type="button" class="admin-app-btn-cancel" onclick="closeAdminApplicationModal()">Batal</button>
            <button type="submit" class="admin-app-btn-save" id="adminApplicationSubmitBtn">Simpan</button>
          </div>
        </form>
      </div>
    </div>
