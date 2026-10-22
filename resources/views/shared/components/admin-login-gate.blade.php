<div id="adminLoginGate" class="admin-login-gate">
  <div class="admin-login-card auth-modal-box">
    <div class="auth-modal-brand">
      <div class="auth-modal-brand-title">{{ $site['name'] ?? 'Quick Spot Color' }}</div>
      <p class="auth-modal-brand-sub">Masuk ke Admin Panel</p>
    </div>
    <div id="adminLoginAlert" class="auth-alert" style="display:none;"></div>
    <form id="adminLoginForm" onsubmit="submitAdminLogin(event)">
      <div class="form-group">
        <label class="form-label" for="adminLoginEmail">Email</label>
        <div class="form-input-wrap">
          <input class="form-input" id="adminLoginEmail" type="email" placeholder="email@example.com" autocomplete="email" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="adminLoginPassword">Password</label>
        <div class="form-input-wrap">
          <input class="form-input" id="adminLoginPassword" type="password" placeholder="Password" autocomplete="current-password" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full auth-submit-btn" id="adminLoginBtn">Masuk Admin</button>
    </form>
  </div>
</div>
