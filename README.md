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
