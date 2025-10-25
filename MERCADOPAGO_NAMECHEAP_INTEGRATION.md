# 🚀 Integração Completa: Mercado Pago + Namecheap + Checkout

## 📋 Fluxo Automático Completo

```
1. Cliente escolhe domínio (escolher-dominio.php)
   ↓
2. Confirma domínio (api/confirmar-dominio.php)
   ↓
3. Vai para pagamento (dashboard/payment.php)
   ↓
4. Paga via Mercado Pago
   ↓
5. Webhook recebe confirmação (api/mercadopago_webhook.php)
   ↓
6. Registra domínio automaticamente (Namecheap)
   ↓
7. Configura DNS automaticamente (Hostinger)
   ↓
8. Ativa site do cliente
```

## 🔧 Arquivos Criados/Modificados

### 1. **api/mercadopago_webhook.php** (NOVO)
Webhook que recebe notificações do Mercado Pago e:
- Valida o pagamento
- Registra domínio automaticamente via Namecheap
- Configura DNS para Hostinger
- Cria assinatura do cliente
- Registra logs de atividade

### 2. **api/namecheap_helper.php** (EXISTENTE)
Centraliza configuração da API Namecheap

### 3. **db/schema.sql** (MODIFICADO)
Adicionadas colunas:
- `clientes.namecheap_domain_id` - ID do domínio no Namecheap
- `clientes.namecheap_order_id` - ID do pedido no Namecheap
- `pagamentos.plano_id` - Referência ao plano
- `pagamentos.dominio` - Domínio do pedido
- `pagamentos.valor_plano` - Valor do plano
- `pagamentos.valor_dominio` - Valor do domínio

Novos status para `clientes.status`:
- `aguardando_pagamento` - Aguardando confirmação de pagamento
- `aguardando_dominio_registro` - Pagamento confirmado, registrando domínio
- `dominio_registrado` - Domínio registrado no Namecheap
- `dns_configurado` - DNS apontado para Hostinger

### 4. **migrate-namecheap.php** (NOVO)
Script para migrar banco de dados existente

## 🛠️ Instalação

### Passo 1: Executar Migração
```bash
php migrate-namecheap.php
```

### Passo 2: Configurar Variáveis de Ambiente (.env)
```env
MP_ACCESS_TOKEN=seu_token_mercado_pago
NAMECHEAP_API_USER=TiagoBaptista13
NAMECHEAP_API_KEY=d528fc44618a47e789db98b20c772872
NAMECHEAP_USERNAME=TiagoBaptista13
NAMECHEAP_CLIENT_IP=167.250.106.18
NAMESERVERS=dns1.hostinger.com,dns2.hostinger.com
```

### Passo 3: Configurar Webhook no Mercado Pago
1. Acesse: https://www.mercadopago.com.br/developers/panel
2. Vá em **Notificações**
3. Adicione URL: `https://seu-dominio.com/api/mercadopago_webhook.php`
4. Selecione eventos: `payment.created`, `payment.updated`

## 📊 Fluxo Detalhado

### 1️⃣ Escolha de Domínio (escolher-dominio.php)
```javascript
// Cliente digita domínio e clica "Verificar"
// Chama: api/verificar-dominio.php
// Retorna: disponibilidade e preço
```

### 2️⃣ Confirmação de Domínio (api/confirmar-dominio.php)
```php
// Atualiza cliente com domínio escolhido
// Status: aguardando_pagamento
// Retorna: dados para pagamento
```

### 3️⃣ Pagamento (dashboard/payment.php)
```php
// Cria preferência no Mercado Pago
// Inclui: plano + domínio
// Salva pedido no banco
// Redireciona para checkout Mercado Pago
```

### 4️⃣ Webhook Mercado Pago (api/mercadopago_webhook.php)
```php
// Recebe notificação de pagamento
// Se aprovado:
//   - Atualiza status do pagamento
//   - Chama registerDomainAutomatically()
//   - Chama configureDNSAutomatically()
//   - Cria assinatura
//   - Registra log
```

### 5️⃣ Registro de Domínio (automático)
```php
function registerDomainAutomatically() {
    // Busca dados do usuário
    // Monta contato (Registrant, Tech, Admin, AuxBilling)
    // Chama Namecheap API: namecheap.domains.create
    // Salva domain_id e order_id no banco
    // Status: dominio_registrado
}
```

### 6️⃣ Configuração de DNS (automático)
```php
function configureDNSAutomatically() {
    // Extrai SLD e TLD do domínio
    // Chama Namecheap API: namecheap.domains.dns.setCustom
    // Usa nameservers do Hostinger
    // Status: dns_configurado
}
```

## 📱 Fluxo do Cliente

```
1. Cliente faz login
   ↓
2. Escolhe plano
   ↓
3. Vai para escolher-dominio.php
   ↓
4. Digita domínio e verifica disponibilidade
   ↓
5. Clica "Continuar para Pagamento"
   ↓
6. Vai para dashboard/payment.php
   ↓
7. Clica "Pagar com Mercado Pago"
   ↓
8. Completa pagamento
   ↓
9. Retorna para payment-success.php
   ↓
10. Webhook processa automaticamente:
    - Registra domínio
    - Configura DNS
    - Ativa site
   ↓
11. Cliente recebe email de confirmação
```

## 🔐 Segurança

- ✅ Validação de usuário em cada etapa
- ✅ Verificação de propriedade do cliente
- ✅ Tokens de segurança do Mercado Pago
- ✅ Logs de todas as ações
- ✅ Tratamento de erros com try-catch
- ✅ Variáveis sensíveis em .env

## 🧪 Testes

### Teste 1: Verificar Domínio
```bash
curl "http://localhost/api/check_domain.php?domain=teste123.com"
```

### Teste 2: Registrar Domínio (Manual)
```bash
curl "http://localhost/api/register_domain.php?domain=teste123.com&firstName=Tiago&lastName=Baptista&email=test@example.com"
```

### Teste 3: Configurar DNS (Manual)
```bash
curl "http://localhost/api/set_dns.php?domain=teste123.com&nameservers=dns1.hostinger.com,dns2.hostinger.com"
```

### Teste 4: Webhook (Simulado)
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

## 📊 Status do Cliente

| Status | Significado |
|--------|-------------|
| `aguardando_email` | Aguardando confirmação de email |
| `aguardando_dominio` | Aguardando escolha de domínio |
| `aguardando_pagamento` | Domínio escolhido, aguardando pagamento |
| `aguardando_dominio_registro` | Pagamento confirmado, registrando domínio |
| `dominio_registrado` | Domínio registrado no Namecheap |
| `dns_configurado` | DNS apontado para Hostinger |
| `ativo` | Site ativo e funcionando |
| `inativo` | Site desativado |
| `cancelado` | Assinatura cancelada |

## 🚀 Próximos Passos

1. **Criar site automaticamente** após DNS configurado
2. **Enviar email de confirmação** com dados de acesso
3. **Criar subdomínio** para site em construção
4. **Configurar SSL** automaticamente
5. **Integrar com painel de controle** do Hostinger

## 📝 Variáveis de Ambiente Necessárias

```env
# Mercado Pago
MP_ACCESS_TOKEN=seu_token_aqui

# Namecheap
NAMECHEAP_API_USER=TiagoBaptista13
NAMECHEAP_API_KEY=sua_chave_aqui
NAMECHEAP_USERNAME=TiagoBaptista13
NAMECHEAP_CLIENT_IP=seu_ip_aqui

# Hostinger
NAMESERVERS=dns1.hostinger.com,dns2.hostinger.com

# Base URL
BASE_URL=https://seu-dominio.com
```

## 🐛 Troubleshooting

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

## 📞 Suporte

Para dúvidas ou problemas, consulte:
- Documentação Mercado Pago: https://www.mercadopago.com.br/developers
- Documentação Namecheap: https://www.namecheap.com/support/api/
- Logs: `error_log` do PHP
