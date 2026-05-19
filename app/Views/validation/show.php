<div class="public-shell">
    <div class="public-card">
        <p class="page-header__eyebrow">Validação pública</p>
        <h1 class="page-header__title">Consulta de autenticidade do certificado</h1>

        <?php if ($certificate): ?>
            <div class="mt-8 public-grid">
                <div class="surface-panel surface-panel--success">
                    <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Certificado válido</p>
                    <p class="mt-3 text-sm text-emerald-900 dark:text-emerald-100">Os dados consultados correspondem a um certificado autêntico emitido pela plataforma.</p>
                </div>
                <div class="surface-panel">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Hash de validação</p>
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
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Instituição</p>
                    <p class="mt-3 text-xl font-semibold"><?= e($certificate['institution_name']) ?></p>
                </div>
                <div class="surface-panel">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Código</p>
                    <p class="mt-3 text-xl font-semibold"><?= e($certificate['certificate_code']) ?></p>
                </div>
                <div class="surface-panel">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Data de conclusão</p>
                    <p class="mt-3 text-xl font-semibold"><?= e($certificate['completion_date']) ?></p>
                </div>
                <div class="surface-panel">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Empresa emissora</p>
                    <p class="mt-3 text-xl font-semibold"><?= e($certificate['company_name']) ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="mt-8 surface-panel surface-panel--danger">
                <p class="text-sm font-semibold text-rose-700 dark:text-rose-300">Certificado não localizado</p>
                <p class="mt-3 text-sm text-rose-900 dark:text-rose-100">O hash informado não corresponde a um certificado válido ou pode ter sido alterado.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
