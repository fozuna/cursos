<?php
$useAdminLayout = true;
$activeNav = 'settings';
$pageHeaderEyebrow = 'Configuracoes';
$pageHeaderTitle = 'Parametros centrais do sistema';
$pageHeaderSubtitle = 'Consulte rapidamente os dados principais de publicacao, validacao e armazenamento sem depender de configuracoes dispersas.';
?>

<main class="admin-shell">
    <?php require base_path('app/Views/partials/page_header.php'); ?>

    <section class="admin-card">
        <div class="card-header">
            <div>
                <p class="card-kicker">Resumo</p>
                <h2 class="card-title">Configuracao operacional</h2>
                <p class="card-subtitle">Os dados abaixo refletem a configuracao central do sistema e ajudam no deploy simples em hospedagem compartilhada ou VPS.</p>
            </div>
            <div class="page-actions">
                <button type="button" class="action-button" data-theme-toggle>Alternar tema</button>
            </div>
        </div>

        <div class="public-grid">
            <div class="surface-panel">
                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Sistema</p>
                <p class="mt-3 text-xl font-semibold"><?= e((string) ($appConfig['name'] ?? config('app.name'))) ?></p>
            </div>
            <div class="surface-panel">
                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Empresa padrao</p>
                <p class="mt-3 text-xl font-semibold"><?= e((string) ($company['name'] ?? 'Nao configurada')) ?></p>
            </div>
            <div class="surface-panel">
                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Base URL</p>
                <p class="mt-3 break-all font-mono text-sm"><?= e((string) ($appConfig['base_url'] ?? '')) ?></p>
            </div>
            <div class="surface-panel">
                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Validacao publica</p>
                <p class="mt-3 break-all font-mono text-sm"><?= e((string) ($appConfig['certificate_validation_route'] ?? '/validar')) ?></p>
            </div>
            <div class="surface-panel">
                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Storage</p>
                <p class="mt-3 break-all font-mono text-sm"><?= e((string) ($appConfig['public_storage_path'] ?? '')) ?></p>
            </div>
            <div class="surface-panel">
                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Index habilitado</p>
                <p class="mt-3 text-xl font-semibold"><?= !empty($appConfig['use_index']) ? 'Sim' : 'Nao' ?></p>
            </div>
        </div>
    </section>

    <section class="admin-card">
        <div class="card-header card-header--tight">
            <div>
                <p class="card-kicker">Boas praticas</p>
                <h2 class="card-title">Fluxo recomendado de publicacao</h2>
                <p class="card-subtitle">Mantenha a configuracao concentrada em `config.php` e ajuste apenas os dados necessarios para publicar em Linux, CloudPanel ou hospedagem compartilhada.</p>
            </div>
        </div>
        <div class="stack-list">
            <div class="summary-card">
                <strong class="data-table__title">1. Ajuste a base da aplicacao</strong>
                <p class="data-table__meta">Revise URL base, caminho de storage, logo institucional e dados da empresa no arquivo central de configuracao.</p>
            </div>
            <div class="summary-card">
                <strong class="data-table__title">2. Publique via FTP ou Git</strong>
                <p class="data-table__meta">Envie os arquivos para o servidor mantendo o `index.php` e a pasta `public` acessiveis pelo ambiente de hospedagem.</p>
            </div>
            <div class="summary-card">
                <strong class="data-table__title">3. Valide o fluxo completo</strong>
                <p class="data-table__meta">Teste dashboard, cadastro, emissao, download e validacao publica antes de liberar o uso operacional.</p>
            </div>
        </div>
    </section>
</main>
