<?php
$useAdminLayout = true;
$activeNav = $activeNav ?? 'enrollments';
$pageHeaderEyebrow = 'Matriculas';
$pageHeaderTitle = 'Operacao de matriculas e conclusoes';
$pageHeaderSubtitle = 'Centralize vinculacao entre alunos e cursos, acompanhe status e habilite a emissao automatica de certificados por conclusao.';
$pageHeaderActions = '<a href="' . e(url('/matriculas/exportar/csv')) . '" class="action-button">Exportar base</a>';
?>

<main class="admin-shell">
    <?php require base_path('app/Views/partials/flash.php'); ?>
    <?php require base_path('app/Views/partials/page_header.php'); ?>

    <section class="section-stack">
        <section class="admin-card form-card">
            <div class="card-header">
                <div>
                    <p class="card-kicker">Card de cadastro</p>
                    <h2 class="card-title">Matricular varios alunos</h2>
                    <p class="card-subtitle">Selecione um curso, defina o status inicial e vincule multiplos alunos em uma unica operacao.</p>
                </div>
                <div class="page-actions">
                    <a href="<?= e(url('/cursos')) ?>" class="action-button">Gerenciar cursos</a>
                    <a href="<?= e(url('/alunos')) ?>" class="action-button">Gerenciar alunos</a>
                </div>
            </div>

            <form method="POST" action="<?= e(url('/matriculas/salvar')) ?>" class="mt-6 space-y-4">
                <div class="form-grid form-grid--3">
                    <label class="field-card">
                        <span>Curso</span>
                        <select name="course_id" class="field-input" required>
                            <option value="">Selecione um curso</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= (int) $course['id'] ?>" <?= (int) $filters['course_id'] === (int) $course['id'] ? 'selected' : '' ?>><?= e($course['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="field-card">
                        <span>Status inicial</span>
                        <select name="status" class="field-input">
                            <option value="active">Ativa</option>
                            <option value="completed">Concluida</option>
                            <option value="cancelled">Cancelada</option>
                        </select>
                    </label>
                    <label class="field-card">
                        <span>Data de matricula</span>
                        <input type="datetime-local" name="enrolled_at" value="<?= e(date('Y-m-d\TH:i')) ?>" class="field-input" required>
                    </label>
                </div>

                <div class="form-grid form-grid--2">
                    <label class="field-card">
                        <span>Data de conclusao</span>
                        <input type="datetime-local" name="completed_at" class="field-input">
                    </label>
                    <div class="summary-card">
                        <p class="card-kicker">Regra operacional</p>
                        <h3 class="card-title">Sem duplicidade</h3>
                        <p class="card-subtitle">O sistema impede o mesmo aluno de ser matriculado duas vezes no mesmo curso.</p>
                    </div>
                </div>

                <div class="field-card">
                    <span>Alunos</span>
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        <?php foreach ($students as $student): ?>
                            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 px-4 py-3 dark:border-slate-700">
                                <input type="checkbox" name="student_ids[]" value="<?= (int) $student['id'] ?>" class="mt-1 h-4 w-4 rounded border-slate-300">
                                <span class="text-sm">
                                    <span class="block font-medium"><?= e($student['full_name']) ?></span>
                                    <span class="text-slate-500 dark:text-slate-400"><?= e((string) ($student['email'] ?? $student['cpf'] ?? 'Sem identificador')) ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="action-button action-button--primary">Criar matriculas</button>
            </form>
        </section>

        <section class="admin-card filter-card">
            <div class="card-header card-header--tight">
                <div>
                    <p class="card-kicker">Filtros e pesquisa</p>
                    <h2 class="card-title">Consultar matriculas</h2>
                    <p class="card-subtitle">Refine por busca, curso, aluno e status para encontrar rapidamente o grupo operacional correto.</p>
                </div>
            </div>

            <form method="GET" action="<?= e(url('/matriculas')) ?>" class="form-grid form-grid--3">
                <label class="field-card">
                    <span>Busca</span>
                    <input type="text" name="search" value="<?= e((string) $filters['search']) ?>" placeholder="Buscar aluno ou curso" class="field-input">
                </label>
                <label class="field-card">
                    <span>Status</span>
                    <select name="status" class="field-input">
                        <option value="">Todos os status</option>
                        <?php foreach (['active' => 'Ativa', 'completed' => 'Concluida', 'cancelled' => 'Cancelada'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="field-card">
                    <span>Curso</span>
                    <select name="course_id" class="field-input">
                        <option value="0">Todos os cursos</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= (int) $course['id'] ?>" <?= (int) $filters['course_id'] === (int) $course['id'] ? 'selected' : '' ?>><?= e($course['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="field-card">
                    <span>Aluno</span>
                    <select name="student_id" class="field-input">
                        <option value="0">Todos os alunos</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= (int) $student['id'] ?>" <?= (int) $filters['student_id'] === (int) $student['id'] ? 'selected' : '' ?>><?= e($student['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="page-actions" style="align-self: end;">
                    <button type="submit" class="action-button action-button--primary">Filtrar</button>
                    <a href="<?= e(url('/matriculas')) ?>" class="action-button">Limpar</a>
                </div>
            </form>

            <div class="mt-4 flex flex-wrap gap-3">
                <a href="<?= e(url('/matriculas/exportar/csv?' . http_build_query(array_filter($filters, static fn ($value) => $value !== '' && $value !== 0)))) ?>" class="action-button">Exportar CSV</a>
                <a href="<?= e(url('/matriculas/exportar/pdf?' . http_build_query(array_filter($filters, static fn ($value) => $value !== '' && $value !== 0)))) ?>" class="action-button">Exportar PDF</a>
            </div>
        </section>

        <section class="admin-card table-card">
            <div class="card-header">
                <div>
                    <p class="card-kicker">Listagem</p>
                    <h2 class="card-title">Matriculas registradas</h2>
                    <p class="card-subtitle">Atualize status, acompanhe conclusoes e mantenha o fluxo operacional centralizado.</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Aluno</th>
                            <th>Curso</th>
                            <th>Status</th>
                            <th>Datas</th>
                            <th>Atualizar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments['items'] as $item): ?>
                            <tr>
                                <td>
                                    <div class="data-table__title"><?= e($item['full_name']) ?></div>
                                    <div class="data-table__meta"><?= e((string) ($item['email'] ?: $item['cpf'] ?: 'Sem identificador')) ?></div>
                                </td>
                                <td>
                                    <div class="data-table__title"><?= e($item['course_name']) ?></div>
                                    <div class="data-table__meta"><?= (int) $item['workload_hours'] ?>h</div>
                                </td>
                                <td>
                                    <span class="badge <?= $item['status'] === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : ($item['status'] === 'cancelled' ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300') ?>">
                                        <?= e($item['status']) ?>
                                    </span>
                                </td>
                                <td class="data-table__meta">
                                    Matricula: <?= e(format_date_br(substr((string) $item['enrolled_at'], 0, 10))) ?>
                                    <?php if (!empty($item['completed_at'])): ?>
                                        <br>Conclusao: <?= e(format_date_br(substr((string) $item['completed_at'], 0, 10))) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="<?= e(url('/matriculas/status/' . (int) $item['id'])) ?>" class="form-grid">
                                        <select name="status" class="field-input">
                                            <?php foreach (['active' => 'Ativa', 'completed' => 'Concluida', 'cancelled' => 'Cancelada'] as $value => $label): ?>
                                                <option value="<?= e($value) ?>" <?= $item['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="date" name="completed_at" value="<?= !empty($item['completed_at']) ? e(substr((string) $item['completed_at'], 0, 10)) : '' ?>" class="field-input">
                                        <button type="submit" class="action-button action-button--primary">Salvar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php
            $pagination = $enrollments;
            $baseUrl = url('/matriculas');
            $query = $filters;
            require base_path('app/Views/partials/pagination.php');
            ?>
        </section>

        <section class="summary-grid">
            <article class="summary-card">
                <p class="card-kicker">Relatorio por curso</p>
                <h3 class="card-title">Alunos vinculados</h3>
                <p class="card-subtitle">Selecione um curso nos filtros para visualizar os alunos vinculados.</p>
                <?php if ($courseReport !== []): ?>
                    <div class="mt-4 space-y-3">
                        <?php foreach ($courseReport as $item): ?>
                            <div class="rounded-2xl border border-slate-200 px-4 py-3 dark:border-slate-700">
                                <p class="font-medium"><?= e($item['full_name']) ?></p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400"><?= e($item['status']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
            <article class="summary-card">
                <p class="card-kicker">Relatorio por aluno</p>
                <h3 class="card-title">Cursos do aluno</h3>
                <p class="card-subtitle">Selecione um aluno nos filtros para ver todos os cursos em que ele esta matriculado.</p>
                <?php if ($studentReport !== []): ?>
                    <div class="mt-4 space-y-3">
                        <?php foreach ($studentReport as $item): ?>
                            <div class="rounded-2xl border border-slate-200 px-4 py-3 dark:border-slate-700">
                                <p class="font-medium"><?= e($item['course_name']) ?></p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400"><?= e($item['status']) ?> | <?= (int) $item['workload_hours'] ?>h</p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
            <article class="summary-card">
                <p class="card-kicker">Certificacao</p>
                <h3 class="card-title">Pronto para emissao</h3>
                <p class="card-subtitle">Matriculas com status `completed` entram automaticamente no fluxo de emissao por curso na tela principal.</p>
            </article>
        </section>
    </section>
</main>
