<?php
$useAdminLayout = true;
$activeNav = 'certificates';
$activeSubNav = 'list';
$pageHeaderEyebrow = 'Compartilhamento';
$pageHeaderTitle = 'Link individual do aluno';
$pageHeaderSubtitle = 'Use este link temporario para compartilhar o acesso protegido ao certificado com o aluno correspondente.';
?>

<main class="admin-shell">
    <?php require base_path('app/Views/partials/page_header.php'); ?>

    <section class="admin-card">
        <div class="card-header">
            <div>
                <p class="card-kicker">Acesso protegido</p>
                <h2 class="card-title"><?= e($share['student_name']) ?></h2>
                <p class="card-subtitle">O link abaixo fica valido por 7 dias, exige confirmacao do celular cadastrado e restringe o acesso ao certificado vinculado.</p>
            </div>
            <div class="page-actions">
                <a href="<?= e(url('/certificados?secao=lista#lista-certificados')) ?>" class="action-button">Voltar para certificados</a>
            </div>
        </div>

        <div class="public-grid">
            <div class="surface-panel">
                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Aluno</p>
                <p class="mt-3 text-xl font-semibold"><?= e($share['student_name']) ?></p>
            </div>
            <div class="surface-panel">
                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Curso</p>
                <p class="mt-3 text-xl font-semibold"><?= e($share['course_name']) ?></p>
            </div>
            <div class="surface-panel">
                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Codigo</p>
                <p class="mt-3 text-xl font-semibold"><?= e($share['certificate_code']) ?></p>
            </div>
            <div class="surface-panel">
                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Expira em</p>
                <p class="mt-3 text-xl font-semibold"><?= e($share['expires_at']) ?></p>
            </div>
        </div>

        <div class="mt-8 surface-panel">
            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Link individual para compartilhamento</p>
            <label class="field-card mt-4">
                <span>URL publica temporaria</span>
                <input type="text" readonly value="<?= e($share['link']) ?>" class="field-input font-mono">
                <small class="field-help">Envie este link apenas ao aluno correto. Mesmo com o link, o download so sera liberado apos confirmar o celular cadastrado.</small>
            </label>
        </div>
    </section>
</main>
