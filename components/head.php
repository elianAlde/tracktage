<!DOCTYPE html>
<html lang="<?= LANG ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#ffffff">
    <title>Tracktage</title>

    <!-- Apply the theme before first paint to avoid a white flash in dark mode -->
    <script>
        (function () {
            var pref = 'auto';
            try { pref = localStorage.getItem('theme') || 'auto'; } catch (e) {}
            var mq = window.matchMedia('(prefers-color-scheme: dark)');
            window.applyTheme = function (p) {
                pref = p || pref;
                var dark = pref === 'dark' || (pref === 'auto' && mq.matches);
                document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
                var meta = document.querySelector('meta[name="theme-color"]');
                if (meta) meta.setAttribute('content', dark ? '#1a1d23' : '#ffffff');
            };
            window.applyTheme();
            mq.addEventListener('change', function () { window.applyTheme(); });
        })();
    </script>

    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>assets/logos/icon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>assets/logos/favicon-32.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>assets/logos/apple-touch-icon.png">
    <link rel="manifest" href="<?= BASE_URL ?>manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <link rel="stylesheet" href="<?= BASE_URL ?>vendors/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>style/components.css">

    <script src="<?= BASE_URL ?>vendors/jquery/jquery-3.7.1.min.js"></script>
    <script src="<?= BASE_URL ?>vendors/bootstrap/bootstrap.bundle.min.js"></script>

    <!-- Globals used by script/app.js: API base URL, currency and the current language's strings -->
    <script>
        var server_url = "<?= BASE_URL ?>";
        var base_currency = "<?= BASE_CURRENCY ?>";
        var I18N = <?= json_encode([
            'lang'    => LANG,
            'locale'  => LOCALE,
            'cookie'  => LANG_COOKIE,
            'strings' => translations(),
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    </script>
    <script src="<?= BASE_URL ?>script/app.js"></script>
</head>
<body>
