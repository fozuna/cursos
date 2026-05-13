<div class="flex min-h-screen items-center justify-center bg-slate-100 px-6 py-10 dark:bg-slate-950">
    <div class="w-full max-w-4xl rounded-[2rem] border border-slate-200 bg-white p-8 shadow-soft dark:border-slate-800 dark:bg-slate-900">
        <p class="text-xs uppercase tracking-[0.35em] text-brand-700 dark:text-brand-100">Validacao publica</p>
        <h1 class="mt-3 text-3xl font-semibold">Consulta de autenticidade do certificado</h1>

        <?php if ($certificate): ?>
            <div class="mt-8 grid gap-6 md:grid-cols-2">
                <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-6 dark:border-emerald-900/60 dark:bg-emerald-950/20">
                    <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Certificado valido</p>
                    <p class="mt-3 text-sm text-emerald-900 dark:text-emerald-100">Os dados consultados correspondem a um certificado autentico emitido pela plataforma.</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6 dark:border-slate-700 dark:bg-slate-950">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Hash de validacao</p>
                    <p class="mt-3 break-all font-mono text-sm"><?= e($hash) ?></p>
                </div>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-2">
                <div class="rounded-3xl border border-slate-200 p-6 dark:border-slate-700">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Aluno</p>
                    <p class="mt-3 text-xl font-semibold"><?= e($certificate['full_name']) ?></p>
                </div>
                <div class="rounded-3xl border border-slate-200 p-6 dark:border-slate-700">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Curso</p>
                    <p class="mt-3 text-xl font-semibold"><?= e($certificate['course_name']) ?></p>
                </div>
                <div class="rounded-3xl border border-slate-200 p-6 dark:border-slate-700">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Instituicao</p>
                    <p class="mt-3 text-xl font-semibold"><?= e($certificate['institution_name']) ?></p>
                </div>
                <div class="rounded-3xl border border-slate-200 p-6 dark:border-slate-700">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Codigo</p>
                    <p class="mt-3 text-xl font-semibold"><?= e($certificate['certificate_code']) ?></p>
                </div>
                <div class="rounded-3xl border border-slate-200 p-6 dark:border-slate-700">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Data de conclusao</p>
                    <p class="mt-3 text-xl font-semibold"><?= e($certificate['completion_date']) ?></p>
                </div>
                <div class="rounded-3xl border border-slate-200 p-6 dark:border-slate-700">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Empresa emissora</p>
                    <p class="mt-3 text-xl font-semibold"><?= e($certificate['company_name']) ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="mt-8 rounded-3xl border border-rose-200 bg-rose-50 p-8 dark:border-rose-900/60 dark:bg-rose-950/20">
                <p class="text-sm font-semibold text-rose-700 dark:text-rose-300">Certificado nao localizado</p>
                <p class="mt-3 text-sm text-rose-900 dark:text-rose-100">O hash informado nao corresponde a um certificado valido ou pode ter sido alterado.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
