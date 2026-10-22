<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $site['name'] ?? 'Quick Spot Color' }} — {{ $site['tagline'] ?? 'Solusi SATSET Cetak DTF' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/spotcolor favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/softsale.css') }}">
    @stack('styles')
</head>
<body>
    @php($bootAppMode = ($landingData ?? [])['appMode'] ?? 'landing')
    @if($bootAppMode === 'admin')
        <div id="appBootSplash" class="app-boot-splash" aria-live="polite" aria-busy="true">
            <div class="app-boot-spinner" role="status"><span class="sr-only">Memuat…</span></div>
        </div>
    @endif

    @yield('content')

    <script>
        window.SOFTSALE_DATA = @json($landingData ?? []);
    </script>
    <script>
        (function () {
            document.documentElement.classList.add('app-booting');

            function revealInView() {
                var trigger = window.innerHeight * 0.88;
                document.querySelectorAll('.reveal').forEach(function (el) {
                    if (el.getBoundingClientRect().top < trigger) el.classList.add('visible');
                });
            }

            function normalizeDashPage(page) {
                if (page === 'clients') return 'users';
                if (page === 'affiliate-referrals') return 'affiliate-overview';
                return page;
            }

            function applyEarlyDashboardRoute() {
                var data = window.SOFTSALE_DATA || {};
                var portal = data.appMode;
                if (!portal || portal === 'landing') return;

                var dashIds = { client: 'dashClient', admin: 'dashAdmin', affiliate: 'dashAffiliate' };
                var dashId = dashIds[portal];
                var dash = dashId ? document.getElementById(dashId) : null;
                if (!dash) return;

                var page = null;
                var hashMatch = window.location.hash.match(/^#\/([a-z-]+)$/);
                if (hashMatch) page = hashMatch[1];
                if (!page) {
                    try {
                        var saved = JSON.parse(localStorage.getItem('softsale_route') || 'null');
                        if (saved && saved.portal === portal) page = saved.page;
                    } catch (e) { /* ignore */ }
                }
                page = normalizeDashPage(page || 'overview');

                var target = document.getElementById(portal + '-' + page);
                if (!target) return;

                dash.querySelectorAll('.dash-page').forEach(function (p) { p.classList.remove('active'); });
                target.classList.add('active');
            }

            function applyInitialClient(client) {
                if (!client) return;
                var title = document.getElementById('clientOverviewTitle');
                var displayName = client.name || client.company_name || 'Client';
                if (title) title.textContent = 'Selamat Datang, ' + displayName;
                var nameInput = document.getElementById('clientSettingsName');
                var email = document.getElementById('clientSettingsEmail');
                var wa = document.getElementById('clientSettingsWhatsapp');
                if (nameInput) nameInput.value = displayName;
                if (email) email.value = client.email || '';
                if (wa) wa.value = client.whatsapp || '';
                var dash = document.getElementById('dashClient');
                if (dash) dash.classList.add('is-ready');
            }

            function applyAdminOverviewSnapshot(snapshot) {
                if (!snapshot) return;
                var clients = document.getElementById('adminOverviewStatClients');
                var affiliates = document.getElementById('adminOverviewStatAffiliates');
                if (clients && snapshot.clients != null) clients.textContent = String(snapshot.clients);
                if (affiliates && snapshot.affiliates_active != null) affiliates.textContent = String(snapshot.affiliates_active);
            }

            function applyInitialPublicAuth(client, mode) {
                if (!client || (mode !== 'landing' && mode !== 'hub')) return;
                document.querySelectorAll('[data-auth-guest]').forEach(function (el) { el.hidden = true; });
                document.querySelectorAll('[data-auth-user]').forEach(function (el) { el.hidden = false; });
            }

            var bootData = window.SOFTSALE_DATA || {};
            applyInitialClient(bootData.initialClient);
            applyInitialPublicAuth(bootData.initialClient, bootData.appMode);
            applyAdminOverviewSnapshot(bootData.adminOverview);
            revealInView();
            applyEarlyDashboardRoute();
            if (bootData.initialClientLicense && bootData.appMode === 'client') {
                var overviewEl = document.getElementById('client-overview');
                if (overviewEl && overviewEl.classList.contains('active') && bootData.initialClientLicense.applications) {
                    var apps = bootData.initialClientLicense.applications;
                    var activeLic = 0;
                    var expiredLic = 0;
                    var activeDev = 0;
                    for (var i = 0; i < apps.length; i++) {
                        var app = apps[i];
                        var expired = app.is_expired;
                        if (!expired && app.expires_at) {
                            var end = new Date(app.expires_at);
                            if (!isNaN(end.getTime()) && end.getTime() < Date.now()) expired = true;
                        }
                        if (expired) expiredLic += 1;
                        else activeLic += 1;
                        activeDev += Number(app.devices_active || 0);
                    }
                    var statActive = document.getElementById('clientOverviewStatActive');
                    var statHint = document.getElementById('clientOverviewStatActiveHint');
                    var statDev = document.getElementById('clientOverviewStatDevices');
                    var statExpired = document.getElementById('clientOverviewStatExpired');
                    if (statActive) statActive.textContent = apps.length ? String(activeLic) : '0';
                    if (statHint) statHint.textContent = apps.length > 1 ? ('dari ' + apps.length + ' aplikasi') : (apps.length ? '' : 'Belum ada aplikasi');
                    if (statDev) statDev.textContent = String(activeDev);
                    if (statExpired) statExpired.textContent = apps.length ? String(expiredLic) : '0';
                }
            }
        })();
    </script>
    <script src="{{ asset('js/softsale.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
