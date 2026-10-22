<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Google — Quick Spot Color</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 420px; margin: 48px auto; padding: 0 20px; color: #1f1f1f; line-height: 1.5; }
    h1 { font-size: 1.25rem; }
    p { color: #444; }
    .ok { color: #059669; font-weight: 600; }
  </style>
</head>
<body>
  <h1>Login berhasil</h1>
  <p class="ok">{{ $message ?? 'Anda sudah masuk dengan Google.' }}</p>
  <p>Quick Spot Color akan terbuka otomatis. Jika tidak, kembali ke aplikasi — login akan diselesaikan di sana.</p>
  <p>Tab browser ini boleh ditutup.</p>
  @if(!empty($deepLink))
  <script>
    window.location.href = @json($deepLink);
  </script>
  @endif
</body>
</html>
