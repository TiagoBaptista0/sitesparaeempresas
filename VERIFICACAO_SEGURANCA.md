# 🔒 Verificação de Segurança e Boas Práticas

## 1. PROBLEMAS ENCONTRADOS E CORRIGIDOS

### 1.1 ✅ CORRIGIDO: Credenciais Hardcoded no Namecheap Helper
**Arquivo**: `api/namecheap_helper.php`
**Problema**: Credenciais do Namecheap estavam hardcoded
```php
// ❌ ANTES (INSEGURO)
define('NAMECHEAP_API_USER', 'TiagoBaptista13');
define('NAMECHEAP_API_KEY', 'd528fc44618a47e789db98b20c772872');
```

**Solução**: Usar variáveis de ambiente
```php
// ✅ DEPOIS (SEGURO)
define('NAMECHEAP_API_USER', $_ENV['NAMECHEAP_API_USER'] ?? '');
define('NAMECHEAP_API_KEY', $_ENV['NAMECHEAP_API_KEY'] ?? '');
```

### 1.2 ✅ CORRIGIDO: Ambiente Hardcoded
**Problema**: API URL estava sempre em sandbox
```php
// ❌ ANTES
define('NAMECHEAP_API_URL', 'https://api.sandbox.namecheap.com/xml.response');
```

**Solução**: Usar variável de ambiente para alternar entre sandbox e produção
```php
// ✅ DEPOIS
define('NAMECHEAP_API_URL', $app_env === 'production' 
    ? 'https://api.namecheap.com/xml.response'
    : 'https://api.sandbox.namecheap.com/xml.response'
);
```

---

## 2. VERIFICAÇÃO DE SEGURANÇA

### 2.1 Variáveis de Ambiente
- ✅ `.env` não está no Git (verificar `.gitignore`)
- ✅ Credenciais carregadas via `config/db.php`
- ✅ Constantes definidas a partir de `$_ENV`
- ⚠️ **IMPORTANTE**: Nunca commitar `.env` com credenciais reais

### 2.2 Autenticação
- ✅ Sessão iniciada em `config/db.php`
- ✅ Verificação de login em `config/functions.php`
- ✅ Token CSRF implementado
- ✅ Logout seguro com destruição de sessão

### 2.3 Banco de Dados
- ✅ Prepared statements usados em `api/mercadopago_webhook.php`
- ✅ Prepared statements usados em `dashboard/payment-success.php`
- ⚠️ Verificar se todos os arquivos usam prepared statements

### 2.4 Email
- ✅ PHPMailer com SMTP seguro (SSL/TLS)
- ✅ Credenciais via variáveis de ambiente
- ✅ Validação de email antes de enviar

### 2.5 Pagamentos
- ✅ Tokens do Mercado Pago via variáveis de ambiente
- ✅ Webhook valida payment_id
- ✅ Valores validados antes de processar

---

## 3. CHECKLIST DE SEGURANÇA PARA PRODUÇÃO

### 3.1 Arquivo `.env`
- [ ] Criar `.env` com credenciais de produção
- [ ] Verificar que `.env` está em `.gitignore`
- [ ] Nunca commitar `.env` com credenciais reais
- [ ] Usar `.env.example` como template

### 3.2 Banco de Dados
- [ ] Usar usuário com permissões limitadas
- [ ] Ativar SSL para conexão remota
- [ ] Fazer backup automático
- [ ] Monitorar logs de erro

### 3.3 HTTPS
- [ ] Certificado SSL válido
- [ ] Redirecionar HTTP → HTTPS
- [ ] Configurar HSTS headers

### 3.4 Mercado Pago
- [ ] Usar credenciais de produção
- [ ] Configurar webhook com URL HTTPS
- [ ] Testar webhook antes de ativar
- [ ] Monitorar logs de webhook

### 3.5 Namecheap
- [ ] Ativar API Access
- [ ] Usar IP whitelist se possível
- [ ] Testar em sandbox primeiro
- [ ] Monitorar logs de API

### 3.6 Email
- [ ] Usar SMTP seguro (SSL/TLS)
- [ ] Testar envio de email
- [ ] Monitorar taxa de rejeição
- [ ] Configurar SPF/DKIM/DMARC

### 3.7 Aplicação
- [ ] Desativar debug mode (`APP_ENV=production`)
- [ ] Configurar error logging
- [ ] Remover arquivos de teste
- [ ] Verificar permissões de arquivo

---

## 4. ARQUIVOS DE TESTE A REMOVER EM PRODUÇÃO

```
test-admin.php
test-cadastro.php
test-db.php
test-email-complete.php
test-email-confirmation.php
test-email.php
test-integration.php
test-registration.html
test-registration.php
test_namecheap.php
create-admin.php
migrate-namecheap.php
```

---

## 5. VARIÁVEIS DE AMBIENTE NECESSÁRIAS

### 5.1 Desenvolvimento
```env
APP_ENV=development
DB_HOST=localhost
DB_NAME=sitesparaempresas
DB_USER=root
DB_PASS=
BASE_URL=http://localhost/sitesparaeempresas
MP_PUBLIC_KEY=TEST-xxxxx
MP_ACCESS_TOKEN=TEST-xxxxx
NAMECHEAP_API_USER=seu_usuario_sandbox
NAMECHEAP_API_KEY=sua_chave_sandbox
SMTP_HOST=localhost
SMTP_PORT=1025
```

### 5.2 Produção
```env
APP_ENV=production
DB_HOST=seu_host_produção
DB_NAME=seu_banco_produção
DB_USER=seu_usuario_produção
DB_PASS=sua_senha_produção
BASE_URL=https://seu-dominio.com
MP_PUBLIC_KEY=PROD-xxxxx
MP_ACCESS_TOKEN=PROD-xxxxx
NAMECHEAP_API_USER=seu_usuario_produção
NAMECHEAP_API_KEY=sua_chave_produção
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=465
SMTP_USERNAME=seu_email@seu-dominio.com
SMTP_PASSWORD=sua_senha_smtp
SMTP_ENCRYPTION=ssl
EMAIL_FROM=noreply@seu-dominio.com
NAMESERVERS=ns1.namecheap.com,ns2.namecheap.com
MERCADOPAGO_WEBHOOK_URL=https://seu-dominio.com/api/mercadopago_webhook.php
```

---

## 6. FLUXO DE MIGRAÇÃO SEGURA

### 6.1 Pré-Produção
1. Testar em ambiente de staging
2. Usar credenciais de teste do Mercado Pago
3. Usar sandbox do Namecheap
4. Testar webhook com webhook.site

### 6.2 Produção
1. Criar `.env` com credenciais reais
2. Executar schema.sql no banco de produção
3. Configurar webhook no Mercado Pago
4. Testar fluxo completo com pagamento real
5. Monitorar logs por 24 horas

### 6.3 Rollback
1. Manter backup do banco de dados
2. Manter versão anterior do código
3. Ter plano de rollback documentado

---

## 7. MONITORAMENTO EM PRODUÇÃO

### 7.1 Logs
- Verificar logs de erro do PHP
- Verificar logs de webhook
- Verificar logs de banco de dados
- Verificar logs de email

### 7.2 Alertas
- Pagamentos falhando
- Emails não sendo enviados
- Domínios não sendo registrados
- Erros de conexão com banco

### 7.3 Métricas
- Taxa de sucesso de pagamento
- Tempo de resposta de API
- Taxa de erro de webhook
- Taxa de entrega de email

---

## 8. RESUMO DE MUDANÇAS NECESSÁRIAS

### Arquivo: `api/namecheap_helper.php`
✅ **CORRIGIDO**: Credenciais agora vêm de `.env`
✅ **CORRIGIDO**: API URL alterna entre sandbox e produção

### Arquivo: `.env`
✅ **ADICIONADO**: Variáveis do Namecheap
✅ **ADICIONADO**: URL do webhook

### Próximos Passos
1. Preencher `.env` com credenciais de produção
2. Remover arquivos de teste
3. Configurar webhook no Mercado Pago
4. Testar fluxo completo
5. Monitorar logs

---

## 9. TESTE DE SEGURANÇA RÁPIDO

```bash
# 1. Verificar se .env está em .gitignore
grep ".env" .gitignore

# 2. Verificar se há credenciais hardcoded
grep -r "NAMECHEAP_API_KEY" --include="*.php" | grep -v ".env"
grep -r "MP_ACCESS_TOKEN" --include="*.php" | grep -v ".env"

# 3. Verificar se há senhas em comentários
grep -r "password" --include="*.php" | grep -i "test\|exemplo\|123"

# 4. Verificar permissões de arquivo
ls -la .env
ls -la config/
```

---

## 10. CONTATO E SUPORTE

Para dúvidas sobre segurança ou configuração:
1. Consultar documentação do Mercado Pago
2. Consultar documentação do Namecheap
3. Consultar documentação do Hostinger (SMTP)
4. Verificar logs de erro
