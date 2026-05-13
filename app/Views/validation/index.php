<?php
$useAdminLayout = true;
$activeNav = 'certificates';
$activeSubNav = 'validate';
$pageHeaderEyebrow = 'Validacao';
$pageHeaderTitle = 'Consulta de autenticidade';
$pageHeaderSubtitle = 'Informe o hash do certificado para validar a autenticidade e conferir os dados principais do documento emitido.';
?>

<main class="admin-shell">
    <?php require base_path('app/Views/partials/page_header.php'); ?>

    <section class="admin-card form-card">
        <div class="card-header">
            <div>
                <p class="card-kicker">Consulta publica</p>
                <h2 class="card-title">Validar certificado</h2>
                <p class="card-subtitle">Esta tela atende o fluxo administrativo e tambem serve de apoio para orientar o usuario sobre o uso do hash publico.</p>
            </div>
        </div>

        <form method="GET" action="<?= e(url('/validar')) ?>" class="mt-6 space-y-4" aria-label="Formulario de validacao de certificado">
            <label class="field-card">
                <span>Hash de validacao</span>
                <input
                    type="text"
                    name="hash"
                    value="<?= e($hash) ?>"
                    class="field-input font-mono"
                    inputmode="text"
                    autocomplete="off"
                    aria-describedby="validation-help"
                    placeholder="Cole aqui o hash SHA-256 do certificado"
                >
                <small id="validation-help" class="field-help">Use o hash exibido no QR Code ou a URL completa de validacao presente no certificado.</small>
            </label>
            <button type="submit" class="action-button action-button--primary">Validar certificado</button>
        </form>
    </section>

    <?php if ($searched): ?>
        <?php if ($certificate): ?>
            <section class="admin-card">
                <div class="public-grid">
                    <div class="surface-panel surface-panel--success">
                        <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Certificado valido</p>
                        <p class="mt-3 text-sm text-emerald-900 dark:text-emerald-100">Os dados consultados correspondem a um certificado autentico emitido pela plataforma.</p>
                    </div>
                    <div class="surface-panel">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Hash informado</p>
                        <p class="mt-3 break-all font-mono text-sm"><?= e($hash) ?></p>
                    </div>
                </div>

                <div class="mt-8 public-grid">
                    <div class="surface-panel">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Aluno</p>
                        <p class="mt-3 text-xl font-semibold"><?= e($certificate['full_name']) ?></p>
                    </div>
                    <div class="surface-panel">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Curso</p>
                        <p class="mt-3 text-xl font-semibold"><?= e($certificate['course_name']) ?></p>
                    </div>
                    <div class="surface-panel">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Codigo</p>
                        <p class="mt-3 text-xl font-semibold"><?= e($certificate['certificate_code']) ?></p>
                    </div>
                    <div class="surface-panel">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Instituicao</p>
                        <p class="mt-3 text-xl font-semibold"><?= e($certificate['institution_name']) ?></p>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <section class="admin-card">
                <div class="surface-panel surface-panel--danger">
                    <p class="text-sm font-semibold text-rose-700 dark:text-rose-300">Certificado nao localizado</p>
                    <p class="mt-3 text-sm text-rose-900 dark:text-rose-100">O hash informado nao corresponde a um certificado valido ou pode ter sido alterado.</p>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</main>
