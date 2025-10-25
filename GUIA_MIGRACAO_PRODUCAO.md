# 🚀 GUIA DE MIGRAÇÃO PARA PRODUÇÃO

## 1. PRÉ-REQUISITOS

### 1.1 Hospedagem
- [ ] Domínio registrado
- [ ] Hospedagem com PHP 7.4+
- [ ] MySQL 5.7+
- [ ] HTTPS/SSL ativado
- [ ] Acesso SSH (recomendado)

### 1.2 Serviços Externos
- [ ] Conta Mercado Pago (produção)
- [ ] Conta Namecheap (produção)
- [ ] Conta Hostinger (ou outro SMTP)
- [ ] Webhook.site (para testes)

### 1.3 Conhecimentos
- [ ] Básico de PHP
- [ ] Básico de MySQL
- [ ] Básico de Git
- [ ] Básico de variáveis de ambiente

---

## 2. PASSO A PASSO DE MIGRAÇÃO

### 2.1 Preparar Ambiente Local

```bash
# 1. Clonar repositório
git clone seu-repositorio.git
cd seu-repositorio

# 2. Instalar dependências
composer install

# 3. Criar .env local
cp .env.example .env

# 4. Configurar .env para desenvolvimento
# Editar .env com credenciais de teste

# 5. Criar banco de dados
mysql -u root < db/schema.sql

# 6. Testar localmente
php -S localhost:8000

# 7. Acessar http://localhost:8000
```

### 2.2 Testar Fluxo Completo Localmente

```bash
# 1. Testar conexão com banco
php test-db.php

# 2. Testar email
php test-email.php

# 3. Testar cadastro
php test-cadastro.php

# 4. Testar integração
php test-integration.php

# 5. Testar Namecheap (sandbox)
php test_namecheap.php
```

### 2.3 Preparar Credenciais de Produção

#### Mercado Pago
1. Acessar: https://www.mercadopago.com.br/
2. Login com sua conta
3. Ir para: Configurações → Credenciais
4. Copiar:
   - `MP_PUBLIC_KEY` (Produção)
   - `MP_ACCESS_TOKEN` (Produção)

#### Namecheap
1. Acessar: https://www.namecheap.com/
2. Login com sua conta
3. Ir para: Account → API Access
4. Ativar API Access
5. Copiar:
   - `NAMECHEAP_API_USER` (seu username)
   - `NAMECHEAP_API_KEY` (sua chave)

#### Hostinger (SMTP)
1. Acessar: https://www.hostinger.com/
2. Login com sua conta
3. Ir para: Email → Configurações
4. Copiar:
   - `SMTP_HOST` (smtp.hostinger.com)
   - `SMTP_PORT` (465)
   - `SMTP_USERNAME` (seu email)
   - `SMTP_PASSWORD` (sua senha)

#### Banco de Dados
1. Criar banco de dados em produção
2. Criar usuário com permissões limitadas
3. Copiar:
   - `DB_HOST` (seu host)
   - `DB_NAME` (seu banco)
   - `DB_USER` (seu usuário)
   - `DB_PASS` (sua senha)

### 2.4 Preparar Servidor

```bash
# 1. Conectar ao servidor via SSH
ssh usuario@seu-dominio.com

# 2. Criar diretório da aplicação
mkdir -p /home/usuario/public_html/seu-dominio.com

# 3. Clonar repositório
cd /home/usuario/public_html/seu-dominio.com
git clone seu-repositorio.git .

# 4. Instalar dependências
composer install --no-dev

# 5. Criar arquivo .env
nano .env

# 6. Copiar conteúdo do .env.example e preencher com credenciais de produção
# (Ver seção 2.3)

# 7. Definir permissões
chmod 755 .
chmod 644 .env
chmod 755 config/
chmod 755 api/
chmod 755 dashboard/
chmod 755 admin/

# 8. Criar banco de dados
mysql -u seu_usuario -p seu_banco < db/schema.sql

# 9. Testar acesso
curl https://seu-dominio.com/
```

### 2.5 Configurar Webhook do Mercado Pago

1. Acessar: https://www.mercadopago.com.br/
2. Ir para: Configurações → Webhooks
3. Adicionar webhook:
   - URL: `https://seu-dominio.com/api/mercadopago_webhook.php`
   - Eventos: `payment.created`, `payment.updated`
4. Testar webhook com evento de teste

### 2.6 Configurar Webhook do Namecheap

1. Acessar: https://www.namecheap.com/
2. Ir para: Account → Webhooks
3. Adicionar webhook (se necessário):
   - URL: `https://seu-dominio.com/api/namecheap_webhook.php`
   - Eventos: `domain.register`, `domain.renew`

### 2.7 Testar em Produção

```bash
# 1. Testar acesso ao site
curl https://seu-dominio.com/

# 2. Testar cadastro
# Acessar https://seu-dominio.com/cadastro.php
# Preencher formulário
# Verificar email de confirmação

# 3. Testar login
# Confirmar email
# Fazer login

# 4. Testar seleção de domínio
# Ir para dashboard
# Selecionar domínio

# 5. Testar pagamento
# Usar cartão de teste do Mercado Pago
# Verificar se pagamento foi processado

# 6. Testar webhook
# Verificar se domínio foi registrado
# Verificar se DNS foi configurado

# 7. Monitorar logs
tail -f /var/log/php-fpm/seu-dominio.com.log
tail -f /var/log/mysql/error.log
```

---

## 3. ARQUIVO .ENV PARA PRODUÇÃO

```env
# Database Configuration
DB_HOST=seu_host_mysql
DB_NAME=seu_banco_dados
DB_USER=seu_usuario_mysql
DB_PASS=sua_senha_mysql

# Application Configuration
APP_NAME="Sites Para Empresas"
APP_ENV=production
BASE_URL=https://seu-dominio.com

# Mercado Pago Configuration (Produção)
MP_PUBLIC_KEY=PROD-xxxxxxxxxxxxxxxxxxxxx
MP_ACCESS_TOKEN=PROD-xxxxxxxxxxxxxxxxxxxxx

# Email Configuration
EMAIL_FROM=noreply@seu-dominio.com
EMAIL_NAME="Sites Para Empresas"

# SMTP Configuration for Hostinger
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=465
SMTP_USERNAME=seu_email@seu-dominio.com
SMTP_PASSWORD=sua_senha_smtp
SMTP_ENCRYPTION=ssl

# Namecheap Configuration
NAMECHEAP_API_USER=seu_username_namecheap
NAMECHEAP_API_KEY=sua_chave_api_namecheap
NAMESERVERS=ns1.namecheap.com,ns2.namecheap.com

# Webhook Configuration
MERCADOPAGO_WEBHOOK_URL=https://seu-dominio.com/api/mercadopago_webhook.php

# Security
SESSION_LIFETIME=7200
```

---

## 4. CHECKLIST FINAL

### 4.1 Antes de Ativar
- [ ] Banco de dados criado e testado
- [ ] .env configurado com credenciais de produção
- [ ] HTTPS/SSL ativado
- [ ] Webhook do Mercado Pago configurado
- [ ] Email testado e funcionando
- [ ] Domínio apontando para servidor
- [ ] Permissões de arquivo corretas
- [ ] Backup do banco de dados configurado

### 4.2 Após Ativar
- [ ] Testar cadastro completo
- [ ] Testar pagamento com cartão de teste
- [ ] Testar webhook
- [ ] Testar registro de domínio
- [ ] Testar configuração de DNS
- [ ] Monitorar logs por 24 horas
- [ ] Testar com usuário real
- [ ] Verificar email de confirmação

### 4.3 Manutenção
- [ ] Backup diário do banco de dados
- [ ] Monitorar logs de erro
- [ ] Monitorar taxa de sucesso de pagamento
- [ ] Monitorar taxa de entrega de email
- [ ] Atualizar dependências mensalmente
- [ ] Revisar segurança trimestralmente

---

## 5. TROUBLESHOOTING

### Problema: Webhook não recebe notificações
**Solução**:
1. Verificar URL no painel do Mercado Pago
2. Verificar se servidor está acessível (HTTPS)
3. Verificar logs em `api/mercadopago_webhook.php`
4. Usar webhook.site para testar
5. Verificar firewall/WAF

### Problema: Email não é enviado
**Solução**:
1. Verificar credenciais SMTP no .env
2. Verificar se porta 465 está aberta
3. Verificar logs do PHP
4. Testar com `test-email.php`
5. Verificar se email está na lista de spam

### Problema: Domínio não é registrado
**Solução**:
1. Verificar credenciais Namecheap no .env
2. Verificar se API está ativada
3. Verificar logs em `api/namecheap_helper.php`
4. Testar com `test_namecheap.php`
5. Verificar se domínio está disponível

### Problema: Erro de conexão com banco
**Solução**:
1. Verificar credenciais no .env
2. Verificar se banco está online
3. Verificar se usuário tem permissões
4. Verificar se host está correto
5. Verificar firewall

### Problema: Erro 500 no servidor
**Solução**:
1. Verificar logs do PHP
2. Verificar logs do Apache/Nginx
3. Verificar permissões de arquivo
4. Verificar se extensões PHP estão ativadas
5. Verificar se composer.json está correto

---

## 6. ROLLBACK

Se algo der errado:

```bash
# 1. Restaurar banco de dados
mysql -u seu_usuario -p seu_banco < backup.sql

# 2. Restaurar código anterior
git revert seu-commit-hash

# 3. Restaurar .env anterior
cp .env.backup .env

# 4. Reiniciar serviços
sudo systemctl restart php-fpm
sudo systemctl restart mysql
sudo systemctl restart nginx
```

---

## 7. MONITORAMENTO

### 7.1 Logs Importantes
```bash
# PHP
/var/log/php-fpm/seu-dominio.com.log

# MySQL
/var/log/mysql/error.log

# Nginx/Apache
/var/log/nginx/access.log
/var/log/apache2/access.log

# Aplicação
/home/usuario/public_html/seu-dominio.com/logs/
```

### 7.2 Métricas
- Taxa de sucesso de pagamento
- Tempo de resposta de API
- Taxa de erro de webhook
- Taxa de entrega de email
- Número de usuários ativos
- Número de domínios registrados

### 7.3 Alertas
- Pagamentos falhando
- Emails não sendo enviados
- Domínios não sendo registrados
- Erros de conexão com banco
- Erros de API

---

## 8. SUPORTE

Para dúvidas ou problemas:

1. Consultar documentação:
   - ANALISE_CONEXOES.md
   - DIAGRAMA_FLUXO.md
   - VERIFICACAO_SEGURANCA.md

2. Consultar documentação externa:
   - Mercado Pago: https://www.mercadopago.com.br/developers
   - Namecheap: https://www.namecheap.com/support/
   - Hostinger: https://www.hostinger.com/support

3. Verificar logs:
   - Logs do PHP
   - Logs do MySQL
   - Logs do servidor web

4. Testar com webhook.site:
   - https://webhook.site/

---

## 9. RESUMO

Para migrar de desenvolvimento para produção:

1. **Preparar credenciais** (Mercado Pago, Namecheap, SMTP, BD)
2. **Criar .env** com credenciais de produção
3. **Fazer upload** do código para servidor
4. **Criar banco de dados** em produção
5. **Configurar webhook** do Mercado Pago
6. **Testar fluxo completo** (cadastro → pagamento → domínio)
7. **Monitorar logs** por 24 horas
8. **Ativar para usuários reais**

**Nenhuma mudança de código é necessária!** Tudo funciona através de variáveis de ambiente.
