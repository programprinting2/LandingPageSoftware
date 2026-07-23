    <div id="client-license" class="dash-page">

      <div class="dash-header" style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">

        <div><h1>Lisensi Saya</h1><p>Daftar aplikasi yang dibeli dan perangkat aktif</p></div>

        <button type="button" class="btn btn-secondary" onclick="loadClientLicense()" style="flex-shrink:0;margin-top:4px;">Muat ulang</button>

      </div>

      <div id="clientLicenseSummary" class="glass" style="padding:20px 24px;margin-bottom:20px;">

        <div style="font-size:13px;color:var(--text-muted);margin-bottom:6px;">Kuota perangkat</div>

        <div id="clientLicenseQuota" style="font-size:22px;font-weight:800;">—</div>

        <div id="clientLicenseHint" style="font-size:13px;color:var(--text-muted);margin-top:8px;">Memuat data lisensi…</div>

      </div>

      <div class="dash-table-wrap"><table class="dash-table"><thead><tr><th>Aplikasi</th><th>IP</th><th>Komputer</th><th>Platform</th><th>Diaktifkan</th><th>Status</th><th>Aksi</th></tr></thead><tbody id="clientLicenseDevices">

        <tr><td colspan="7" style="text-align:center;color:var(--text-muted);">Memuat…</td></tr>

      </tbody></table></div>

    </div>
