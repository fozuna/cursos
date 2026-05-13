<?php
$zipFile = $flash['zip_file'] ?? null;
$flashDetails = $flash['details'] ?? null;
$activeNav = 'dashboard';
require base_path('app/Views/partials/admin_nav.php');
?>

<main class="mx-auto flex min-h-screen w-full max-w-7xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
    <?php require base_path('app/Views/partials/flash.php'); ?>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Cursos</p>
            <h2 class="mt-3 text-3xl font-semibold"><?= (int) $courseCount ?></h2>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Alunos</p>
            <h2 class="mt-3 text-3xl font-semibold"><?= (int) $studentCount ?></h2>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Matriculas</p>
            <h2 class="mt-3 text-3xl font-semibold"><?= (int) $enrollmentCount ?></h2>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Certificados emitidos</p>
            <h2 class="mt-3 text-3xl font-semibold"><?= (int) ($stats['generated_count'] ?? 0) ?></h2>
        </article>
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">Cursos com certificado</p>
            <h2 class="mt-3 text-3xl font-semibold"><?= (int) ($stats['unique_courses'] ?? 0) ?></h2>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <div class="rounded-[2rem] bg-gradient-to-br from-slate-950 via-slate-900 to-brand-950 p-6 text-white shadow-soft">
            <p class="text-xs uppercase tracking-[0.3em] text-brand-100">Emissao simplificada</p>
            <h2 class="mt-3 text-3xl font-semibold">Selecione um curso e gere os certificados automaticamente</h2>
            <p class="mt-4 max-w-2xl text-sm leading-6 text-slate-300">O sistema usa os dados do curso e as matriculas concluidas para preencher frente, verso, QR Code e lote ZIP sem digitacao manual.</p>

            <form method="POST" action="<?= e(url('/certificados/gerar')) ?>" class="mt-8 space-y-4">
                <input type="hidden" name="input_mode" value="course">
                <label class="block rounded-3xl bg-white/10 p-4">
                    <span class="text-sm font-semibold text-white">Curso</span>
                    <select name="course_id" class="mt-3 w-full rounded-2xl border border-white/15 bg-white/95 px-4 py-3 text-sm text-slate-900 outline-none" required>
                        <option value="">Selecione um curso para emissao</option>
                        <?php foreach ($activeCourses as $course): ?>
                            <option value="<?= (int) $course['id'] ?>"><?= e($course['name']) ?> | <?= e(format_date_br((string) $course['end_date'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button type="submit" class="inline-flex rounded-full bg-white px-6 py-3 text-sm font-semibold text-slate-950">
                    Gerar certificados do curso
                </button>
            </form>

            <?php if ($zipFile): ?>
                <div class="mt-5 rounded-3xl border border-white/15 bg-white/10 p-4">
                    <p class="text-sm font-semibold">Ultimo lote pronto para download</p>
                    <p class="mt-1 text-xs text-slate-300"><?= e($flashDetails ?: 'Os certificados foram processados e agrupados em ZIP.') ?></p>
                    <a href="<?= e(url('/download/' . basename($zipFile))) ?>" class="mt-4 inline-flex rounded-full border border-white/20 px-4 py-2 text-sm font-semibold text-white">Baixar ZIP</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Atalhos</p>
                    <h3 class="mt-2 text-2xl font-semibold">Fluxo operacional</h3>
                </div>
                <button type="button" data-theme-toggle class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium dark:border-slate-700">Tema</button>
            </div>
            <div class="mt-6 grid gap-4">
                <a href="<?= e(url('/cursos')) ?>" class="rounded-3xl border border-slate-200 p-5 transition hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-500">
                    <p class="text-lg font-semibold">1. Configure os cursos</p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Cadastre carga horaria, periodo, instrutor e conteudo programatico.</p>
                </a>
                <a href="<?= e(url('/alunos')) ?>" class="rounded-3xl border border-slate-200 p-5 transition hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-500">
                    <p class="text-lg font-semibold">2. Cadastre os alunos</p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Valide nome, e-mail, telefone e CPF antes das matriculas.</p>
                </a>
                <a href="<?= e(url('/matriculas')) ?>" class="rounded-3xl border border-slate-200 p-5 transition hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-500">
                    <p class="text-lg font-semibold">3. Matricule em lote</p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Vincule varios alunos ao mesmo curso e acompanhe status.</p>
                </a>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold">Matriculas recentes</h3>
                <a href="<?= e(url('/matriculas')) ?>" class="text-sm font-medium text-brand-700 dark:text-brand-100">Ver modulo</a>
            </div>
            <div class="mt-5 overflow-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="pb-3 pr-4">Aluno</th>
                            <th class="pb-3 pr-4">Curso</th>
                            <th class="pb-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        <?php foreach ($recentEnrollments as $enrollment): ?>
                            <tr>
                                <td class="py-4 pr-4 font-medium"><?= e($enrollment['full_name']) ?></td>
                                <td class="py-4 pr-4"><?= e($enrollment['course_name']) ?></td>
                                <td class="py-4">
                                    <span class="rounded-full px-3 py-1 text-xs <?= $enrollment['status'] === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : ($enrollment['status'] === 'cancelled' ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300') ?>">
                                        <?= e($enrollment['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold">Certificados recentes</h3>
                <span class="text-xs uppercase tracking-[0.25em] text-slate-400">Auditoria</span>
            </div>
            <div class="mt-5 space-y-4">
                <?php foreach ($latestCertificates as $certificate): ?>
                    <article class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold"><?= e($certificate['full_name']) ?></p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400"><?= e($certificate['course_name']) ?></p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs <?= $certificate['status'] === 'generated' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' ?>">
                                <?= e($certificate['status']) ?>
                            </span>
                        </div>
                        <div class="mt-3 flex items-center justify-between gap-4 text-sm">
                            <span class="text-slate-500 dark:text-slate-400"><?= e($certificate['certificate_code']) ?></span>
                            <?php if (!empty($certificate['pdf_path'])): ?>
                                <a href="<?= e(url('/download/' . basename($certificate['pdf_path']))) ?>" class="font-medium text-brand-700 dark:text-brand-100">Baixar PDF</a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1fr_1fr_1fr]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-xl font-semibold">Lotes recentes</h3>
            <div class="mt-4 space-y-4">
                <?php foreach ($latestBatches as $batch): ?>
                    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                        <p class="font-medium"><?= e($batch['name']) ?></p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400"><?= (int) $batch['processed_items'] ?>/<?= (int) $batch['total_items'] ?> via <?= e($batch['import_source']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 xl:col-span-2">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold">Historico de operacoes</h3>
                <span class="text-xs uppercase tracking-[0.25em] text-slate-400">Logs resumidos</span>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <?php foreach ($history as $item): ?>
                    <article class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold"><?= e($item['action']) ?></p>
                            <span class="rounded-full px-3 py-1 text-xs <?= $item['status'] === 'success' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' ?>"><?= e($item['status']) ?></span>
                        </div>
                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300"><?= e($item['message']) ?></p>
                        <p class="mt-3 text-xs uppercase tracking-[0.2em] text-slate-400"><?= e($item['created_at']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
