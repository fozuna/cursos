<div class="status-shell">
    <div class="status-card text-center">
        <p class="page-header__eyebrow">404</p>
        <h1 class="page-header__title">Rota nao encontrada</h1>
        <p class="page-header__subtitle" style="margin: 0.75rem auto 0;">
            O caminho <?= e($path ?? '/') ?> nao esta disponivel nesta aplicacao.
        </p>
        <a href="<?= e(url('/')) ?>" class="action-button action-button--primary mt-8">
            Voltar ao dashboard
        </a>
    </div>
</div>
