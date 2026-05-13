<?php
$activeNav = $activeNav ?? 'dashboard';
$navItems = [
    'dashboard' => ['label' => 'Visao Geral', 'href' => url('/')],
    'courses' => ['label' => 'Cursos', 'href' => url('/cursos')],
    'students' => ['label' => 'Alunos', 'href' => url('/alunos')],
    'enrollments' => ['label' => 'Matriculas', 'href' => url('/matriculas')],
];
?>
<header class="rounded-[2rem] border border-slate-200 bg-white/95 px-6 py-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/95">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">TRAXTER Educacional</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-950 dark:text-white"><?= e($company['name'] ?? config('app.name')) ?></h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Cursos, alunos, matriculas e emissao automatica de certificados em um fluxo unico.</p>
        </div>
        <nav class="flex flex-wrap gap-2">
            <?php foreach ($navItems as $key => $item): ?>
                <a href="<?= e($item['href']) ?>" class="rounded-full px-4 py-2 text-sm font-medium transition <?= $activeNav === $key ? 'bg-slate-950 text-white dark:bg-white dark:text-slate-950' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800' ?>">
                    <?= e($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>
