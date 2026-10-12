<div class="modal-overlay" id="authModal">
  <div class="modal-box auth-modal-box">
    <button class="modal-close" type="button" onclick="closeModal()" aria-label="Tutup">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>
    <div class="auth-modal-brand">
      <div class="auth-modal-brand-title">{{ $site['name'] ?? 'Quick Spot Color' }}</div>
      <p class="auth-modal-brand-sub">Masuk atau daftar untuk melanjutkan</p>
    </div>
    <div class="modal-tabs">
      <button type="button" class="modal-tab active" id="tabLogin" onclick="switchTab('login')">Masuk</button>
      <button type="button" class="modal-tab" id="tabRegister" onclick="switchTab('register')">Daftar</button>
    </div>

    <div id="authAlert" class="auth-alert" style="display:none;"></div>

    <div id="verificationResendWrap" class="auth-verification-resend" hidden>
      <p>Belum menerima email? Kirim ulang link ke <strong id="verificationResendEmail"></strong></p>
      <button type="button" class="btn btn-secondary btn-full auth-verification-btn" id="verificationResendBtn" onclick="resendVerificationEmail()">Kirim Ulang Link Verifikasi</button>
    </div>

    <form id="loginForm" onsubmit="submitClientLogin(event)">
      <div class="role-selector">
        <button type="button" class="role-btn active" onclick="selectRole(this,'login')">Client</button>
        <button type="button" class="role-btn" onclick="selectRole(this,'login')">Affiliate</button>
        <button type="button" class="role-btn" onclick="selectRole(this,'login')">Admin</button>
      </div>
      <div class="form-group">
        <label class="form-label" for="loginEmail">Email</label>
        <div class="form-input-wrap">
          <input class="form-input" id="loginEmail" type="email" placeholder="email@example.com" autocomplete="email" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="loginPassword">Password</label>
        <div class="form-input-wrap">
          <input class="form-input" id="loginPassword" type="password" placeholder="Password" autocomplete="current-password" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full auth-submit-btn" id="loginBtn">Masuk</button>
    </form>

    <form id="registerForm" style="display:none;" onsubmit="submitClientRegistration(event)">
      <div class="role-selector">
        <button type="button" class="role-btn active" onclick="selectRole(this,'register')">Client</button>
        <button type="button" class="role-btn" onclick="selectRole(this,'register')">Affiliate</button>
      </div>
      <div class="form-group">
        <label class="form-label" for="registerName">Nama Lengkap</label>
        <div class="form-input-wrap">
          <input class="form-input" id="registerName" name="name" type="text" placeholder="Nama Anda" autocomplete="name" required>
        </div>
      </div>
      <div class="form-group" id="companyField">
        <label class="form-label" for="registerCompany">Nama Perusahaan</label>
        <div class="form-input-wrap">
          <input class="form-input" id="registerCompany" name="company_name" type="text" placeholder="PT / CV / UD" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="registerEmail">Email</label>
        <div class="form-input-wrap">
          <input class="form-input" id="registerEmail" name="email" type="email" placeholder="email@example.com" autocomplete="email" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="registerWhatsapp">No. WhatsApp</label>
        <div class="form-input-wrap">
          <input class="form-input" id="registerWhatsapp" name="whatsapp" type="text" placeholder="08xxxxxxxxxx" autocomplete="tel" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="registerPassword">Password</label>
        <div class="form-input-wrap">
          <input class="form-input" id="registerPassword" name="password" type="password" placeholder="Min. 8 karakter" autocomplete="new-password" minlength="8" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full auth-submit-btn" id="registerBtn">Daftar Sekarang</button>
    </form>
  </div>
</div>
