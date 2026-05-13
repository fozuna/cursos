<?php
$activeNav = $activeNav ?? 'dashboard';
$activeSubNav = $activeSubNav ?? null;
$companyName = $company['name'] ?? config('app.name');
$certificateMenuExpanded = in_array($activeNav, ['certificates'], true);

$icon = static function (string $name): string {
    $icons = [
        'dashboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 13.5h6.5V20H4z"/><path d="M13.5 4H20v9.5h-6.5z"/><path d="M13.5 15.5H20V20h-6.5z"/><path d="M4 4h6.5v6.5H4z"/></svg>',
        'students' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M15 19v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1"/><circle cx="9" cy="8" r="4"/><path d="M17 11a4 4 0 1 1 0-8"/><path d="M21 19v-1a4 4 0 0 0-3-3.87"/></svg>',
        'courses' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6.5A2.5 2.5 0 0 1 6.5 4H20v13.5A2.5 2.5 0 0 0 17.5 15H4z"/><path d="M4 6.5V20h13.5A2.5 2.5 0 0 0 20 17.5"/><path d="M8 8h8"/><path d="M8 11.5h6"/></svg>',
        'enrollments' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M8 7h12"/><path d="M8 12h12"/><path d="M8 17h12"/><path d="M4 7h.01"/><path d="M4 12h.01"/><path d="M4 17h.01"/></svg>',
        'certificates' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 4h10l3 3v13H4V4z"/><path d="M14 4v4h4"/><path d="M8 12h8"/><path d="M8 16h5"/></svg>',
        'generate' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>',
        'list' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M8 6h12"/><path d="M8 12h12"/><path d="M8 18h12"/><path d="M4 6h.01"/><path d="M4 12h.01"/><path d="M4 18h.01"/></svg>',
        'validate' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M9 12.75 11.25 15 15.5 9.75"/><circle cx="12" cy="12" r="9"/></svg>',
        'settings' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 15.5A3.5 3.5 0 1 0 12 8.5a3.5 3.5 0 0 0 0 7Z"/><path d="M19.4 15a1 1 0 0 0 .2 1.1l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1 1 0 0 0-1.1-.2 1 1 0 0 0-.6.9V20a2 2 0 1 1-4 0v-.2a1 1 0 0 0-.7-.9 1 1 0 0 0-1.1.2l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1 1 0 0 0 .2-1.1 1 1 0 0 0-.9-.6H4a2 2 0 1 1 0-4h.2a1 1 0 0 0 .9-.7 1 1 0 0 0-.2-1.1l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1 1 0 0 0 1.1.2H9a1 1 0 0 0 .6-.9V4a2 2 0 1 1 4 0v.2a1 1 0 0 0 .7.9 1 1 0 0 0 1.1-.2l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1 1 0 0 0-.2 1.1v.1a1 1 0 0 0 .9.6H20a2 2 0 1 1 0 4h-.2a1 1 0 0 0-.9.7Z"/></svg>',
    ];

    return $icons[$name] ?? $icons['dashboard'];
};

$primarySections = [
    [
        'title' => 'Dashboard',
        'items' => [
            ['key' => 'dashboard', 'label' => 'Painel executivo', 'href' => url('/'), 'icon' => 'dashboard'],
        ],
    ],
    [
        'title' => 'Usuarios',
        'items' => [
            ['key' => 'students', 'label' => 'Alunos', 'href' => url('/alunos'), 'icon' => 'students'],
            ['key' => 'enrollments', 'label' => 'Matriculas', 'href' => url('/matriculas'), 'icon' => 'enrollments'],
        ],
    ],
    [
        'title' => 'Cursos',
        'items' => [
            ['key' => 'courses', 'label' => 'Catalogo de cursos', 'href' => url('/cursos'), 'icon' => 'courses'],
        ],
    ],
];

$certificateItems = [
    ['key' => 'generate', 'label' => 'Gerar certificados', 'href' => url('/certificados'), 'icon' => 'generate'],
    ['key' => 'list', 'label' => 'Listar certificados', 'href' => url('/certificados?secao=lista#lista-certificados'), 'icon' => 'list'],
    ['key' => 'validate', 'label' => 'Validar certificado', 'href' => url('/validar'), 'icon' => 'validate'],
];

$settingsItems = [
    ['key' => 'settings', 'label' => 'Configuracoes gerais', 'href' => url('/configuracoes'), 'icon' => 'settings'],
];
?>
<button
    type="button"
    class="sidebar-overlay"
    data-sidebar-overlay
    aria-hidden="true"
    tabindex="-1"
></button>

<aside
    id="admin-sidebar"
    class="admin-sidebar"
    data-sidebar
    aria-label="Menu lateral principal"
    aria-hidden="false"
>
    <div class="admin-sidebar__header">
        <a href="<?= e(url('/')) ?>" class="admin-sidebar__brand" aria-label="Ir para o dashboard principal">
            <span class="admin-sidebar__brand-mark" aria-hidden="true">CP</span>
            <span>
                <strong class="admin-sidebar__brand-title"><?= e(config('app.name')) ?></strong>
                <span class="admin-sidebar__brand-subtitle"><?= e($companyName) ?></span>
            </span>
        </a>
        <button
            type="button"
            class="admin-sidebar__close"
            data-sidebar-toggle
            aria-controls="admin-sidebar"
            aria-expanded="false"
            aria-label="Fechar menu lateral"
        >
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="admin-sidebar__body">
        <nav class="sidebar-nav" aria-label="Navegacao administrativa">
            <?php foreach ($primarySections as $section): ?>
                <div class="sidebar-group">
                    <p class="sidebar-group__title"><?= e($section['title']) ?></p>
                    <ul class="sidebar-list" role="list">
                        <?php foreach ($section['items'] as $item): ?>
                            <?php $isActive = $activeNav === $item['key']; ?>
                            <li>
                                <a
                                    href="<?= e($item['href']) ?>"
                                    class="sidebar-link <?= $isActive ? 'is-active' : '' ?>"
                                    <?= $isActive ? 'aria-current="page"' : '' ?>
                                >
                                    <span class="sidebar-link__icon"><?= $icon($item['icon']) ?></span>
                                    <span class="sidebar-link__label"><?= e($item['label']) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>

            <div class="sidebar-group">
                <p class="sidebar-group__title">Certificados</p>
                <button
                    type="button"
                    class="sidebar-link sidebar-link--toggle <?= $certificateMenuExpanded ? 'is-active' : '' ?>"
                    data-submenu-toggle
                    data-submenu-target="certificates-submenu"
                    aria-controls="certificates-submenu"
                    aria-expanded="<?= $certificateMenuExpanded ? 'true' : 'false' ?>"
                >
                    <span class="sidebar-link__icon"><?= $icon('certificates') ?></span>
                    <span class="sidebar-link__label">Operacoes com certificados</span>
                    <span class="sidebar-link__caret" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 9 6 6 6-6"/></svg>
                    </span>
                </button>
                <div
                    id="certificates-submenu"
                    class="sidebar-submenu <?= $certificateMenuExpanded ? 'is-open' : '' ?>"
                    data-submenu-panel
                    aria-hidden="<?= $certificateMenuExpanded ? 'false' : 'true' ?>"
                >
                    <ul class="sidebar-submenu__list" role="list">
                        <?php foreach ($certificateItems as $item): ?>
                            <?php $isSubActive = $activeNav === 'certificates' && $activeSubNav === $item['key']; ?>
                            <li>
                                <a
                                    href="<?= e($item['href']) ?>"
                                    class="sidebar-link sidebar-link--sub <?= $isSubActive ? 'is-active' : '' ?>"
                                    <?= $isSubActive ? 'aria-current="page"' : '' ?>
                                >
                                    <span class="sidebar-link__icon"><?= $icon($item['icon']) ?></span>
                                    <span class="sidebar-link__label"><?= e($item['label']) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="sidebar-group">
                <p class="sidebar-group__title">Configuracoes</p>
                <ul class="sidebar-list" role="list">
                    <?php foreach ($settingsItems as $item): ?>
                        <?php $isActive = $activeNav === $item['key']; ?>
                        <li>
                            <a
                                href="<?= e($item['href']) ?>"
                                class="sidebar-link <?= $isActive ? 'is-active' : '' ?>"
                                <?= $isActive ? 'aria-current="page"' : '' ?>
                            >
                                <span class="sidebar-link__icon"><?= $icon($item['icon']) ?></span>
                                <span class="sidebar-link__label"><?= e($item['label']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
    </div>

    <div class="admin-sidebar__footer">
        <button type="button" class="action-button sidebar-theme-toggle" data-theme-toggle aria-label="Alternar tema visual">
            <span class="sidebar-link__icon"><?= $icon('settings') ?></span>
            <span>Alternar tema</span>
        </button>
        <p class="admin-sidebar__meta">Menu responsivo com suporte a teclado, leitores de tela e navegação persistente.</p>
    </div>
</aside>
