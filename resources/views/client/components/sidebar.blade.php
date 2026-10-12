  <aside class="dash-sidebar">
    <div class="dash-sidebar-brand"><div class="dash-sidebar-brand-title">{{ $site['name'] ?? 'Quick Spot Color' }}</div><span>Client Portal</span></div>
    <div class="dash-nav">
      <button class="dash-nav-item active" onclick="switchDashPage('client','overview',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg> Dashboard
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('client','license',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg> Lisensi Saya
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('client','downloads',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg> Download
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('client','billing',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> Pembayaran
      </button>
      <button class="dash-nav-item" onclick="switchDashPage('client','settings',this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg> Pengaturan
      </button>
    </div>
    <div class="dash-sidebar-footer"><button class="dash-logout" onclick="doLogout()">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg> Keluar
    </button></div>
  </aside>
