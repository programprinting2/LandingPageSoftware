    <div id="client-affiliate-overview" class="dash-page">
      <div class="dash-header"><h1 id="clientAffiliateOverviewTitle">Program Affiliate</h1><p id="clientAffiliateOverviewSubtitle">Ajak client baru dan dapatkan komisi</p></div>
      <div id="clientAffiliateEnrollCard" class="dash-form-card" hidden>
        <h3>Aktifkan program affiliate</h3>
        <p style="font-size:14px;color:var(--text-2);margin:0 0 16px;line-height:1.5;">Dapatkan link referral unik. Setiap pembelian melalui link Anda bisa menghasilkan komisi sesuai ketentuan produk.</p>
        <button type="button" class="btn btn-primary" id="clientAffiliateEnrollBtn" onclick="enrollClientAffiliate()">Aktifkan sekarang</button>
        <p id="clientAffiliateEnrollError" class="admin-app-label-hint" style="margin-top:12px;color:var(--rose);" hidden></p>
      </div>
      <div id="clientAffiliateDashboard">
      <div class="ref-link-card">
        <div style="font-size:13px;color:var(--text-2);">Link Referral Anda</div>
        <div class="ref-link-bar">
          <div class="ref-link-text" id="clientRefLinkText">—</div>
          <button type="button" class="btn btn-primary" style="padding:10px 18px;" onclick="copyClientRefLink()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 9h-9a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2zM5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            <span id="clientCopyBtnText">Salin</span>
          </button>
        </div>
      </div>
      <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card"><div class="stat-icon" style="background:rgba(56,126,245,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-1)" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg></div><div><div class="stat-label">Total Referral</div><div class="stat-value" id="clientAffiliateStatSales">—</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(16,185,129,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="1.8" stroke-linecap="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div><div class="stat-label">Total Komisi</div><div class="stat-value" id="clientAffiliateStatTotal">—</div></div></div>
        <div class="stat-card"><div class="stat-icon" style="background:rgba(245,166,35,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--amber)" stroke-width="1.8" stroke-linecap="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zM12 6v6l4 2"/></svg></div><div><div class="stat-label">Pending</div><div class="stat-value" id="clientAffiliateStatPending">—</div></div></div>
      </div>
      <div class="dash-table-wrap">
        <div class="dash-table-header">
          <h3>Daftar Referral</h3>
          <span id="clientAffiliateTableMeta" class="admin-app-label-hint"></span>
        </div>
        <table class="dash-table"><thead><tr><th>Client</th><th>Email</th><th>Aplikasi</th><th>Paket</th><th>Nilai</th><th>Komisi</th><th>Tanggal</th><th>Status</th></tr></thead><tbody id="clientAffiliateReferralsBody">
        <tr><td colspan="8" style="text-align:center;color:var(--text-3);padding:24px;">Memuat…</td></tr>
      </tbody></table>
        <div id="clientAffiliatePagination" class="dash-pagination" hidden></div>
      </div>
      </div>
    </div>
