<div class="modal-overlay" id="authModal" hidden aria-hidden="true">
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

    <div id="authGoogleWrap" class="auth-google-wrap" hidden>
      <button type="button" class="btn btn-google btn-full" onclick="startGoogleAuth()">
        <span class="btn-google-icon" aria-hidden="true">
          <svg width="20" height="20" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
          </svg>
        </span>
        Masuk dengan akun Google
      </button>
      <div class="auth-divider" aria-hidden="true"><span>atau</span></div>
    </div>

    <div id="verificationResendWrap" class="auth-verification-resend" hidden>
      <p>Belum menerima email? Kirim ulang link ke <strong id="verificationResendEmail"></strong></p>
      <button type="button" class="btn btn-secondary btn-full auth-verification-btn" id="verificationResendBtn" onclick="resendVerificationEmail()">Kirim Ulang Link Verifikasi</button>
    </div>

    <form id="loginForm" onsubmit="submitClientLogin(event)">
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
      <div class="form-group">
        <label class="form-label" for="registerName">Nama akun</label>
        <div class="form-input-wrap">
          <input class="form-input" id="registerName" name="name" type="text" placeholder="Nama akun" autocomplete="name" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="registerEmail">Email</label>
        <div class="form-input-wrap">
          <input class="form-input" id="registerEmail" name="email" type="email" placeholder="email@example.com" autocomplete="email" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="registerWhatsapp">No. WhatsApp <span class="form-label-optional">(opsional)</span></label>
        <div class="form-input-wrap">
          <input class="form-input" id="registerWhatsapp" name="whatsapp" type="text" placeholder="08xxxxxxxxxx" autocomplete="tel">
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
