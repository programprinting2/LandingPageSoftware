    <div id="admin-licenses" class="dash-page">
      <div class="dash-header"><h1>Manajemen Lisensi</h1><p>Renew = aktifkan lagi lisensi expired. Revoke = hentikan lisensi yang masih aktif. Lepas perangkat = hapus slot aktivasi di software. Ganti paket = ubah tier lisensi tanpa pembayaran client (masa berlaku dihitung ulang).</p></div>
      <div class="dash-table-wrap">
        <div class="dash-table-header dash-table-header--toolbar">
          <h3 id="adminLicensesCount">Memuat…</h3>
          <div class="admin-invoice-toolbar admin-licenses-toolbar">
            <div class="admin-toolbar-field admin-toolbar-field--grow">
              <label class="admin-toolbar-label" for="adminLicenseSearch">Cari</label>
              <div class="admin-search-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <input type="search" id="adminLicenseSearch" class="admin-invoice-search admin-licenses-search" placeholder="Client, email, aplikasi, paket…" autocomplete="off">
              </div>
            </div>
            <div class="admin-toolbar-field admin-toolbar-field--filter">
              <label class="admin-toolbar-label" for="adminLicenseApplicationFilter">Aplikasi</label>
              <select id="adminLicenseApplicationFilter" class="admin-invoice-filter" onchange="onAdminLicenseApplicationFilterChange()">
                <option value="">Semua aplikasi</option>
              </select>
            </div>
          </div>
        </div>
        <table class="dash-table admin-licenses-table">
          <thead>
            <tr>
              <th>License Key</th>
              <th>Client</th>
              <th>Aplikasi</th>
              <th>Paket</th>
              <th>Status</th>
              <th>Berlaku Hingga</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="adminLicensesTableBody">
            <tr><td colspan="7" style="text-align:center;color:var(--text-3);padding:28px;">Memuat…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal-overlay admin-app-overlay" id="adminLicensePackageModal" hidden aria-hidden="true">
      <div class="admin-app-modal admin-license-package-modal">
        <div class="admin-app-modal-header">
          <div>
            <h3 id="adminLicensePackageModalTitle">Ganti paket</h3>
            <p id="adminLicensePackageModalSubtitle">—</p>
          </div>
          <button type="button" class="admin-app-modal-close" onclick="closeAdminLicensePackageModal()" aria-label="Tutup">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
          </button>
        </div>
        <div id="adminLicensePackageAlert" class="admin-app-alert" hidden></div>
        <div class="admin-license-package-body">
          <p class="admin-app-label-hint" style="margin:0 0 12px;">Pilih paket baru. Masa berlaku dan kuota perangkat disesuaikan dengan paket (tanpa QRIS).</p>
          <div id="adminLicensePackageOptions" class="admin-app-checklist admin-app-checklist-light">Memuat paket…</div>
        </div>
        <div class="admin-app-footer">
          <button type="button" class="admin-app-btn-cancel" onclick="closeAdminLicensePackageModal()">Batal</button>
          <button type="button" class="admin-app-btn-save" id="adminLicensePackageSaveBtn" onclick="submitAdminLicensePackageChange()">Simpan</button>
        </div>
      </div>
    </div>
