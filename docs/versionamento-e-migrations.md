# Fluxo de Versionamento e Migrations

Este projeto adota um fluxo simples e obrigatorio:

`Trae -> alteracoes locais -> validacao local -> commit -> push -> GitHub -> Hostinger`

## Regra obrigatoria de versionamento

Sempre que um conjunto coerente de alteracoes for concluido:

1. revisar os arquivos alterados;
2. executar as validacoes aplicaveis;
3. criar `commit` com mensagem objetiva;
4. executar `push` imediatamente para `main`.

Nao deixar lotes extensos de mudancas apenas locais quando a entrega ja estiver pronta.

## Regra obrigatoria para mudancas de schema

Sempre que houver alteracao na estrutura do banco ou no formato dos dados persistidos:

1. criar uma nova migration em `database/migrations`;
2. usar um nome descritivo e sequencial;
3. documentar claramente o objetivo da migration no commit e no `CHANGELOG.md`;
4. executar a migration em ambiente de desenvolvimento;
5. confirmar que a aplicacao continua funcional apos a migration;
6. validar o procedimento de rollback manual quando aplicavel;
7. somente depois disso realizar `commit` e `push`.

## Padrao de migrations

- Cada mudanca estrutural deve ficar em um novo arquivo.
- Nao editar migrations antigas que ja possam ter sido executadas em outros ambientes.
- Preferir scripts pequenos, objetivos e reversiveis sempre que tecnicamente possivel.

Exemplos:

- `004_add_indexes_to_enrollments.sql`
- `005_add_users_table.sql`
- `006_alter_courses_certificate_fields.sql`

## Checklist obrigatorio antes de commit com schema

- Nova migration criada
- Migration executada localmente com sucesso
- Impacto na aplicacao revisado
- Rollback pensado e documentado
- `CHANGELOG.md` atualizado
- Testes/checagens executados
- So entao `commit`
- So entao `push`

## Rollback

Este projeto nao usa framework de migration com rollback automatico. Portanto:

- o rollback deve ser avaliado no momento da criacao da migration;
- quando necessario, a reversao deve ser documentada no proprio contexto da entrega;
- mudancas destrutivas exigem atencao redobrada e devem ser evitadas sem necessidade real.

## Mensagens de commit recomendadas

- `feat: adicionar modulo de usuarios`
- `fix: corrigir validacao de matriculas duplicadas`
- `refactor: padronizar layout vertical das telas administrativas`
- `docs: documentar fluxo de versionamento e migrations`

## Responsabilidade operacional

Antes de qualquer `push` no branch principal:

- confirmar que o repositório local esta consistente;
- confirmar que as migrations necessarias foram aplicadas em desenvolvimento;
- confirmar que a documentacao minima da mudanca foi atualizada.
