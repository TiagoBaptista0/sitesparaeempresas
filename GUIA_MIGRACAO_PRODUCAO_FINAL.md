# 🚀 GUIA DE MIGRAÇÃO PARA PRODUÇÃO

## ✅ Pré-requisitos

- ✅ Domínio registrado
- ✅ Hosting com PHP 8.3+
- ✅ Banco de dados MySQL
- ✅ SSL/HTTPS configurado
- ✅ Credenciais Mercado Pago (produção)
- ✅ Credenciais Namecheap (produção)

---

## 📋 Checklist de Migração

### 1. Preparar Ambiente de Produção
- [ ] Criar banco de dados em produção
- [ ] Configurar usuário MySQL com permissões
- [ ] Fazer backup do banco de dados local
- [ ] Testar conexão com banco de produção

### 2. Atualizar Credenciais
- [ ] Obter credenciais Mercado Pago de produção
- [ ] Obter credenciais Namecheap de produção
- [ ] Obter credenciais SMTP de produção
- [ ] Atualizar arquivo .env

### 3. Configurar SSL/HTTPS
- [ ] Instalar certificado SSL
- [ ] Configurar redirecionamento HTTP → HTTPS
- [ ] Testar HTTPS em todos os endpoints

### 4. Configurar Webhook
- [ ] Atualizar URL do webhook no Mercado Pago
- [ ] Testar webhook em produção
- [ ] Configurar logs de webhook

### 5. Testar Sistema Completo
- [ ] Testar fluxo de pagamento
- [ ] Testar envio de emails
- [ ] Testar registro de domínio
- [ ] Testar dashboard do usuário

### 6. Fazer Deploy
- [ ] Fazer upload dos arquivos
- [ ] Executar migrations do banco de dados
- [ ] Limpar cache
- [ ] Testar em produção

---

## 🔧 Atualizar .env para Produção

### Antes (Desenvolvimento)
```
APP_ENV=development
BASE_URL=http://localhost/sitesparaeempresas
MP_PUBLIC_KEY=TEST-4eb6f22c-f997-4e2e-a751-c3381bb29a3a
MP_ACCESS_TOKEN=TEST-2235218074018734-101521-e81d1e6f8f3e4c4e0f5e5e5e5e5e5e5e-191014229
MERCADOPAGO_WEBHOOK_URL=https://julian-interprotoplasmic-lanette.ngrok-free.dev/sitesparaeempresas/api/mercadopago_webhook.php
```

### Depois (Produção)
```
APP_ENV=production
BASE_URL=https://sitesparaempresas.com
MP_PUBLIC_KEY=PROD-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
MP_ACCESS_TOKEN=PROD-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
MERCADOPAGO_WEBHOOK_URL=https://sitesparaempresas.com/api/mercadopago_webhook.php
```

---

## 📝 Passos de Migração

### 1. Preparar Banco de Dados
```bash
# Fazer backup local
mysqldump -u root sitesparaempresas > backup.sql

# Criar banco em produção
mysql -h prod-host -u prod-user -p prod_db < backup.sql
```

### 2. Atualizar Arquivo .env
```bash
# Editar .env com credenciais de produção
nano .env
```

### 3. Fazer Upload dos Arquivos
```bash
# Via FTP/SFTP
ftp prod-host
put -r * /public_html/sitesparaempresas/

# Ou via Git
git push production main
```

### 4. Configurar Permissões
```bash
# Permissões de diretórios
chmod 755 /public_html/sitesparaempresas/
chmod 755 /public_html/sitesparaempresas/api/
chmod 755 /public_html/sitesparaempresas/dashboard/

# Permissões de arquivos
chmod 644 /public_html/sitesparaempresas/.env
chmod 644 /public_html/sitesparaempresas/config/db.php
```

### 5. Testar Conexão com Banco
```bash
# Acessar via SSH
ssh user@prod-host

# Testar conexão
php -r "require 'config/db.php'; echo 'Conectado!'"
```

### 6. Configurar Webhook no Mercado Pago
```
1. Acessar: https://www.mercadopago.com.br/developers/panel
2. Ir para: Webhooks
3. Adicionar URL: https://sitesparaempresas.com/api/mercadopago_webhook.php
4. Selecionar eventos: payment.created, payment.updated
5. Salvar
```

### 7. Testar Sistema Completo
```bash
# Testar banco de dados
php test-payment-complete.php

# Testar validação de domínio
php api/test-check-domain.php

# Testar webhook
curl -X POST https://sitesparaempresas.com/api/mercadopago_webhook.php \
  -H "Content-Type: application/json" \
  -d '{"type":"payment","data":{"id":123}}'
```

---

## 🔒 Segurança em Produção

### 1. Proteger Arquivo .env
```bash
# Não fazer upload do .env para Git
echo ".env" >> .gitignore

# Criar .env.example
cp .env .env.example
# Remover valores sensíveis do .env.example
```

### 2. Configurar HTTPS
```bash
# Redirecionar HTTP para HTTPS
# Adicionar em .htaccess
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 3. Configurar Headers de Segurança
```php
// Adicionar em config/db.php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
```

### 4. Limpar Arquivos de Teste
```bash
# Remover arquivos de teste
rm test-*.php
rm api/test-*.php
```

---

## 📊 Monitoramento em Produção

### 1. Configurar Logs
```php
// Adicionar em config/db.php
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php-errors.log');
```

### 2. Monitorar Webhook
```bash
# Ver logs do webhook
tail -f /var/log/php-errors.log | grep webhook
```

### 3. Monitorar Banco de Dados
```sql
-- Verificar pagamentos
SELECT * FROM pagamentos ORDER BY data_criacao DESC LIMIT 10;

-- Verificar assinaturas
SELECT * FROM assinaturas ORDER BY data_inicio DESC LIMIT 10;

-- Verificar erros
SELECT * FROM logs WHERE tipo = 'erro' ORDER BY data DESC LIMIT 10;
```

---

## 🚨 Troubleshooting em Produção

### Problema: Webhook não recebe notificações
**Solução:**
1. Verificar URL do webhook no Mercado Pago
2. Verificar se HTTPS está funcionando
3. Verificar logs do servidor
4. Testar webhook manualmente

### Problema: Email não é enviado
**Solução:**
1. Verificar credenciais SMTP
2. Verificar se porta SMTP está aberta
3. Verificar logs de erro
4. Testar SMTP manualmente

### Problema: Banco de dados não conecta
**Solução:**
1. Verificar credenciais no .env
2. Verificar se host do banco está correto
3. Verificar se usuário tem permissões
4. Testar conexão manualmente

---

## ✅ Checklist Final

- [ ] Banco de dados em produção
- [ ] Credenciais atualizadas
- [ ] SSL/HTTPS configurado
- [ ] Webhook configurado
- [ ] Arquivos de teste removidos
- [ ] Logs configurados
- [ ] Segurança verificada
- [ ] Sistema testado completo
- [ ] Backup realizado
- [ ] Pronto para produção

---

## 🎉 Resultado

✅ **Sistema migrado para produção**
✅ **Pronto para aceitar pagamentos reais**
✅ **Seguro e monitorado**
✅ **Pronto para crescer**

**Parabéns! Seu sistema está em produção!** 🚀
