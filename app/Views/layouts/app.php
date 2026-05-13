<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title><?= e(config('app.name')) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            700: '#1d4ed8',
                            950: '#172554'
                        }
                    },
                    boxShadow: {
                        soft: '0 20px 70px rgba(15, 23, 42, 0.12)'
                    }
                }
            }
        };
    </script>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="min-h-full bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <?php $useAdminLayout = $useAdminLayout ?? false; ?>
    <a href="#main-content" class="skip-link">Pular para o conteudo principal</a>
    <?php if ($useAdminLayout): ?>
        <div class="app-layout" data-admin-layout>
            <?php require base_path('app/Views/partials/admin_sidebar.php'); ?>
            <div class="app-layout__main">
                <header class="mobile-topbar" aria-label="Cabecalho mobile">
                    <button
                        type="button"
                        class="mobile-topbar__toggle"
                        data-sidebar-toggle
                        aria-controls="admin-sidebar"
                        aria-expanded="false"
                        aria-label="Abrir menu lateral"
                    >
                        <span class="mobile-topbar__toggle-line" aria-hidden="true"></span>
                        <span class="mobile-topbar__toggle-line" aria-hidden="true"></span>
                        <span class="mobile-topbar__toggle-line" aria-hidden="true"></span>
                    </button>
                    <a href="<?= e(url('/')) ?>" class="mobile-topbar__brand" aria-label="Ir para o dashboard principal">
                        <span class="mobile-topbar__brand-mark" aria-hidden="true">CP</span>
                        <span><?= e(config('app.name')) ?></span>
                    </a>
                </header>
                <div id="main-content" class="app-layout__content">
                    <?= $content ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div id="main-content">
            <?= $content ?>
        </div>
    <?php endif; ?>
    <script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</body>
</html>
