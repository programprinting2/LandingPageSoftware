<div class="modal-overlay" id="clientLicenseDeviceSlotsModal" hidden aria-hidden="true">
  <div class="modal-box client-license-device-slots-modal-box">
    <button class="modal-close" type="button" onclick="closeClientLicenseDeviceSlotsModal()" aria-label="Tutup">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>
    <div class="client-license-device-slots-head">
      <h2 id="clientLicenseDeviceSlotsTitle">Paket</h2>
      <p id="clientLicenseDeviceSlotsSubtitle"></p>
    </div>
    <div id="clientLicenseDeviceSlotsAlert" class="auth-alert" style="display:none;"></div>
    <div class="client-license-device-slots-body">
      <div class="client-license-device-slots-summary" id="clientLicenseDeviceSlotsSummary" aria-live="polite"></div>

      <div class="client-license-device-slots-voucher">
        <label class="client-license-device-slots-field-label" for="clientLicenseDeviceSlotsReferral">Kode voucher</label>
        <div class="client-license-device-slots-voucher-row">
          <input type="text" class="client-license-device-slots-voucher-input" id="clientLicenseDeviceSlotsReferral" name="referral_code" maxlength="6" placeholder="Opsional" autocomplete="off" spellcheck="false">
          <button type="button" class="btn btn-secondary client-license-device-slots-voucher-apply" id="clientLicenseDeviceSlotsReferralApplyBtn">Terapkan</button>
        </div>
        <p id="clientLicenseDeviceSlotsReferralStatus" class="client-license-device-slots-referral-status" role="status"></p>
      </div>

      <div class="client-license-device-slots-device-block" id="clientLicenseDeviceSlotsDeviceBlock">
        <label class="client-license-device-slots-field-label" id="clientLicenseDeviceSlotsDeviceLabel" for="clientLicenseDeviceSlotsInput">Jumlah device</label>
        <div class="client-license-device-slots-stepper">
          <button type="button" class="btn btn-secondary client-license-device-slots-step-btn" id="clientLicenseDeviceSlotsDec" aria-label="Kurangi">−</button>
          <input type="number" class="client-license-device-slots-input" id="clientLicenseDeviceSlotsInput" min="1" max="99" value="1" aria-labelledby="clientLicenseDeviceSlotsDeviceLabel" aria-describedby="clientLicenseDeviceSlotsLimitInfo">
          <button type="button" class="btn btn-secondary client-license-device-slots-step-btn" id="clientLicenseDeviceSlotsInc" aria-label="Tambah">+</button>
        </div>
        <p class="client-license-device-slots-limit-info" id="clientLicenseDeviceSlotsLimitInfo" hidden role="status"></p>
        <p class="client-license-device-slots-footnote" id="clientLicenseDeviceSlotsFootnote">
          Slot device tambahan mengikuti durasi paket aktif dan tidak berlaku lagi saat paket expired atau di-upgrade.
        </p>
      </div>
    </div>
    <div class="client-license-device-slots-foot">
      <button type="button" class="btn btn-secondary" onclick="closeClientLicenseDeviceSlotsModal()">Batal</button>
      <button type="button" class="btn btn-primary" id="clientLicenseDeviceSlotsConfirmBtn">Lanjut ke pembayaran</button>
    </div>
  </div>
</div>
