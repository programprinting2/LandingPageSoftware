    <div id="client-overview" class="dash-page active client-overview-page">
      <div class="dash-header">
        <h1 id="clientOverviewTitle">Selamat Datang</h1>
        <p>Ringkasan aplikasi pada akun Anda</p>
      </div>

      <div class="stat-grid client-overview-top-stats">
        <div class="stat-card">
          <div class="stat-icon" style="background:rgba(16,185,129,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="1.8" stroke-linecap="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg></div>
          <div>
            <div class="stat-label">Lisensi aktif</div>
            <div class="stat-value" id="clientOverviewStatActive">—</div>
            <div class="client-overview-stat-hint" id="clientOverviewStatActiveHint"></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:rgba(56,126,245,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-1)" stroke-width="1.8" stroke-linecap="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg></div>
          <div>
            <div class="stat-label">Perangkat aktif</div>
            <div class="stat-value" id="clientOverviewStatDevices">—</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:rgba(244,63,94,0.1)"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--rose)" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg></div>
          <div>
            <div class="stat-label">Lisensi expired</div>
            <div class="stat-value" id="clientOverviewStatExpired">—</div>
          </div>
        </div>
      </div>

      <div class="client-overview-section client-overview-apps-block">
        <div class="client-overview-section-head">
          <div>
            <h3>Aplikasi</h3>
          </div>
          <button type="button" class="btn btn-secondary client-overview-link-btn" onclick="switchDashPage('client','license',document.querySelector('#dashClient .dash-nav-item[onclick*=license]'))">Kelola lisensi</button>
        </div>
        <div id="clientOverviewAppsGrid" class="client-overview-apps-grid">
          <div class="client-overview-loading">Memuat…</div>
        </div>
      </div>
    </div>
