<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <?= $content ?>
    <script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</body>
</html>
