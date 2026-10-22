    <div id="admin-licenses" class="dash-page">
      <div class="dash-header"><h1>Manajemen Lisensi</h1><p>Renew = aktifkan lagi lisensi expired. Revoke = hentikan lisensi yang masih aktif. Lepas perangkat = hapus slot aktivasi di software (tanpa mengubah status lisensi). Ganti paket manual lewat Edit Client → Aplikasi &amp; Lisensi.</p></div>
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
        <table class="dash-table">
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
