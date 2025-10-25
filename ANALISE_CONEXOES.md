# 📋 Análise de Conexões e Fluxo da Aplicação

## 1. ESTRUTURA DE CONFIGURAÇÃO

### 1.1 Carregamento de Variáveis de Ambiente
- **Arquivo**: `config/db.php`
- **Método**: Leitura manual do `.env` (linhas 8-24)
- **Variáveis Carregadas**:
  - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
  - `MP_PUBLIC_KEY`, `MP_ACCESS_TOKEN`
  - `APP_NAME`, `EMAIL_FROM`, `EMAIL_NAME`
  - `SMTP_HOST`, `SMTP_PORT`, `SMTP_USERNAME`, `SMTP_PASSWORD`, `SMTP_ENCRYPTION`
  - `BASE_URL` (calculada dinamicamente)

### 1.2 Definições de Constantes
- **Arquivo**: `config/config.php`
- **Conteúdo**: Planos, status de site, status de pagamento

---

## 2. FLUXO DE PAGAMENTO (Mercado Pago)

### 2.1 Fluxo Completo
```
1. dashboard/domain-selection.php
   ↓
2. dashboard/payment.php (POST)
   - Valida plano_id, domain, preços
   - Cria preferência no Mercado Pago
   - Insere registro em pagamentos (status='pendente')
   - Redireciona para init_point do MP
   ↓
3. Mercado Pago (Checkout)
   - Usuário realiza pagamento
   ↓
4. Retorno para dashboard/payment-success.php
   - Recebe: payment_id, preference_id, merchant_order_id
   - Busca dados do pagamento no MP
   - Atualiza status em pagamentos (status='aprovado')
   - Cria assinatura
   ↓
5. Webhook: api/mercadopago_webhook.php
   - Recebe notificação do MP
   - Valida payment_id
   - Atualiza pagamentos
   - Registra domínio automaticamente
   - Cria assinatura
```

### 2.2 Configuração Necessária para Produção

**Arquivo**: `.env`
```
# Mercado Pago (Produção)
MP_PUBLIC_KEY=PROD-xxxxx
MP_ACCESS_TOKEN=PROD-xxxxx

# Webhook URL (deve ser acessível publicamente)
MERCADOPAGO_WEBHOOK_URL=https://seu-dominio.com/api/mercadopago_webhook.php
```

**Configurar no Painel do Mercado Pago**:
1. Ir para: Configurações → Webhooks
2. URL: `https://seu-dominio.com/api/mercadopago_webhook.php`
3. Eventos: `payment.created`, `payment.updated`

---

## 3. FLUXO DE REGISTRO DE DOMÍNIO (Namecheap)

### 3.1 Fluxo Completo
```
1. dashboard/domain-selection.php
   - Busca domínios disponíveis via api/domain_search.php
   ↓
2. api/domain_search.php
   - Chama Namecheap API
   - Retorna lista de domínios com preços
   ↓
3. dashboard/payment.php
   - Inclui preço do domínio
   ↓
4. Após pagamento aprovado:
   api/mercadopago_webhook.php
   - Chama registerDomainAutomatically()
   - Registra domínio no Namecheap
   - Atualiza clientes (status='dominio_registrado')
   ↓
5. api/set_dns.php
   - Configura nameservers
   - Atualiza clientes (status='dns_configurado')
```

### 3.2 Configuração Necessária para Produção

**Arquivo**: `.env`
```
# Namecheap API
NAMECHEAP_API_USER=seu_usuario_namecheap
NAMECHEAP_API_KEY=sua_chave_api_namecheap
NAMESERVERS=ns1.namecheap.com,ns2.namecheap.com
```

**Obter Credenciais**:
1. Login em: https://www.namecheap.com/
2. Ir para: Account → API Access
3. Ativar API Access
4. Copiar API Key e Username

---

## 4. FLUXO DE EMAIL

### 4.1 Confirmação de Email
```
1. api/cadastro.php
   - Cria usuário com email_confirmed=FALSE
   - Gera token de confirmação
   - Envia email via send-confirmation-email.php
   ↓
2. api/send-confirmation-email.php
   - Usa PHPMailer com SMTP
   - Envia link: confirm-email.php?token=xxx
   ↓
3. api/confirm-email.php
   - Valida token
   - Atualiza email_confirmed=TRUE
   - Redireciona para login
```

### 4.2 Configuração Necessária para Produção

**Arquivo**: `.env`
```
# SMTP (Hostinger)
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=465
SMTP_USERNAME=seu_email@seu-dominio.com
SMTP_PASSWORD=sua_senha_smtp
SMTP_ENCRYPTION=ssl

# Email
EMAIL_FROM=noreply@seu-dominio.com
EMAIL_NAME="Sites Para Empresas"
```

---

## 5. BANCO DE DADOS

### 5.1 Tabelas Principais
- **usuarios**: Usuários do sistema
- **pagamentos**: Registros de pagamento (Mercado Pago)
- **assinaturas**: Assinaturas ativas
- **clientes**: Dados dos clientes (domínio, status)
- **sites**: Sites criados
- **planos**: Planos disponíveis
- **email_confirmacao**: Tokens de confirmação

### 5.2 Fluxo de Dados
```
usuarios (id, email, senha, email_confirmed)
    ↓
pagamentos (usuario_id, plano_id, dominio, mercadopago_payment_id)
    ↓
assinaturas (usuario_id, plano_id, status)
    ↓
clientes (user_id, dominio, status, namecheap_domain_id)
    ↓
sites (usuario_id, dominio, status)
```

---

## 6. CHECKLIST PARA MIGRAÇÃO PARA PRODUÇÃO

### 6.1 Variáveis de Ambiente (.env)
- [ ] `DB_HOST` = servidor de produção
- [ ] `DB_NAME` = banco de produção
- [ ] `DB_USER` = usuário de produção
- [ ] `DB_PASS` = senha de produção
- [ ] `BASE_URL` = https://seu-dominio.com
- [ ] `APP_ENV` = production
- [ ] `MP_PUBLIC_KEY` = chave de produção
- [ ] `MP_ACCESS_TOKEN` = token de produção
- [ ] `NAMECHEAP_API_USER` = seu usuário
- [ ] `NAMECHEAP_API_KEY` = sua chave
- [ ] `NAMESERVERS` = seus nameservers
- [ ] `SMTP_HOST` = seu servidor SMTP
- [ ] `SMTP_USERNAME` = seu email
- [ ] `SMTP_PASSWORD` = sua senha
- [ ] `EMAIL_FROM` = seu email
- [ ] `MERCADOPAGO_WEBHOOK_URL` = https://seu-dominio.com/api/mercadopago_webhook.php

### 6.2 Configurações no Mercado Pago
- [ ] Trocar para credenciais de produção
- [ ] Configurar webhook em: https://seu-dominio.com/api/mercadopago_webhook.php
- [ ] Testar webhook com evento de teste

### 6.3 Configurações no Namecheap
- [ ] Ativar API Access
- [ ] Copiar API Key e Username
- [ ] Adicionar ao .env

### 6.4 Servidor Web
- [ ] HTTPS ativado
- [ ] Certificado SSL válido
- [ ] Permissões de arquivo corretas
- [ ] PHP 7.4+ com extensões necessárias

### 6.5 Banco de Dados
- [ ] Executar schema.sql
- [ ] Criar usuário com permissões corretas
- [ ] Backup automático configurado

---

## 7. ARQUIVOS CRÍTICOS E SUAS CONEXÕES

### 7.1 Configuração
```
config/db.php
├── Carrega .env
├── Define constantes de BD
├── Define constantes de MP
├── Define BASE_URL
└── Conecta ao MySQL

config/config.php
├── Define PLANOS
├── Define STATUS_SITE
└── Define STATUS_PAGAMENTO
```

### 7.2 API
```
api/cadastro.php
├── Cria usuário
├── Gera token de email
└── Chama send-confirmation-email.php

api/send-confirmation-email.php
├── Usa PHPMailer
├── Envia email com token
└── Usa SMTP do .env

api/mercadopago_webhook.php
├── Recebe notificação do MP
├── Atualiza pagamentos
├── Chama registerDomainAutomatically()
└── Cria assinatura

api/domain_search.php
├── Chama Namecheap API
└── Retorna domínios disponíveis

api/namecheap_helper.php
├── Funções auxiliares do Namecheap
├── registerDomainAutomatically()
├── setDNS()
└── Usa credenciais do .env
```

### 7.3 Dashboard
```
dashboard/payment.php
├── Valida dados
├── Cria preferência MP
├── Insere em pagamentos
└── Redireciona para MP

dashboard/payment-success.php
├── Recebe retorno do MP
├── Atualiza pagamentos
├── Cria assinatura
└── Busca dados do usuário

dashboard/domain-selection.php
├── Chama api/domain_search.php
├── Exibe domínios
└── Envia para payment.php
```

---

## 8. VARIÁVEIS DE AMBIENTE COMPLETAS

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=sitesparaempresas
DB_USER=root
DB_PASS=

# Application Configuration
APP_NAME="Sites Para Empresas"
APP_ENV=development
BASE_URL=http://localhost/sitesparaeempresas

# Mercado Pago Configuration (Brasil)
MP_PUBLIC_KEY=TEST-4eb6f22c-f997-4e2e-a751-c3381bb29a3a
MP_ACCESS_TOKEN=TEST-2235218074018734-101521-e81d1e6f8f3e4c4e0f5e5e5e5e5e5e5e-191014229

# Email Configuration
EMAIL_FROM=noreply@sitesparaempresas.com
EMAIL_NAME="Sites Para Empresas"

# SMTP Configuration for Hostinger
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=465
SMTP_USERNAME=noreply@sitesparaempresas.com
SMTP_PASSWORD=Mf3007mf!
SMTP_ENCRYPTION=ssl

# Namecheap Configuration
NAMECHEAP_API_USER=seu_usuario
NAMECHEAP_API_KEY=sua_chave
NAMESERVERS=ns1.namecheap.com,ns2.namecheap.com

# Webhook Configuration
MERCADOPAGO_WEBHOOK_URL=https://webhook.site/7efc1be6-c650-46f6-9435-609862fb32a9

# Security
SESSION_LIFETIME=7200
```

---

## 9. TESTES RECOMENDADOS

### 9.1 Teste Local (Desenvolvimento)
```bash
# 1. Testar conexão BD
php test-db.php

# 2. Testar email
php test-email.php

# 3. Testar cadastro
php test-cadastro.php

# 4. Testar integração completa
php test-integration.php
```

### 9.2 Teste em Produção
1. Criar conta de teste
2. Confirmar email
3. Selecionar domínio
4. Realizar pagamento (usar cartão de teste do MP)
5. Verificar webhook recebido
6. Verificar domínio registrado no Namecheap
7. Verificar DNS configurado

---

## 10. TROUBLESHOOTING

### Problema: Webhook não recebe notificações
**Solução**:
1. Verificar URL no painel do MP
2. Verificar se servidor está acessível (HTTPS)
3. Verificar logs em `api/mercadopago_webhook.php`
4. Usar webhook.site para testar

### Problema: Email não é enviado
**Solução**:
1. Verificar credenciais SMTP no .env
2. Verificar se porta 465 está aberta
3. Verificar logs do PHP
4. Testar com `test-email.php`

### Problema: Domínio não é registrado
**Solução**:
1. Verificar credenciais Namecheap no .env
2. Verificar se API está ativada
3. Verificar logs em `api/namecheap_helper.php`
4. Testar com `test_namecheap.php`

---

## 11. RESUMO DE MUDANÇAS NECESSÁRIAS

Para migrar de desenvolvimento para produção, altere apenas no `.env`:

```env
# 1. Banco de Dados
DB_HOST=seu_host_produção
DB_NAME=seu_banco_produção
DB_USER=seu_usuario_produção
DB_PASS=sua_senha_produção

# 2. URL Base
BASE_URL=https://seu-dominio.com
APP_ENV=production

# 3. Mercado Pago (Produção)
MP_PUBLIC_KEY=PROD-xxxxx
MP_ACCESS_TOKEN=PROD-xxxxx

# 4. Namecheap
NAMECHEAP_API_USER=seu_usuario
NAMECHEAP_API_KEY=sua_chave

# 5. SMTP
SMTP_HOST=seu_smtp_produção
SMTP_USERNAME=seu_email_produção
SMTP_PASSWORD=sua_senha_produção
EMAIL_FROM=seu_email_produção

# 6. Webhook
MERCADOPAGO_WEBHOOK_URL=https://seu-dominio.com/api/mercadopago_webhook.php
```

**Nenhuma mudança de código é necessária!** Tudo funciona através de variáveis de ambiente.
