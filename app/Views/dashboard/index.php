<?php
$zipFile = $flash['zip_file'] ?? null;
$flashDetails = $flash['details'] ?? null;
$useAdminLayout = true;
$activeNav = 'dashboard';
$pageHeaderEyebrow = 'Operacao educacional';
$pageHeaderTitle = 'Painel executivo de cursos, alunos, matriculas e certificados';
$pageHeaderSubtitle = 'Visualize a operacao, dispare a emissao automatica por curso e acompanhe o historico recente em uma interface mais fluida e corporativa.';
$pageHeaderActions = '<a href="' . e(url('/cursos')) . '" class="action-button">Gerenciar cursos</a>'
    . '<a href="' . e(url('/matriculas')) . '" class="action-button">Ir para matriculas</a>';
?>

<main class="admin-shell">
    <?php require base_path('app/Views/partials/flash.php'); ?>
    <?php require base_path('app/Views/partials/page_header.php'); ?>

    <section class="admin-card stats-card">
        <div class="card-header card-header--tight">
            <div>
                <p class="card-kicker">Indicadores</p>
                <h2 class="card-title">Panorama da operacao</h2>
                <p class="card-subtitle">Leitura executiva dos principais volumes e do status atual da plataforma.</p>
            </div>
        </div>
        <div class="stats-grid">
            <article class="admin-card stats-card">
                <p class="stats-card__label">Cursos</p>
                <h2 class="stats-card__value"><?= (int) $courseCount ?></h2>
                <p class="stats-card__meta">Catalogo ativo e historico de formacoes.</p>
            </article>
            <article class="admin-card stats-card">
                <p class="stats-card__label">Alunos</p>
                <h2 class="stats-card__value"><?= (int) $studentCount ?></h2>
                <p class="stats-card__meta">Base validada para matriculas e certificados.</p>
            </article>
            <article class="admin-card stats-card">
                <p class="stats-card__label">Matriculas</p>
                <h2 class="stats-card__value"><?= (int) $enrollmentCount ?></h2>
                <p class="stats-card__meta">Vinculos operacionais entre cursos e alunos.</p>
            </article>
            <article class="admin-card stats-card">
                <p class="stats-card__label">Certificados emitidos</p>
                <h2 class="stats-card__value"><?= (int) ($stats['generated_count'] ?? 0) ?></h2>
                <p class="stats-card__meta">Documentos concluidos e prontos para download.</p>
            </article>
            <article class="admin-card stats-card">
                <p class="stats-card__label">Cursos com certificado</p>
                <h2 class="stats-card__value"><?= (int) ($stats['unique_courses'] ?? 0) ?></h2>
                <p class="stats-card__meta">Programas com emissao efetivamente realizada.</p>
            </article>
        </div>
    </section>

    <section class="admin-card form-card">
        <div class="card-header">
            <div>
                <p class="card-kicker">Card de acoes</p>
                <h2 class="card-title">Emissao simplificada por curso</h2>
                <p class="card-subtitle">Selecione um curso e deixe que o sistema preencha dados, concluintes, QR Code, frente, verso e lote ZIP automaticamente.</p>
            </div>
            <div class="page-actions">
                <button type="button" data-theme-toggle class="action-button">Alternar tema</button>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="rounded-[1.75rem] bg-gradient-to-br from-slate-950 via-slate-900 to-brand-950 p-6 text-white shadow-soft">
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
                    <button type="submit" class="action-button bg-white text-slate-950">Gerar certificados do curso</button>
                </form>

                <?php if ($zipFile): ?>
                    <div class="mt-5 rounded-3xl border border-white/15 bg-white/10 p-4">
                        <p class="text-sm font-semibold">Ultimo lote pronto para download</p>
                        <p class="mt-1 text-xs text-slate-300"><?= e($flashDetails ?: 'Os certificados foram processados e agrupados em ZIP.') ?></p>
                        <a href="<?= e(url('/download/' . basename($zipFile))) ?>" class="mt-4 inline-flex rounded-full border border-white/20 px-4 py-2 text-sm font-semibold text-white">Baixar ZIP</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="section-stack">
                <article class="summary-card">
                    <p class="card-kicker">Fluxo operacional</p>
                    <h3 class="card-title">1. Configure os cursos</h3>
                    <p class="card-subtitle">Cadastre carga horaria, periodo, instrutor e conteudo programatico.</p>
                </article>
                <article class="summary-card">
                    <p class="card-kicker">Fluxo operacional</p>
                    <h3 class="card-title">2. Cadastre os alunos</h3>
                    <p class="card-subtitle">Valide nome, e-mail, telefone e CPF antes das matriculas.</p>
                </article>
                <article class="summary-card">
                    <p class="card-kicker">Fluxo operacional</p>
                    <h3 class="card-title">3. Matricule e conclua</h3>
                    <p class="card-subtitle">Vincule varios alunos ao mesmo curso, conclua e emita sem redigitacao manual.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="admin-card table-card">
        <div class="card-header">
            <div>
                <p class="card-kicker">Listagem</p>
                <h2 class="card-title">Matriculas recentes</h2>
                <p class="card-subtitle">Acompanhe as ultimas movimentacoes operacionais com foco em curso, aluno e status atual.</p>
            </div>
            <div class="page-actions">
                <a href="<?= e(url('/matriculas')) ?>" class="action-button">Ver modulo</a>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Curso</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentEnrollments as $enrollment): ?>
                        <tr>
                            <td class="data-table__title"><?= e($enrollment['full_name']) ?></td>
                            <td><?= e($enrollment['course_name']) ?></td>
                            <td>
                                <span class="badge <?= $enrollment['status'] === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : ($enrollment['status'] === 'cancelled' ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300') ?>">
                                    <?= e($enrollment['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="admin-card table-card">
        <div class="card-header">
            <div>
                <p class="card-kicker">Listagem</p>
                <h2 class="card-title">Certificados recentes</h2>
                <p class="card-subtitle">Visualize rapidamente os documentos mais recentes e baixe os PDFs individuais quando necessario.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Curso</th>
                        <th>Codigo</th>
                        <th>Status</th>
                        <th>Arquivo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($latestCertificates as $certificate): ?>
                        <tr>
                            <td class="data-table__title"><?= e($certificate['full_name']) ?></td>
                            <td><?= e($certificate['course_name']) ?></td>
                            <td class="data-table__meta"><?= e($certificate['certificate_code']) ?></td>
                            <td>
                                <span class="badge <?= $certificate['status'] === 'generated' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300' ?>">
                                    <?= e($certificate['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($certificate['pdf_path'])): ?>
                                    <a href="<?= e(url('/download/' . basename($certificate['pdf_path']))) ?>" class="action-button">Baixar PDF</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="summary-grid">
        <article class="summary-card">
            <p class="card-kicker">Resumo operacional</p>
            <h3 class="card-title">Lotes recentes</h3>
            <div class="mt-4 space-y-3">
                <?php foreach ($latestBatches as $batch): ?>
                    <div class="rounded-2xl border border-slate-200 px-4 py-3 dark:border-slate-700">
                        <p class="font-medium"><?= e($batch['name']) ?></p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400"><?= (int) $batch['processed_items'] ?>/<?= (int) $batch['total_items'] ?> via <?= e($batch['import_source']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
        <article class="summary-card" style="grid-column: span 2;">
            <p class="card-kicker">Historico</p>
            <h3 class="card-title">Operacoes recentes</h3>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <?php foreach ($history as $item): ?>
                    <article class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-semibold"><?= e($item['action']) ?></p>
                            <span class="badge <?= $item['status'] === 'success' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' ?>"><?= e($item['status']) ?></span>
                        </div>
                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300"><?= e($item['message']) ?></p>
                        <p class="mt-3 text-xs uppercase tracking-[0.2em] text-slate-400"><?= e($item['created_at']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </article>
    </section>
</main>
