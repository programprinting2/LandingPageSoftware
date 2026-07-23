    <div id="affiliate-overview" class="dash-page active">
      <div class="dash-header"><h1>Halo, Rina Susanti!</h1><p>Pantau performa affiliate Anda</p></div>
      <div class="ref-link-card">
        <div style="font-size:13px;color:var(--text-2);">Link Referral Anda</div>
        <div class="ref-link-bar">
          <div class="ref-link-text" id="refLinkText">{{ $landingData['referralUrl'] ?? url('/?ref=AFF001') }}</div>
          <button class="btn btn-primary" style="padding:10px 18px;" onclick="copyRefLink()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 9h-9a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2zM5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            <span id="copyBtnText">Salin</span>
          </button>
        </div>
      </div>
      <div class="stat-grid" style="grid-template-columns:repeat(4,1fr);">
        <div class="stat-card"><div class="stat-icon" style="background:rgba(56,126,245,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-1)" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg></div><div><div class="stat-label">Total Referral</div><div class="stat-value">12</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(16,185,129,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="1.8" stroke-linecap="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div><div class="stat-label">Total Komisi</div><div class="stat-value">Rp 4.800.000</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(245,166,35,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--amber)" stroke-width="1.8" stroke-linecap="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zM12 6v6l4 2"/></svg></div><div><div class="stat-label">Pending</div><div class="stat-value">Rp 1.200.000</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(6,191,212,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-2)" stroke-width="1.8" stroke-linecap="round"><path d="M18 20V10M12 20V4M6 20v-6"/></svg></div><div><div class="stat-label">Konversi</div><div class="stat-value">15%</div></div></div>
      </div>
      <div class="dash-table-wrap"><div class="dash-table-header"><h3>Referral Terbaru</h3></div><table class="dash-table"><thead><tr><th>Client</th><th>Paket</th><th>Komisi</th><th>Tanggal</th><th>Status</th></tr></thead><tbody>
        <tr><td class="td-bold">PT Maju Jaya</td><td>Professional</td><td class="td-green">Rp 400.000</td><td>15 Jul 2026</td><td><span class="badge badge-paid">Paid</span></td></tr>
        <tr><td class="td-bold">PT Karya Mandiri</td><td>Professional</td><td class="td-amber">Rp 400.000</td><td>10 Jul 2026</td><td><span class="badge badge-pending">Pending</span></td></tr>
      </tbody></table></div>
    </div>
