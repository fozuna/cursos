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
- `public/storage/certificados`: PDFs e ZIPs gerados
- `public/uploads`: planilhas enviadas

## Instalacao

```bash
composer install
copy .env.example .env
php database/migrate.php
```

Aponte o virtual host para `public/`.

## Deploy Hostinger

Fluxo preparado para hospedagem compartilhada Hostinger no diretorio `cursos.traxter.com.br`, sem exigir ajuste manual do document root para `public/`.

### Publicacao automatizada via GitHub Actions

1. Versione o projeto no branch `main`.
2. No repositorio GitHub, configure os secrets:
   - `HOSTINGER_ENV_FILE`: conteudo completo do arquivo `.env` de producao
   - `HOSTINGER_FTP_SERVER`: host FTP/FTPS da Hostinger
   - `HOSTINGER_FTP_USERNAME`: usuario FTP
   - `HOSTINGER_FTP_PASSWORD`: senha FTP
   - `HOSTINGER_DEPLOY_PATH`: caminho remoto final, por exemplo `/domains/cursos.traxter.com.br/public_html/`
3. Cada push para `main` executa o workflow `.github/workflows/deploy-hostinger.yml`.
4. O workflow:
   - instala dependencias sem `dev`
   - gera um pacote flat em `dist/hostinger/cursos.traxter.com.br`
   - publica os arquivos por FTPS na Hostinger

### Build local do pacote Hostinger

```bash
composer install
composer build:hostinger
```

O pacote final sera gerado em:

- `dist/hostinger/cursos.traxter.com.br`

### Estrutura do pacote publicado

- `index.php` fica na raiz do deploy
- `assets/` vai para a raiz publica
- `uploads/` e `storage/` sao criados na raiz do deploy
- `app/`, `config/`, `database/`, `routes/` e `vendor/` seguem no mesmo pacote
- `.htaccess` na raiz bloqueia acesso direto a diretorios sensiveis

### Ambiente de producao recomendado

Use um `.env` com valores como:

```env
APP_URL=https://cursos.traxter.com.br
APP_USE_INDEX=false
APP_ENV=production
APP_DEBUG=false
APP_PUBLIC_PATH=/home/SEU_USUARIO/domains/cursos.traxter.com.br/public_html
APP_PUBLIC_STORAGE_PATH=/home/SEU_USUARIO/domains/cursos.traxter.com.br/public_html/storage/certificados
APP_UPLOAD_PATH=/home/SEU_USUARIO/domains/cursos.traxter.com.br/public_html/uploads
INSTITUTION_CNPJ=30.358.115/0001-13
INSTITUTION_LOGO_PATH=assets/images/logo-escura.png
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seu_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### Observacoes

- O deploy automatizado nao envia `.env` versionado; ele usa o secret `HOSTINGER_ENV_FILE`.
- O build Hostinger converte a estrutura local baseada em `public/` para uma estrutura flat adequada a hospedagem compartilhada.
- Para validar em producao apos o primeiro deploy, acesse `https://cursos.traxter.com.br`.

## Emissao Com Conteudo Programatico

- O certificado agora e emitido em duas paginas:
- Frente: dados principais, assinaturas, QR Code e validacao.
- Verso: conteudo programatico do curso.

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

### Testes

```bash
php database/migrate.php
vendor\bin\phpunit
```

Os testes cobrem:

- validacao unitaria do conteudo programatico
- renderizacao integrada da frente e do verso do certificado
- cenarios com conteudo enxuto e conteudo detalhado
