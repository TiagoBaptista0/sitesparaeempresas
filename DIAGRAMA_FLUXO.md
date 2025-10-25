# 📊 Diagrama de Fluxo e Conexões

## 1. FLUXO COMPLETO DO SISTEMA

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    FLUXO COMPLETO DO SISTEMA                            │
└─────────────────────────────────────────────────────────────────────────┘

1. CADASTRO E LOGIN
═══════════════════════════════════════════════════════════════════════════

    cadastro.php
         ↓
    api/cadastro.php
         ├─ Valida dados
         ├─ Cria usuário em usuarios
         ├─ Gera token de email
         ├─ Insere em email_confirmacao
         └─ Chama api/send-confirmation-email.php
              ├─ Usa PHPMailer
              ├─ Lê SMTP_* do .env
              └─ Envia email com link
                   ↓
              api/confirm-email.php
                   ├─ Valida token
                   ├─ Atualiza email_confirmed=TRUE
                   └─ Redireciona para login.php
                        ↓
                   api/login.php
                        ├─ Valida credenciais
                        ├─ Cria sessão
                        └─ Redireciona para dashboard


2. SELEÇÃO DE DOMÍNIO E PAGAMENTO
═══════════════════════════════════════════════════════════════════════════

    dashboard/domain-selection.php
         ├─ Chama api/domain_search.php
         │   ├─ Lê NAMECHEAP_API_USER do .env
         │   ├─ Lê NAMECHEAP_API_KEY do .env
         │   ├─ Chama api/namecheap_helper.php
         │   │   └─ callNamecheapAPI()
         │   │       ├─ Monta query com credenciais
         │   │       ├─ Chama API (sandbox ou produção)
         │   │       └─ Retorna XML com domínios
         │   └─ Retorna JSON com domínios e preços
         └─ Exibe formulário com domínios
              ↓
         Usuário seleciona domínio e plano
              ↓
         dashboard/payment.php (POST)
              ├─ Valida plano_id, domain, preços
              ├─ Lê MP_ACCESS_TOKEN do .env
              ├─ Cria preferência no Mercado Pago
              │   ├─ Item 1: Plano (R$ X)
              │   ├─ Item 2: Domínio (R$ Y)
              │   └─ URLs de retorno
              ├─ Insere em pagamentos (status='pendente')
              └─ Redireciona para init_point do MP
                   ↓
              Mercado Pago (Checkout)
                   ├─ Usuário realiza pagamento
                   └─ Retorna para dashboard/payment-success.php
                        ↓
                   dashboard/payment-success.php
                        ├─ Recebe: payment_id, preference_id
                        ├─ Busca pagamento no MP
                        ├─ Atualiza pagamentos (status='aprovado')
                        ├─ Cria assinatura
                        └─ Exibe mensagem de sucesso


3. WEBHOOK DO MERCADO PAGO (Confirmação)
═══════════════════════════════════════════════════════════════════════════

    Mercado Pago envia notificação
         ↓
    api/mercadopago_webhook.php
         ├─ Recebe JSON com payment_id
         ├─ Lê MP_ACCESS_TOKEN do .env
         ├─ Busca pagamento no MP
         ├─ Valida status='approved'
         ├─ Atualiza pagamentos
         ├─ Chama registerDomainAutomatically()
         │   ├─ Lê NAMECHEAP_API_USER do .env
         │   ├─ Lê NAMECHEAP_API_KEY do .env
         │   ├─ Chama api/namecheap_helper.php
         │   │   └─ callNamecheapAPI()
         │   │       ├─ Registra domínio
         │   │       └─ Retorna domain_id
         │   ├─ Atualiza clientes (status='dominio_registrado')
         │   └─ Chama setDNS()
         │       ├─ Lê NAMESERVERS do .env
         │       ├─ Chama callNamecheapAPI()
         │       └─ Atualiza clientes (status='dns_configurado')
         ├─ Cria assinatura
         └─ Retorna JSON com sucesso


4. DASHBOARD DO USUÁRIO
═══════════════════════════════════════════════════════════════════════════

    dashboard/index.php
         ├─ Verifica login (config/functions.php)
         ├─ Busca dados do usuário
         ├─ Busca assinaturas ativas
         ├─ Busca clientes (domínios)
         └─ Exibe dashboard com informações


5. ADMIN
═══════════════════════════════════════════════════════════════════════════

    admin/clientes.php
         ├─ Verifica se é admin
         ├─ Lista todos os clientes
         └─ Permite gerenciar

    admin/pagamentos.php
         ├─ Verifica se é admin
         ├─ Lista todos os pagamentos
         └─ Permite gerenciar

    admin/cliente.php
         ├─ Verifica se é admin
         ├─ Exibe detalhes do cliente
         └─ Permite editar
```

---

## 2. FLUXO DE DADOS NO BANCO DE DADOS

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    FLUXO DE DADOS NO BANCO                              │
└─────────────────────────────────────────────────────────────────────────┘

CADASTRO
════════════════════════════════════════════════════════════════════════════

    usuarios
    ├─ id: 1
    ├─ nome: "João Silva"
    ├─ email: "joao@example.com"
    ├─ email_confirmed: FALSE
    └─ email_token: "abc123..."
         ↓
    email_confirmacao
    ├─ usuario_id: 1
    ├─ token: "abc123..."
    └─ data_expiracao: NOW() + 24h
         ↓
    [Email enviado]
         ↓
    usuarios (atualizado)
    └─ email_confirmed: TRUE


PAGAMENTO
════════════════════════════════════════════════════════════════════════════

    pagamentos
    ├─ id: 1
    ├─ usuario_id: 1
    ├─ plano_id: 2
    ├─ dominio: "meusite.com"
    ├─ valor: 780.00
    ├─ valor_plano: 490.00
    ├─ valor_dominio: 290.00
    ├─ status: "pendente"
    ├─ metodo_pagamento: "mercadopago"
    └─ mercadopago_payment_id: NULL
         ↓
    [Usuário vai para Mercado Pago]
         ↓
    pagamentos (atualizado)
    ├─ status: "aprovado"
    ├─ mercadopago_payment_id: "12345678"
    └─ data_pagamento: NOW()
         ↓
    assinaturas
    ├─ usuario_id: 1
    ├─ plano_id: 2
    ├─ status: "ativa"
    ├─ data_inicio: TODAY
    ├─ data_fim: TODAY + 1 month
    └─ valor: 490.00
         ↓
    clientes
    ├─ user_id: 1
    ├─ dominio: "meusite.com"
    ├─ status: "aguardando_dominio_registro"
    ├─ plano_id: 2
    └─ namecheap_order_id: NULL
         ↓
    [Webhook registra domínio]
         ↓
    clientes (atualizado)
    ├─ status: "dominio_registrado"
    ├─ namecheap_domain_id: "987654"
    └─ namecheap_order_id: "555555"
         ↓
    [Webhook configura DNS]
         ↓
    clientes (atualizado)
    ├─ status: "dns_configurado"
    └─ [Pronto para usar]
         ↓
    sites
    ├─ usuario_id: 1
    ├─ dominio: "meusite.com"
    ├─ status: "em_construcao"
    └─ template: "padrao"
```

---

## 3. FLUXO DE VARIÁVEIS DE AMBIENTE

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    FLUXO DE VARIÁVEIS DE AMBIENTE                       │
└─────────────────────────────────────────────────────────────────────────┘

.env (arquivo)
├─ DB_HOST=localhost
├─ DB_NAME=sitesparaempresas
├─ DB_USER=root
├─ DB_PASS=
├─ MP_PUBLIC_KEY=TEST-xxxxx
├─ MP_ACCESS_TOKEN=TEST-xxxxx
├─ NAMECHEAP_API_USER=usuario
├─ NAMECHEAP_API_KEY=chave
├─ SMTP_HOST=smtp.hostinger.com
├─ SMTP_PORT=465
├─ SMTP_USERNAME=email@dominio.com
├─ SMTP_PASSWORD=senha
├─ EMAIL_FROM=noreply@dominio.com
├─ NAMESERVERS=ns1.namecheap.com,ns2.namecheap.com
└─ MERCADOPAGO_WEBHOOK_URL=https://webhook.site/xxxxx
     ↓
config/db.php (lê .env)
├─ Abre arquivo .env
├─ Faz parse linha por linha
├─ Carrega em $_ENV
├─ Carrega em putenv()
└─ Define constantes
     ├─ DB_HOST
     ├─ DB_NAME
     ├─ DB_USER
     ├─ DB_PASS
     ├─ MP_PUBLIC_KEY
     ├─ MP_ACCESS_TOKEN
     ├─ SMTP_HOST
     ├─ SMTP_PORT
     ├─ SMTP_USERNAME
     ├─ SMTP_PASSWORD
     ├─ EMAIL_FROM
     ├─ EMAIL_NAME
     └─ BASE_URL (calculada)
          ↓
Usadas em:
├─ config/functions.php
│   └─ enviarEmail() usa SMTP_*
├─ api/namecheap_helper.php
│   └─ callNamecheapAPI() usa NAMECHEAP_*
├─ dashboard/payment.php
│   └─ Cria preferência MP usa MP_ACCESS_TOKEN
├─ api/mercadopago_webhook.php
│   └─ Busca pagamento usa MP_ACCESS_TOKEN
└─ Todos os arquivos usam BASE_URL
```

---

## 4. FLUXO DE REQUISIÇÕES HTTP

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    FLUXO DE REQUISIÇÕES HTTP                            │
└─────────────────────────────────────────────────────────────────────────┘

DESENVOLVIMENTO (Local)
════════════════════════════════════════════════════════════════════════════

Usuário
  ↓
http://localhost/sitesparaeempresas/cadastro.php
  ├─ GET: Exibe formulário
  └─ POST: Envia dados
       ↓
api/cadastro.php
  ├─ Valida dados
  ├─ Cria usuário
  └─ Retorna JSON
       ↓
api/send-confirmation-email.php
  ├─ Envia email via SMTP
  └─ Retorna sucesso/erro
       ↓
Email recebido
  ├─ Link: http://localhost/sitesparaeempresas/api/confirm-email.php?token=xxx
  └─ Usuário clica
       ↓
api/confirm-email.php
  ├─ Valida token
  ├─ Atualiza usuário
  └─ Redireciona para login


PRODUÇÃO (Hospedagem)
════════════════════════════════════════════════════════════════════════════

Usuário
  ↓
https://seu-dominio.com/cadastro.php
  ├─ GET: Exibe formulário
  └─ POST: Envia dados
       ↓
api/cadastro.php
  ├─ Valida dados
  ├─ Cria usuário
  └─ Retorna JSON
       ↓
api/send-confirmation-email.php
  ├─ Envia email via SMTP
  └─ Retorna sucesso/erro
       ↓
Email recebido
  ├─ Link: https://seu-dominio.com/api/confirm-email.php?token=xxx
  └─ Usuário clica
       ↓
api/confirm-email.php
  ├─ Valida token
  ├─ Atualiza usuário
  └─ Redireciona para login


WEBHOOK DO MERCADO PAGO
════════════════════════════════════════════════════════════════════════════

Mercado Pago (Servidor)
  ↓
POST https://seu-dominio.com/api/mercadopago_webhook.php
  ├─ Headers: Content-Type: application/json
  └─ Body: {"type": "payment", "data": {"id": "12345678"}}
       ↓
api/mercadopago_webhook.php
  ├─ Recebe JSON
  ├─ Valida payment_id
  ├─ Busca pagamento no MP
  ├─ Atualiza banco de dados
  ├─ Registra domínio no Namecheap
  └─ Retorna JSON com sucesso
       ↓
Mercado Pago recebe resposta 200 OK
```

---

## 5. FLUXO DE CHAMADAS DE API

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    FLUXO DE CHAMADAS DE API                             │
└─────────────────────────────────────────────────────────────────────────┘

NAMECHEAP API
════════════════════════════════════════════════════════════════════════════

api/namecheap_helper.php
  ├─ Lê NAMECHEAP_API_USER do .env
  ├─ Lê NAMECHEAP_API_KEY do .env
  ├─ Lê APP_ENV do .env
  └─ Define URL (sandbox ou produção)
       ↓
callNamecheapAPI($params)
  ├─ Monta query string com credenciais
  ├─ Chama URL via file_get_contents()
  ├─ Recebe XML
  ├─ Faz parse com simplexml_load_string()
  └─ Retorna array com sucesso/erro
       ↓
Usado em:
├─ api/domain_search.php
│   └─ Command: namecheap.domains.getList
├─ api/register_domain.php
│   └─ Command: namecheap.domains.create
├─ api/set_dns.php
│   └─ Command: namecheap.domains.dns.setCustom
└─ api/mercadopago_webhook.php
    └─ registerDomainAutomatically()


MERCADO PAGO API
════════════════════════════════════════════════════════════════════════════

dashboard/payment.php
  ├─ Lê MP_ACCESS_TOKEN do .env
  ├─ Cria preferência
  └─ Retorna init_point
       ↓
Usuário vai para init_point
  ├─ Realiza pagamento
  └─ Retorna para payment-success.php
       ↓
dashboard/payment-success.php
  ├─ Lê MP_ACCESS_TOKEN do .env
  ├─ Busca pagamento via Payment::find_by_id()
  └─ Atualiza banco de dados
       ↓
Mercado Pago envia webhook
  ↓
api/mercadopago_webhook.php
  ├─ Lê MP_ACCESS_TOKEN do .env
  ├─ Busca pagamento via Payment::find_by_id()
  ├─ Valida status
  └─ Atualiza banco de dados


SMTP (Email)
════════════════════════════════════════════════════════════════════════════

config/functions.php::enviarEmail()
  ├─ Lê SMTP_HOST do .env
  ├─ Lê SMTP_PORT do .env
  ├─ Lê SMTP_USERNAME do .env
  ├─ Lê SMTP_PASSWORD do .env
  ├─ Lê SMTP_ENCRYPTION do .env
  ├─ Lê EMAIL_FROM do .env
  ├─ Lê EMAIL_NAME do .env
  └─ Usa PHPMailer
       ↓
PHPMailer
  ├─ Conecta ao SMTP
  ├─ Autentica
  ├─ Envia email
  └─ Retorna sucesso/erro
```

---

## 6. CHECKLIST DE CONEXÕES

### 6.1 Configuração
- [x] `.env` carregado em `config/db.php`
- [x] Constantes definidas em `config/db.php`
- [x] Funções definidas em `config/functions.php`
- [x] Banco de dados conectado em `config/db.php`

### 6.2 Autenticação
- [x] Cadastro em `api/cadastro.php`
- [x] Email de confirmação em `api/send-confirmation-email.php`
- [x] Confirmação de email em `api/confirm-email.php`
- [x] Login em `api/login.php`
- [x] Logout em `dashboard/logout.php`

### 6.3 Domínios
- [x] Busca em `api/domain_search.php`
- [x] Seleção em `dashboard/domain-selection.php`
- [x] Registro em `api/register_domain.php`
- [x] DNS em `api/set_dns.php`

### 6.4 Pagamentos
- [x] Criação em `dashboard/payment.php`
- [x] Sucesso em `dashboard/payment-success.php`
- [x] Erro em `dashboard/payment-error.php`
- [x] Webhook em `api/mercadopago_webhook.php`

### 6.5 Dashboard
- [x] Index em `dashboard/index.php`
- [x] Perfil em `dashboard/perfil.php`
- [x] Meu Site em `dashboard/meu-site.php`
- [x] Pagamentos em `dashboard/pagamentos.php`
- [x] Suporte em `dashboard/suporte.php`

### 6.6 Admin
- [x] Clientes em `admin/clientes.php`
- [x] Cliente em `admin/cliente.php`
- [x] Pagamentos em `admin/pagamentos.php`

---

## 7. RESUMO DE MUDANÇAS PARA PRODUÇÃO

```
DESENVOLVIMENTO                          PRODUÇÃO
═══════════════════════════════════════════════════════════════════════════

.env:
BASE_URL=http://localhost/...    →    BASE_URL=https://seu-dominio.com
APP_ENV=development              →    APP_ENV=production
DB_HOST=localhost                →    DB_HOST=seu_host_produção
MP_PUBLIC_KEY=TEST-xxxxx         →    MP_PUBLIC_KEY=PROD-xxxxx
MP_ACCESS_TOKEN=TEST-xxxxx       →    MP_ACCESS_TOKEN=PROD-xxxxx
NAMECHEAP_API_URL=sandbox        →    NAMECHEAP_API_URL=produção
SMTP_HOST=localhost              →    SMTP_HOST=smtp.hostinger.com
MERCADOPAGO_WEBHOOK_URL=         →    MERCADOPAGO_WEBHOOK_URL=
  webhook.site/xxxxx                    https://seu-dominio.com/api/...

CÓDIGO: Nenhuma mudança necessária!
```

Tudo funciona através de variáveis de ambiente.
