    <div id="admin-overview" class="dash-page active">
      <div class="dash-header"><h1>Admin Dashboard</h1><p>Ringkasan keseluruhan sistem</p></div>
      <div class="stat-grid" style="grid-template-columns:repeat(4,1fr);">
        <div class="stat-card"><div class="stat-icon" style="background:rgba(56,126,245,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-1)" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg></div><div><div class="stat-label">Total Client</div><div class="stat-value">5</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(16,185,129,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="1.8" stroke-linecap="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div><div class="stat-label">Revenue</div><div class="stat-value">Rp 20.000.000</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(6,191,212,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-2)" stroke-width="1.8" stroke-linecap="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></div><div><div class="stat-label">Affiliate Aktif</div><div class="stat-value">2</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(245,166,35,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--amber)" stroke-width="1.8" stroke-linecap="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg></div><div><div class="stat-label">Lisensi Aktif</div><div class="stat-value">3</div></div></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:20px;">
        <div class="dash-table-wrap"><div class="dash-table-header"><h3>Client Terbaru</h3></div><table class="dash-table"><thead><tr><th>Nama</th><th>Paket</th><th>Status</th></tr></thead><tbody>
          <tr><td class="td-bold">PT Maju Jaya</td><td>Professional</td><td><span class="badge badge-active">Active</span></td></tr>
          <tr><td class="td-bold">CV Tekno Solusi</td><td>Enterprise</td><td><span class="badge badge-active">Active</span></td></tr>
          <tr><td class="td-bold">UD Sinar Abadi</td><td>Starter</td><td><span class="badge badge-trial">Trial</span></td></tr>
        </tbody></table></div>
        <div class="dash-table-wrap"><div class="dash-table-header"><h3>Komisi Pending</h3></div><table class="dash-table"><thead><tr><th>Affiliate</th><th>Jumlah</th><th>Status</th></tr></thead><tbody>
          <tr><td class="td-mono">AFF001</td><td>Rp 400.000</td><td><span class="badge badge-pending">Pending</span></td></tr>
          <tr><td class="td-mono">AFF003</td><td>Rp 200.000</td><td><span class="badge badge-pending">Pending</span></td></tr>
        </tbody></table></div>
      </div>
      <div class="dash-table-wrap"><div class="dash-table-header"><h3>Revenue Bulanan (2026)</h3></div><div style="padding:20px;">
        <div class="revenue-bars" id="revenueBars"></div>
      </div></div>
    </div>
