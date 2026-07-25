    <div id="admin-settings" class="dash-page">
      <div class="dash-header"><h1>Pengaturan Sistem</h1><p>Konfigurasi email, komisi, dan lisensi</p></div>

      <div class="dash-tabs admin-settings-tabs">
        <button type="button" class="dash-tab active" data-admin-settings-tab="email" onclick="switchAdminSettingsTab('email', this)">Email</button>
        <button type="button" class="dash-tab" data-admin-settings-tab="commission" onclick="switchAdminSettingsTab('commission', this)">Komisi</button>
        <button type="button" class="dash-tab" data-admin-settings-tab="license" onclick="switchAdminSettingsTab('license', this)">Lisensi</button>
      </div>

      <div id="adminSettingsEmailPanel" class="admin-settings-panel">
        <div id="adminMailSettingsAlert" class="admin-app-alert" hidden style="margin-bottom:16px;"></div>

        <div class="dash-form-card admin-settings-card">
          <div class="admin-settings-panel-head">
            <div>
              <h3>Pengaturan Email</h3>
              <p>Konfigurasi SMTP untuk email verifikasi client dan notifikasi lainnya.</p>
            </div>
            <div class="admin-settings-panel-actions">
              <button type="button" class="btn btn-secondary btn-sm" onclick="applyGmailMailPreset()">Preset Gmail</button>
              <span id="adminMailSettingsSource" class="badge badge-trial">Memuat…</span>
            </div>
          </div>

          <div class="mail-settings-help">
            <strong>Gmail:</strong> aktifkan 2-Step Verification → buat <em>App Password</em> di Google Account → isi Username &amp; Password SMTP di bawah. Host: <code>smtp.gmail.com</code>, Port: <code>587</code>, Enkripsi: <code>TLS</code>.
          </div>

          <div class="mail-settings-section">
            <h4 class="mail-settings-section-title">Umum</h4>
            <div class="admin-app-form-grid">
              <div class="form-group">
                <label class="admin-app-label">Driver Email</label>
                <select class="admin-app-input" id="mailSettingsMailer" onchange="toggleMailSettingsSmtpFields()">
                  <option value="log">Log (development — tulis ke laravel.log)</option>
                  <option value="smtp">SMTP (Gmail / Google Workspace / lainnya)</option>
                  <option value="sendmail">Sendmail</option>
                </select>
                <small class="mail-settings-hint" id="mailSettingsDriverHint">Mode development: email tidak dikirim, hanya dicatat di log.</small>
              </div>
              <div class="form-group">
                <label class="admin-app-label">URL Aplikasi (APP_URL)</label>
                <input class="admin-app-input" id="mailSettingsAppUrl" type="url" placeholder="https://domain-anda.com">
                <small class="mail-settings-hint">Digunakan untuk link verifikasi email client. Wajib HTTPS di production.</small>
              </div>
            </div>
          </div>

          <div class="mail-settings-section" id="mailSettingsSmtpFields">
            <h4 class="mail-settings-section-title">Konfigurasi SMTP</h4>
            <p class="mail-settings-section-desc">Diperlukan jika Driver Email = SMTP. Untuk Gmail, klik <strong>Preset Gmail</strong> lalu isi Username &amp; App Password.</p>
            <div class="admin-app-form-grid">
              <div class="form-group">
                <label class="admin-app-label">Host SMTP</label>
                <input class="admin-app-input mail-settings-smtp-input" id="mailSettingsHost" type="text" placeholder="smtp.gmail.com">
              </div>
              <div class="form-group">
                <label class="admin-app-label">Port</label>
                <input class="admin-app-input mail-settings-smtp-input" id="mailSettingsPort" type="number" min="1" max="65535" placeholder="587">
              </div>
              <div class="form-group">
                <label class="admin-app-label">Enkripsi</label>
                <select class="admin-app-input mail-settings-smtp-input" id="mailSettingsEncryption">
                  <option value="">Tidak ada</option>
                  <option value="tls">TLS (Gmail: 587)</option>
                  <option value="ssl">SSL (port 465)</option>
                </select>
              </div>
              <div class="form-group">
                <label class="admin-app-label">Username SMTP</label>
                <input class="admin-app-input mail-settings-smtp-input" id="mailSettingsUsername" type="text" placeholder="emailanda@gmail.com">
                <small class="mail-settings-hint">Email Gmail lengkap (sama dengan Email Pengirim).</small>
              </div>
            </div>
            <div class="form-group" style="margin-top:14px;">
              <label class="admin-app-label">Password SMTP <span id="mailSettingsPasswordHint" class="mail-settings-hint" style="font-weight:400;"></span></label>
              <input class="admin-app-input mail-settings-smtp-input" id="mailSettingsPassword" type="password" placeholder="App Password Google (16 karakter)" autocomplete="new-password">
              <small class="mail-settings-hint">Bukan password login Gmail — gunakan App Password dari Google Account.</small>
            </div>
          </div>

          <div class="mail-settings-section">
            <h4 class="mail-settings-section-title">Pengirim &amp; Verifikasi</h4>
            <div class="admin-app-form-grid">
              <div class="form-group">
                <label class="admin-app-label">Email Pengirim (From)</label>
                <input class="admin-app-input" id="mailSettingsFromAddress" type="email" placeholder="emailanda@gmail.com">
                <small class="mail-settings-hint">Disarankan sama dengan Username SMTP untuk Gmail.</small>
              </div>
              <div class="form-group">
                <label class="admin-app-label">Nama Pengirim (From Name)</label>
                <input class="admin-app-input" id="mailSettingsFromName" type="text" placeholder="SoftSale">
              </div>
              <div class="form-group">
                <label class="admin-app-label">Masa Berlaku Link Verifikasi (menit)</label>
                <input class="admin-app-input" id="mailSettingsVerificationExpire" type="number" min="5" max="10080" placeholder="60">
                <small class="mail-settings-hint">Default 60 menit. Link konfirmasi email client expired setelah waktu ini.</small>
              </div>
            </div>
          </div>

          <div class="admin-settings-actions">
            <button type="button" class="btn btn-primary" id="mailSettingsSaveBtn" onclick="saveAdminMailSettings()">Simpan Pengaturan Email</button>
          </div>

          <div class="admin-settings-subsection">
            <h4>Tes Kirim Email</h4>
            <p>Simpan pengaturan terlebih dahulu, lalu kirim email percobaan.</p>
            <div class="admin-settings-inline-form">
              <input class="admin-app-input" id="mailSettingsTestEmail" type="email" placeholder="email@example.com">
              <button type="button" class="btn btn-secondary" id="mailSettingsTestBtn" onclick="sendAdminMailSettingsTest()">Kirim Email Tes</button>
            </div>
          </div>
        </div>
      </div>

      <div id="adminSettingsCommissionPanel" class="admin-settings-panel" hidden>
        <div class="dash-form-card admin-settings-card">
          <div class="admin-settings-panel-head">
            <div>
              <h3>Pengaturan Komisi</h3>
              <p>Atur persentase komisi affiliate, durasi cookie tracking, dan batas minimum penarikan.</p>
            </div>
          </div>

          <div class="admin-app-form-grid">
            <div class="form-group">
              <label class="admin-app-label">Persentase Komisi (%)</label>
              <input class="admin-app-input" type="number" min="0" max="100" value="20">
            </div>
            <div class="form-group">
              <label class="admin-app-label">Cookie Tracking (hari)</label>
              <input class="admin-app-input" type="number" min="1" max="365" value="30">
            </div>
            <div class="form-group">
              <label class="admin-app-label">Minimum Withdrawal (Rp)</label>
              <input class="admin-app-input" type="number" min="0" step="1000" value="500000">
            </div>
          </div>

          <div class="admin-settings-actions">
            <button type="button" class="btn btn-primary">Simpan Pengaturan Komisi</button>
          </div>
        </div>
      </div>

      <div id="adminSettingsLicensePanel" class="admin-settings-panel" hidden>
        <div class="dash-form-card admin-settings-card">
          <div class="admin-settings-panel-head">
            <div>
              <h3>Pengaturan Lisensi</h3>
              <p>Format license key, durasi trial, dan batas perangkat per lisensi.</p>
            </div>
          </div>

          <div class="admin-app-form-grid">
            <div class="form-group">
              <label class="admin-app-label">Format License Key</label>
              <input class="admin-app-input" type="text" value="{PLAN}-{YEAR}-{RANDOM4}" style="font-family:var(--mono);font-size:13px;">
              <small class="mail-settings-hint">Placeholder: <code>{PLAN}</code>, <code>{YEAR}</code>, <code>{RANDOM4}</code></small>
            </div>
            <div class="form-group">
              <label class="admin-app-label">Durasi Trial (hari)</label>
              <input class="admin-app-input" type="number" min="0" max="365" value="14">
            </div>
            <div class="form-group">
              <label class="admin-app-label">Max Device per License</label>
              <input class="admin-app-input" type="number" min="1" max="99" value="3">
            </div>
          </div>

          <div class="admin-settings-actions">
            <button type="button" class="btn btn-primary">Simpan Pengaturan Lisensi</button>
          </div>
        </div>
      </div>
    </div>
