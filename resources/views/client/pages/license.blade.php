    <div id="client-license" class="dash-page client-license-page">

      <div class="dash-header client-license-header">
        <div>
          <h1>Lisensi Saya</h1>
          <p>Ringkasan paket yang dibeli per aplikasi, masa berlaku, dan perangkat yang teraktivasi.</p>
        </div>
        <button type="button" class="btn btn-secondary client-license-reload" onclick="loadClientLicense({ force: true })">Muat ulang</button>
      </div>

      <div id="clientLicenseApps" class="client-license-apps" aria-live="polite">
        <div class="client-license-loading">Memuat…</div>
      </div>

    </div>
