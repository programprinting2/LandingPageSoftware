    <div id="admin-commissions" class="dash-page">
      <div class="dash-header"><h1>Komisi Affiliate</h1><p>Kelola pembayaran komisi</p></div>
      <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card"><div class="stat-icon" style="background:rgba(16,185,129,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="1.8" stroke-linecap="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div><div class="stat-label">Total Dibayar</div><div class="stat-value" id="adminCommissionsTotalPaid">—</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(245,166,35,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--amber)" stroke-width="1.8" stroke-linecap="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zM12 6v6l4 2"/></svg></div><div><div class="stat-label">Menunggu Bayar</div><div class="stat-value" id="adminCommissionsPending">—</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(56,126,245,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-1)" stroke-width="1.8" stroke-linecap="round"><path d="M18 20V10M12 20V4M6 20v-6"/></svg></div><div><div class="stat-label">Total Transaksi</div><div class="stat-value" id="adminCommissionsTotalCount">—</div></div></div>
      </div>
      <div class="dash-table-wrap">
        <div class="dash-table-header">
          <h3 id="adminCommissionsCount">Memuat komisi...</h3>
        </div>
        <table class="dash-table">
          <thead>
            <tr>
              <th>Affiliate</th>
              <th>Client</th>
              <th>Paket</th>
              <th>Komisi</th>
              <th>Tanggal</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="adminCommissionsTableBody">
            <tr><td colspan="7" style="text-align:center;color:var(--text-3);padding:24px;">Memuat data...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
