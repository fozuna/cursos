<?php
$activeNav = $activeNav ?? 'students';
$studentForm = $editingStudent ?? [];
$pageHeaderEyebrow = 'Alunos';
$pageHeaderTitle = 'Cadastro e consulta de alunos';
$pageHeaderSubtitle = 'Mantenha uma base consistente, validada e pronta para matriculas, conclusoes e emissao de certificados.';
$pageHeaderActions = $editingStudent
    ? '<a href="' . e(url('/alunos')) . '" class="action-button">Novo cadastro</a>'
    : '<a href="' . e(url('/alunos/exportar/csv')) . '" class="action-button">Exportar base</a>';
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
                    <h2 class="card-title"><?= $editingStudent ? 'Editar aluno' : 'Novo aluno' ?></h2>
                    <p class="card-subtitle">Nome obrigatorio e demais campos opcionais, com validacoes de formato e duplicidade para manter a base confiavel.</p>
                </div>
                <div class="page-actions">
                    <?php if ($editingStudent): ?>
                        <a href="<?= e(url('/alunos')) ?>" class="action-button">Cancelar edicao</a>
                    <?php endif; ?>
                    <a href="<?= e(url('/alunos/exportar/pdf')) ?>" class="action-button">Resumo PDF</a>
                </div>
            </div>

            <form method="POST" action="<?= e(url('/alunos/salvar')) ?>" class="mt-6 space-y-4">
                <input type="hidden" name="student_id" value="<?= (int) ($studentForm['id'] ?? 0) ?>">
                <div class="form-grid form-grid--3">
                    <label class="field-card" style="grid-column: span 2;">
                        <span>Nome completo</span>
                        <input type="text" name="full_name" value="<?= e((string) ($studentForm['full_name'] ?? '')) ?>" class="field-input" required>
                    </label>
                    <label class="field-card">
                        <span>E-mail</span>
                        <input type="email" name="email" value="<?= e((string) ($studentForm['email'] ?? '')) ?>" class="field-input">
                    </label>
                    <label class="field-card">
                        <span>Telefone</span>
                        <input type="text" name="phone" value="<?= e((string) ($studentForm['phone'] ?? '')) ?>" class="field-input" data-phone-mask placeholder="(00) 00000-0000">
                    </label>
                    <label class="field-card">
                        <span>CPF</span>
                        <input type="text" name="cpf" value="<?= e((string) ($studentForm['cpf'] ?? '')) ?>" class="field-input" data-cpf-mask placeholder="000.000.000-00">
                    </label>
                </div>
                <button type="submit" class="action-button action-button--primary">
                    <?= $editingStudent ? 'Salvar alteracoes' : 'Cadastrar aluno' ?>
                </button>
            </form>
        </section>

        <section class="admin-card filter-card">
            <div class="card-header card-header--tight">
                <div>
                    <p class="card-kicker">Filtros e pesquisa</p>
                    <h2 class="card-title">Localizar alunos</h2>
                    <p class="card-subtitle">Pesquise por nome, e-mail, telefone ou CPF para encontrar rapidamente o cadastro correto.</p>
                </div>
            </div>
            <form method="GET" action="<?= e(url('/alunos')) ?>" class="form-grid form-grid--3">
                <label class="field-card" style="grid-column: span 2;">
                    <span>Busca principal</span>
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Buscar por nome, e-mail, telefone ou CPF" class="field-input">
                </label>
                <div class="page-actions">
                    <button type="submit" class="action-button action-button--primary">Buscar alunos</button>
                    <a href="<?= e(url('/alunos')) ?>" class="action-button">Limpar</a>
                </div>
            </form>

            <div class="mt-4 flex flex-wrap gap-3">
                <a href="<?= e(url('/alunos/exportar/csv' . ($search !== '' ? '?search=' . urlencode($search) : ''))) ?>" class="action-button">Exportar CSV</a>
                <a href="<?= e(url('/alunos/exportar/pdf' . ($search !== '' ? '?search=' . urlencode($search) : ''))) ?>" class="action-button">Exportar PDF</a>
            </div>
        </section>

        <section class="admin-card table-card">
            <div class="card-header">
                <div>
                    <p class="card-kicker">Listagem</p>
                    <h2 class="card-title">Base de alunos</h2>
                    <p class="card-subtitle">Acompanhe dados cadastrais e acione manutencao rapida com foco operacional.</p>
                </div>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Aluno</th>
                            <th>Contato</th>
                            <th>CPF</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students['items'] as $student): ?>
                            <tr>
                                <td>
                                    <div class="data-table__title"><?= e($student['full_name']) ?></div>
                                    <div class="data-table__meta">ID interno #<?= (int) $student['id'] ?></div>
                                </td>
                                <td>
                                    <div><?= e((string) ($student['email'] ?? 'Sem e-mail')) ?></div>
                                    <div class="data-table__meta"><?= e((string) ($student['phone'] ?? 'Sem telefone')) ?></div>
                                </td>
                                <td><?= e((string) ($student['cpf'] ?? 'Nao informado')) ?></td>
                                <td>
                                    <div class="page-actions">
                                        <a href="<?= e(url('/alunos?edit=' . (int) $student['id'])) ?>" class="action-button">Editar</a>
                                        <form method="POST" action="<?= e(url('/alunos/excluir/' . (int) $student['id'])) ?>" onsubmit="return confirm('Deseja remover este aluno?');">
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
            $pagination = $students;
            $baseUrl = url('/alunos');
            $query = ['search' => $search];
            require base_path('app/Views/partials/pagination.php');
            ?>
        </section>

        <section class="summary-grid">
            <article class="summary-card">
                <p class="card-kicker">Qualidade cadastral</p>
                <h3 class="card-title">Dados mais confiaveis</h3>
                <p class="card-subtitle">E-mail e CPF seguem validacao e bloqueio de duplicidade para reduzir erros operacionais.</p>
            </article>
            <article class="summary-card">
                <p class="card-kicker">Busca</p>
                <h3 class="card-title">Consulta rapida</h3>
                <p class="card-subtitle">A busca percorre nome, e-mail, telefone e CPF, facilitando atendimento e manutencao.</p>
            </article>
            <article class="summary-card">
                <p class="card-kicker">Uso no fluxo</p>
                <h3 class="card-title">Pronto para matriculas</h3>
                <p class="card-subtitle">Cada aluno cadastrado fica imediatamente disponivel para ser vinculado aos cursos no modulo de matriculas.</p>
            </article>
        </section>
    </section>
</main>
