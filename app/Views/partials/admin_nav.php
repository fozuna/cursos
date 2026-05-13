<?php
$activeNav = $activeNav ?? 'dashboard';
$navItems = [
    'dashboard' => ['label' => 'Visao Geral', 'href' => url('/')],
    'courses' => ['label' => 'Cursos', 'href' => url('/cursos')],
    'students' => ['label' => 'Alunos', 'href' => url('/alunos')],
    'enrollments' => ['label' => 'Matriculas', 'href' => url('/matriculas')],
];
?>
<header class="page-header">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="page-header__eyebrow">TRAXTER Educacional</p>
            <h1 class="page-header__title"><?= e($company['name'] ?? config('app.name')) ?></h1>
            <p class="page-header__subtitle">Cursos, alunos, matriculas e emissao automatica de certificados em um fluxo unico.</p>
        </div>
        <nav class="page-actions" aria-label="Navegacao principal administrativa">
            <?php foreach ($navItems as $key => $item): ?>
                <a href="<?= e($item['href']) ?>" class="action-button <?= $activeNav === $key ? 'action-button--primary' : '' ?>">
                    <?= e($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>
