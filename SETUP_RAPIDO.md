# ⚡ Guia Rápido de Setup - Integração Completa

## 🚀 5 Passos para Ativar

### 1️⃣ Executar Migração do Banco
```bash
php migrate-namecheap.php
```

### 2️⃣ Configurar .env
```env
# Mercado Pago
MP_ACCESS_TOKEN=seu_token_aqui

# Namecheap (já configurado)
NAMECHEAP_API_USER=TiagoBaptista13
NAMECHEAP_API_KEY=d528fc44618a47e789db98b20c772872
NAMECHEAP_USERNAME=TiagoBaptista13
NAMECHEAP_CLIENT_IP=167.250.106.18

# Hostinger Nameservers
NAMESERVERS=dns1.hostinger.com,dns2.hostinger.com

# Base URL
BASE_URL=https://seu-dominio.com
```

### 3️⃣ Configurar Webhook Mercado Pago
1. Acesse: https://www.mercadopago.com.br/developers/panel
2. Vá em **Notificações** → **Webhooks**
3. Clique em **Adicionar novo webhook**
4. URL: `https://seu-dominio.com/api/mercadopago_webhook.php`
5. Selecione eventos:
   - `payment.created`
   - `payment.updated`
6. Clique em **Salvar**

### 4️⃣ Testar Integração
```bash
php test-integration.php
```

### 5️⃣ Testar Fluxo Completo
1. Acesse: `http://localhost/escolher-dominio.php`
2. Escolha um domínio
3. Clique em "Continuar para Pagamento"
4. Complete o pagamento no Mercado Pago
5. Verifique se o domínio foi registrado automaticamente

## 📊 Fluxo Automático

```
Cliente escolhe domínio
    ↓
Confirma domínio (api/confirmar-dominio.php)
    ↓
Vai para pagamento (dashboard/payment.php)
    ↓
Paga via Mercado Pago
    ↓
Webhook recebe confirmação (api/mercadopago_webhook.php)
    ↓
Registra domínio automaticamente (Namecheap)
    ↓
Configura DNS automaticamente (Hostinger)
    ↓
Ativa site do cliente
```

## 🔍 Verificar Status

### Ver Pagamentos
```sql
SELECT id, usuario_id, dominio, valor, status, data_criacao 
FROM pagamentos 
ORDER BY id DESC LIMIT 10;
```

### Ver Clientes
```sql
SELECT id, user_id, dominio, status, namecheap_domain_id, namecheap_order_id 
FROM clientes 
ORDER BY id DESC LIMIT 10;
```

### Ver Logs
```sql
SELECT usuario_id, acao, descricao, data_criacao 
FROM logs 
WHERE acao LIKE '%dominio%' OR acao LIKE '%pagamento%'
ORDER BY id DESC LIMIT 20;
```

## 🧪 Testes Manuais

### Teste 1: Verificar Domínio
```bash
curl "http://localhost/api/check_domain.php?domain=teste123.com"
```

### Teste 2: Registrar Domínio
```bash
curl "http://localhost/api/register_domain.php?domain=teste123.com&firstName=Tiago&lastName=Baptista&email=test@example.com"
```

### Teste 3: Configurar DNS
```bash
curl "http://localhost/api/set_dns.php?domain=teste123.com&nameservers=dns1.hostinger.com,dns2.hostinger.com"
```

### Teste 4: Simular Webhook
```bash
curl -X POST http://localhost/api/mercadopago_webhook.php \
  -H "Content-Type: application/json" \
  -d '{
    "type": "payment",
    "data": {
      "id": "123456789"
    }
  }'
```

## 📁 Arquivos Criados/Modificados

| Arquivo | Tipo | Descrição |
|---------|------|-----------|
| `api/mercadopago_webhook.php` | ✨ NOVO | Webhook Mercado Pago + Namecheap |
| `api/namecheap_helper.php` | ✨ NOVO | Configuração Namecheap |
| `api/check_domain.php` | ✨ NOVO | Verificar disponibilidade |
| `api/register_domain.php` | ✨ NOVO | Registrar domínio |
| `api/set_dns.php` | ✨ NOVO | Configurar DNS |
| `db/schema.sql` | 📝 MODIFICADO | Adicionadas colunas Namecheap |
| `dashboard/payment.php` | 📝 MODIFICADO | Integração com novo sistema |
| `migrate-namecheap.php` | ✨ NOVO | Script de migração |
| `test-integration.php` | ✨ NOVO | Teste de integração |

## ⚠️ Importante

- **Sandbox vs Produção**: Em `api/namecheap_helper.php`, mude a URL para produção quando pronto
- **Nameservers**: Use `dns1.hostinger.com,dns2.hostinger.com` em produção
- **Variáveis de Ambiente**: Nunca commite credenciais no Git
- **Logs**: Verifique `error_log` do PHP para troubleshooting

## 🆘 Troubleshooting

### Webhook não recebe notificações
- Verificar URL no painel Mercado Pago
- Verificar logs em `error_log`
- Testar com curl

### Domínio não registra
- Verificar credenciais Namecheap
- Verificar dados do usuário no banco
- Verificar logs de atividade

### DNS não configura
- Verificar nameservers do Hostinger
- Verificar formato do domínio
- Verificar logs de atividade

## 📞 Documentação Completa

Veja `MERCADOPAGO_NAMECHEAP_INTEGRATION.md` para documentação detalhada.
