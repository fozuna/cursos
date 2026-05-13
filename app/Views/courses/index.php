<?php
$activeNav = $activeNav ?? 'courses';
$courseForm = $editingCourse ?? [];
$programValues = [
    'program_syllabus' => (string) ($courseForm['program_syllabus'] ?? $programSectionDefaults['syllabus']),
    'program_objectives' => (string) ($courseForm['program_objectives'] ?? $programSectionDefaults['objectives']),
    'program_modules' => (string) ($courseForm['program_modules'] ?? $programSectionDefaults['modules']),
    'program_methodology' => (string) ($courseForm['program_methodology'] ?? $programSectionDefaults['methodology']),
    'program_evaluation' => (string) ($courseForm['program_evaluation'] ?? $programSectionDefaults['evaluation']),
];
$pageHeaderEyebrow = 'Cursos';
$pageHeaderTitle = 'Cadastro e operacao de cursos';
$pageHeaderSubtitle = 'Gerencie catalogo, conteudo programatico e disponibilidade para matriculas e emissao automatica de certificados.';
$pageHeaderActions = $editingCourse
    ? '<a href="' . e(url('/cursos')) . '" class="action-button">Novo cadastro</a>'
    : '<a href="' . e(url('/cursos/exportar/pdf')) . '" class="action-button">Resumo PDF</a>';
require base_path('app/Views/partials/admin_nav.php');
?>

<main class="admin-shell">
    <?php require base_path('app/Views/partials/flash.php'); ?>
    <?php require base_path('app/Views/partials/page_header.php'); ?>

    <section class="section-stack">
        <section class="admin-card form-card">
            <div class="card-header">
                <div>
                    <p class="card-kicker">Card de cadastro</p>
                    <h2 class="card-title"><?= $editingCourse ? 'Editar curso' : 'Novo curso' ?></h2>
                    <p class="card-subtitle">Organize os dados essenciais para certificados, cronograma e conteudo programatico em um unico fluxo.</p>
                </div>
                <div class="page-actions">
                    <?php if ($editingCourse): ?>
                        <a href="<?= e(url('/cursos')) ?>" class="action-button">Cancelar edicao</a>
                    <?php endif; ?>
                    <a href="<?= e(url('/cursos/exportar/csv')) ?>" class="action-button">Exportar CSV</a>
                </div>
            </div>

            <form method="POST" action="<?= e(url('/cursos/salvar')) ?>" class="mt-6 space-y-4">
                <input type="hidden" name="course_id" value="<?= (int) ($courseForm['id'] ?? 0) ?>">
                <div class="form-grid form-grid--3">
                    <label class="field-card">
                        <span>Nome do curso</span>
                        <input type="text" name="name" value="<?= e((string) ($courseForm['name'] ?? '')) ?>" class="field-input" required>
                    </label>
                    <label class="field-card">
                        <span>Carga horaria</span>
                        <input type="number" name="workload_hours" value="<?= e((string) ($courseForm['workload_hours'] ?? '')) ?>" class="field-input" required>
                    </label>
                    <label class="field-card">
                        <span>Data de inicio</span>
                        <input type="date" name="start_date" value="<?= e((string) ($courseForm['start_date'] ?? '')) ?>" class="field-input" required>
                    </label>
                    <label class="field-card">
                        <span>Data de termino</span>
                        <input type="date" name="end_date" value="<?= e((string) ($courseForm['end_date'] ?? '')) ?>" class="field-input" required>
                    </label>
                    <label class="field-card">
                        <span>Instrutor</span>
                        <input type="text" name="instructor_name" value="<?= e((string) ($courseForm['instructor_name'] ?? '')) ?>" class="field-input" required>
                    </label>
                    <label class="field-card">
                        <span>Instituicao executora</span>
                        <input type="text" name="institution_name" value="<?= e((string) ($courseForm['institution_name'] ?? $company['name'])) ?>" class="field-input" required>
                    </label>
                    <label class="field-card">
                        <span>Prefixo do certificado</span>
                        <input type="text" name="certificate_prefix" value="<?= e((string) ($courseForm['certificate_prefix'] ?? 'CERT')) ?>" class="field-input" required>
                    </label>
                    <label class="field-card flex items-center gap-3">
                        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300" <?= !isset($courseForm['is_active']) || (int) $courseForm['is_active'] === 1 ? 'checked' : '' ?>>
                        <span>Curso ativo para matriculas e certificados</span>
                    </label>
                </div>

                <div class="form-grid">
                    <label class="field-card">
                        <span><?= e($programSectionLabels['syllabus']) ?></span>
                        <textarea name="program_syllabus" rows="3" class="field-input min-h-28" required><?= e($programValues['program_syllabus']) ?></textarea>
                    </label>
                    <label class="field-card">
                        <span><?= e($programSectionLabels['objectives']) ?></span>
                        <textarea name="program_objectives" rows="3" class="field-input min-h-28" required><?= e($programValues['program_objectives']) ?></textarea>
                    </label>
                    <label class="field-card">
                        <span><?= e($programSectionLabels['modules']) ?></span>
                        <textarea name="program_modules" rows="5" class="field-input min-h-32" required><?= e($programValues['program_modules']) ?></textarea>
                    </label>
                    <div class="form-grid form-grid--2">
                        <label class="field-card">
                            <span><?= e($programSectionLabels['methodology']) ?></span>
                            <textarea name="program_methodology" rows="4" class="field-input min-h-28" required><?= e($programValues['program_methodology']) ?></textarea>
                        </label>
                        <label class="field-card">
                            <span><?= e($programSectionLabels['evaluation']) ?></span>
                            <textarea name="program_evaluation" rows="4" class="field-input min-h-28" required><?= e($programValues['program_evaluation']) ?></textarea>
                        </label>
                    </div>
                </div>

                <button type="submit" class="action-button action-button--primary">
                    <?= $editingCourse ? 'Salvar alteracoes' : 'Cadastrar curso' ?>
                </button>
            </form>
        </section>

        <section class="admin-card filter-card">
            <div class="card-header card-header--tight">
                <div>
                    <p class="card-kicker">Filtros e pesquisa</p>
                    <h2 class="card-title">Refinar catalogo</h2>
                    <p class="card-subtitle">Pesquise cursos por nome, instrutor ou instituicao e acione exportacoes do resultado atual.</p>
                </div>
            </div>
            <form method="GET" action="<?= e(url('/cursos')) ?>" class="form-grid form-grid--3">
                <label class="field-card" style="grid-column: span 2;">
                    <span>Busca principal</span>
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Buscar curso, instrutor ou instituicao" class="field-input">
                </label>
                <div class="page-actions">
                    <button type="submit" class="action-button action-button--primary">Buscar cursos</button>
                    <a href="<?= e(url('/cursos')) ?>" class="action-button">Limpar</a>
                </div>
            </form>

            <div class="mt-4 flex flex-wrap gap-3">
                <a href="<?= e(url('/cursos/exportar/csv' . ($search !== '' ? '?search=' . urlencode($search) : ''))) ?>" class="action-button">Exportar CSV</a>
                <a href="<?= e(url('/cursos/exportar/pdf' . ($search !== '' ? '?search=' . urlencode($search) : ''))) ?>" class="action-button">Exportar PDF</a>
            </div>
        </section>

        <section class="admin-card table-card">
            <div class="card-header">
                <div>
                    <p class="card-kicker">Listagem</p>
                    <h2 class="card-title">Cursos cadastrados</h2>
                    <p class="card-subtitle">Acompanhe disponibilidade, periodo e volume operacional de cada curso.</p>
                </div>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Periodo</th>
                            <th>Instrutor</th>
                            <th>Status</th>
                            <th>Matriculas</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses['items'] as $course): ?>
                            <tr>
                                <td>
                                    <div class="data-table__title"><?= e($course['name']) ?></div>
                                    <div class="data-table__meta"><?= e($course['institution_name']) ?> | <?= (int) $course['workload_hours'] ?>h</div>
                                </td>
                                <td><?= e(format_date_br((string) $course['start_date'])) ?><br><span class="data-table__meta"><?= e(format_date_br((string) $course['end_date'])) ?></span></td>
                                <td><?= e($course['instructor_name']) ?></td>
                                <td>
                                    <span class="badge <?= (int) $course['is_active'] === 1 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' ?>">
                                        <?= (int) $course['is_active'] === 1 ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="data-table__title"><?= (int) ($course['active_enrollments'] ?? 0) ?> ativas</div>
                                    <div class="data-table__meta"><?= (int) ($course['completed_enrollments'] ?? 0) ?> concluidas</div>
                                </td>
                                <td>
                                    <div class="page-actions">
                                        <a href="<?= e(url('/cursos?edit=' . (int) $course['id'])) ?>" class="action-button">Editar</a>
                                        <form method="POST" action="<?= e(url('/cursos/excluir/' . (int) $course['id'])) ?>" onsubmit="return confirm('Deseja remover este curso?');">
                                            <button type="submit" class="action-button">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php
            $pagination = $courses;
            $baseUrl = url('/cursos');
            $query = ['search' => $search];
            require base_path('app/Views/partials/pagination.php');
            ?>
        </section>

        <section class="summary-grid">
            <article class="summary-card">
                <p class="card-kicker">Resumo</p>
                <h3 class="card-title">Base ativa</h3>
                <p class="card-subtitle"><?= count($courses['items']) ?> cursos exibidos na consulta atual.</p>
            </article>
            <article class="summary-card">
                <p class="card-kicker">Operacao</p>
                <h3 class="card-title">Emissao automatica</h3>
                <p class="card-subtitle">Cursos ativos ficam imediatamente disponiveis para gerar certificados a partir das matriculas concluidas.</p>
            </article>
            <article class="summary-card">
                <p class="card-kicker">Padrao</p>
                <h3 class="card-title">Conteudo programatico</h3>
                <p class="card-subtitle">As cinco secoes do curso alimentam automaticamente o verso do certificado, sem redigitacao manual.</p>
            </article>
        </section>
    </section>
</main>
