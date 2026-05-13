<div class="status-shell">
    <div class="status-card text-center">
        <p class="page-header__eyebrow">500</p>
        <h1 class="page-header__title">Falha ao processar a requisicao</h1>
        <p class="page-header__subtitle" style="margin: 0.75rem auto 0;">
            <?= e($message ?? 'Erro interno no sistema.') ?>
        </p>
        <a href="<?= e(url('/')) ?>" class="action-button action-button--primary mt-8">
            Retornar ao sistema
        </a>
    </div>
</div>
