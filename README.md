# Sistema de Certificados em Lote

Aplicacao PHP 8.2+ para importacao de alunos, geracao automatica de certificados premium, exportacao PDF/ZIP e validacao publica por hash e QR Code.

## Stack

- PHP 8.2+
- PDO orientado a objetos
- MariaDB
- Tailwind CSS via CDN
- DomPDF
- Endroid QR Code
- SimpleXLSX
- ZipStream PHP
- PHPUnit

## Estrutura

- `app/Controllers`: fluxo HTTP
- `app/Repositories`: acesso a dados com prepared statements
- `app/Services`: regras de negocio
- `app/Views`: dashboard, preview, validacao e template PDF
- `database/migrations`: schema MariaDB
- `docs/ui-components.md`: guia do sistema visual e componentes reutilizaveis
- `docs/versionamento-e-migrations.md`: politica operacional de versionamento e mudancas de schema
- `CHANGELOG.md`: historico resumido de alteracoes relevantes
- `public/storage/certificados`: PDFs e ZIPs gerados
- `public/uploads`: planilhas enviadas

## Instalacao

```bash
composer install
copy .env.example .env
php database/migrate.php
```

Aponte o virtual host para `public/`.

## Versionamento Obrigatorio

- Toda entrega concluida deve terminar com `commit` e `push` imediato para o repositório remoto.
- Nao acumular alteracoes prontas sem versionamento.
- Mensagens de commit devem ser claras e objetivas.

## Regra Para Migrations

Sempre que houver mudanca de schema ou alteracao estrutural de dados:

- criar nova migration em `database/migrations`;
- testar a migration em desenvolvimento;
- revisar o procedimento de rollback manual quando aplicavel;
- atualizar `CHANGELOG.md`;
- somente depois disso fazer `commit` e `push`.

Guia operacional completo:

- `docs/versionamento-e-migrations.md`

## Emissao Com Conteudo Programatico

- O certificado agora e emitido em duas paginas:
- Frente: dados principais, assinaturas, QR Code e validacao.
- Verso: conteudo programatico do curso.

## Modulos Educacionais

- `Cursos`: cadastro completo com nome, carga horaria, periodo, instrutor, instituicao executora e cinco secoes de conteudo programatico.
- `Alunos`: cadastro com nome obrigatorio e campos opcionais para e-mail, telefone e CPF, com validacoes de formato e duplicidade.
- `Matriculas`: vinculo entre alunos e cursos com status `active`, `completed` e `cancelled`, datas de matricula e conclusao e bloqueio de duplicidade por aluno/curso.

### Fluxo recomendado

- Cadastre o curso no modulo `Cursos`.
- Cadastre os participantes no modulo `Alunos`.
- Crie as matriculas no modulo `Matriculas`, inclusive em lote para varios alunos.
- Marque as matriculas concluidas.
- Na tela inicial, selecione apenas o curso para gerar os certificados automaticamente com base nas matriculas concluidas.

### Exportacoes

- `Cursos`: exportacao CSV e PDF.
- `Alunos`: exportacao CSV e PDF.
- `Matriculas`: exportacao CSV e PDF, com filtros por curso, aluno, status e busca textual.

### Integridade

- Nao e possivel excluir cursos com matriculas ativas.
- Nao e possivel excluir alunos com matriculas vinculadas.
- Nao e possivel matricular o mesmo aluno duas vezes no mesmo curso.

## Sistema Visual

- O layout administrativo segue um padrao vertical com:
- `page-header`
- `form-card`
- `filter-card`
- `table-card`
- `summary-card`
- O guia de uso dos componentes esta em `docs/ui-components.md`.

### Cadastro Pelo Painel

- Acesse o dashboard administrativo.
- Escolha o modo de entrada manual ou importacao CSV/XLSX.
- Preencha obrigatoriamente as cinco secoes do conteudo programatico:
- `Ementa detalhada`
- `Objetivos do curso`
- `Conteudo programatico por modulo/aula`
- `Metodologia utilizada`
- `Criterios de avaliacao`
- Gere o certificado normalmente. O sistema grava o conteudo no banco e monta automaticamente o verso do PDF.

### Validacoes

- O conteudo programatico e obrigatorio antes da emissao.
- O sistema valida o volume total de texto para evitar estouro do verso do certificado.
- Em caso de excesso de texto, resuma as secoes antes de gerar o lote.

## Download Publico do Certificado

- O painel administrativo agora permite gerar um link individual por certificado em `Certificados > Gerar link aluno`.
- O link publico expira em 7 dias e exige a confirmacao do celular do aluno no formato brasileiro `(XX) 9XXXX-XXXX`.
- O acesso so e liberado quando o token esta valido, o telefone corresponde ao aluno do certificado e o PDF ja existe no storage.
- O fluxo inclui protecao CSRF, limitacao de taxa por IP com bloqueio temporario apos 5 tentativas invalidas em 15 minutos e log de preview/download para auditoria.
- Em producao, configure `APP_URL` com `https://` para manter todas as requisicoes publicas em conexao segura.

## Controle de Reemissao

- Cada tentativa de emissao em lote agora calcula uma identidade unica por certificado com base no aluno, curso, data de conclusao e carga horaria.
- O sistema consulta um registro persistente indexado em `certificate_issue_registry` para bloquear automaticamente certificados ja emitidos com sucesso em lotes anteriores.
- O operador responsavel informa o campo `Solicitado por` antes da geracao; esse identificador fica associado ao lote e a cada item processado para auditoria.
- Ao final de cada lote, a tela administrativa exibe um relatorio com itens gerados, bloqueados por reemissao e falhas encontradas no processamento.
- Bloqueios, sucessos e erros tambem sao registrados em `generation_histories` e no log estruturado da aplicacao.

### Testes

```bash
php database/migrate.php
vendor\bin\phpunit
```

Os testes cobrem:

- validacao unitaria do conteudo programatico
- validacao unitaria de cadastro de cursos
- validacao unitaria de cadastro de alunos
- renderizacao integrada da frente e do verso do certificado
- cenarios com conteudo enxuto e conteudo detalhado
- emissao de link publico com validacao por celular e bloqueio por excesso de tentativas invalidas
- identificacao de certificado ja emitido, bloqueio de lote completo repetido e processamento de lote parcial sem reemitir itens antigos
