<div class="modal-overlay" id="authModal">
  <div class="glass modal-box">
    <button class="modal-close" onclick="closeModal()">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>
    <div style="text-align:center; margin-bottom:24px;">
      <div class="gradient-text" style="font-size:24px; font-weight:800;">SoftSale</div>
    </div>
    <div class="modal-tabs">
      <button class="modal-tab active" id="tabLogin" onclick="switchTab('login')">Masuk</button>
      <button class="modal-tab" id="tabRegister" onclick="switchTab('register')">Daftar</button>
    </div>

    <div id="authAlert" style="display:none; margin-bottom:14px; padding:12px 14px; border-radius:10px; font-size:13px;"></div>

    <div id="verificationResendWrap" hidden style="margin-bottom:14px; padding:12px 14px; border-radius:10px; background:rgba(245,166,35,0.08); border:1px solid rgba(245,166,35,0.2);">
      <p style="font-size:12px; color:var(--text-2); margin-bottom:8px;">Belum menerima email? Kirim ulang link ke <strong id="verificationResendEmail" style="color:var(--text-1);"></strong></p>
      <button type="button" class="btn btn-secondary btn-full" id="verificationResendBtn" onclick="resendVerificationEmail()" style="padding:8px 14px; font-size:12px;">Kirim Ulang Link Verifikasi</button>
    </div>

    <!-- LOGIN FORM -->
    <form id="loginForm" onsubmit="submitClientLogin(event)">
      <div class="role-selector">
        <button type="button" class="role-btn active" onclick="selectRole(this,'login')">Client</button>
        <button type="button" class="role-btn" onclick="selectRole(this,'login')">Affiliate</button>
        <button type="button" class="role-btn" onclick="selectRole(this,'login')">Admin</button>
      </div>
      <div class="form-group">
        <label class="form-label" for="loginEmail">Email</label>
        <div class="form-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.8" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zM22 6l-10 7L2 6"/></svg>
          <input class="form-input" id="loginEmail" type="email" placeholder="email@example.com" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="loginPassword">Password</label>
        <div class="form-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.8" stroke-linecap="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
          <input class="form-input" id="loginPassword" type="password" placeholder="Password" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full" id="loginBtn" style="margin-top:6px;">Masuk</button>
    </form>

    <!-- REGISTER FORM -->
    <form id="registerForm" style="display:none;" onsubmit="submitClientRegistration(event)">
      <div class="role-selector">
        <button type="button" class="role-btn active" onclick="selectRole(this,'register')">Client</button>
        <button type="button" class="role-btn" onclick="selectRole(this,'register')">Affiliate</button>
      </div>
      <div class="form-group">
        <label class="form-label" for="registerName">Nama Lengkap</label>
        <div class="form-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg>
          <input class="form-input" id="registerName" name="name" type="text" placeholder="Nama Anda" required>
        </div>
      </div>
      <div class="form-group" id="companyField">
        <label class="form-label" for="registerCompany">Nama Perusahaan</label>
        <div class="form-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.8" stroke-linecap="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
          <input class="form-input" id="registerCompany" name="company_name" type="text" placeholder="PT / CV / UD" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="registerEmail">Email</label>
        <div class="form-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.8" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zM22 6l-10 7L2 6"/></svg>
          <input class="form-input" id="registerEmail" name="email" type="email" placeholder="email@example.com" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="registerWhatsapp">No. WhatsApp</label>
        <div class="form-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.8" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          <input class="form-input" id="registerWhatsapp" name="whatsapp" type="text" placeholder="08xxxxxxxxxx" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="registerPassword">Password</label>
        <div class="form-input-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.8" stroke-linecap="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
          <input class="form-input" id="registerPassword" name="password" type="password" placeholder="Min. 8 karakter" minlength="8" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full" id="registerBtn" style="margin-top:6px;">Daftar Sekarang</button>
    </form>
  </div>
</div>
