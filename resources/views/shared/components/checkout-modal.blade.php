<div class="modal-overlay" id="checkoutModal" hidden>
  <div class="modal-box checkout-modal-box">
    <button class="modal-close" type="button" onclick="closeCheckoutModal()" aria-label="Tutup">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>
    <div class="checkout-modal-head">
      <h2 id="checkoutModalTitle">Checkout</h2>
      <p id="checkoutModalSubtitle">Selesaikan pembayaran untuk mengaktifkan paket</p>
    </div>
    <div id="checkoutAlert" class="auth-alert" style="display:none;"></div>
    <div id="checkoutBody" class="checkout-modal-body">
      <div class="checkout-summary">
        <div><span class="checkout-label">Paket</span><strong id="checkoutPackageName">—</strong></div>
        <div><span class="checkout-label">Total</span><strong id="checkoutAmountLabel">—</strong></div>
        <div><span class="checkout-label">Berlaku hingga</span><strong id="checkoutExpiresLabel">—</strong></div>
      </div>
      <div id="checkoutPaymentActions" class="checkout-actions">
        <a id="checkoutPaymentLink" class="btn btn-primary btn-full" href="#" target="_blank" rel="noopener noreferrer" hidden>Buka halaman pembayaran QRIS</a>
        <button type="button" id="checkoutSimulateBtn" class="btn btn-secondary btn-full" hidden onclick="simulateClientCheckout()">Simulasikan pembayaran (sandbox)</button>
      </div>
      <p id="checkoutHint" class="checkout-hint">Setelah bayar, lisensi diperbarui otomatis. Anda bisa menutup modal ini dan cek status di dashboard.</p>
      <div id="checkoutPaidSuccess" class="checkout-paid" hidden>
        <p><strong>Pembayaran berhasil.</strong> Paket Anda sudah aktif.</p>
        <button type="button" class="btn btn-primary btn-full" onclick="goToClientDashboard(); closeCheckoutModal();">Ke dashboard</button>
      </div>
    </div>
  </div>
</div>
