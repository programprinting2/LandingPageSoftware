  <aside class="dash-sidebar">
    <div class="dash-sidebar-brand"><div class="dash-sidebar-brand-title">{{ $site['name'] ?? 'Quick Spot Color' }}</div><span>Affiliate Portal</span></div>
    <div class="dash-nav">
      <button class="dash-nav-item active" onclick="switchDashPage('affiliate','overview',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg> Dashboard
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('affiliate','referrals',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg> Referral Saya
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('affiliate','commissions',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> Komisi
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('affiliate','settings',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg> Pengaturan
      </button>
    </div>
    <div class="dash-sidebar-footer"><button class="dash-logout" onclick="doLogout()">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg> Keluar
    </button></div>
  </aside>
