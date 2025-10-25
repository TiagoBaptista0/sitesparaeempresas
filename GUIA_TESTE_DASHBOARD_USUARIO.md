# 🎯 GUIA DE TESTE - DASHBOARD DO USUÁRIO

## ✅ Pré-requisitos

- ✅ Banco de dados configurado
- ✅ Webhook Mercado Pago funcionando
- ✅ Emails configurados
- ✅ ngrok rodando

---

## 🚀 Teste Completo do Dashboard

### 1. Acessar Dashboard
```
URL: https://julian-interprotoplasmic-lanette.ngrok-free.dev/sitesparaeempresas/dashboard/index.php
```

**Esperado:**
- ✓ Página carrega sem erros
- ✓ Menu lateral visível
- ✓ Informações do usuário exibidas
- ✓ Planos disponíveis

---

### 2. Ir para "Meu Site"
```
Menu → Meu Site
```

**Esperado:**
- ✓ Página carrega
- ✓ Planos exibidos com preços
- ✓ Campo para digitar domínio
- ✓ Botão "Buscar Disponibilidade"

---

### 3. Buscar Disponibilidade de Domínio
```
1. Digitar: "meusite123456"
2. Selecionar: ".com"
3. Clicar: "Buscar Disponibilidade"
```

**Esperado:**
- ✓ Requisição enviada para API
- ✓ Resposta recebida em tempo real
- ✓ Status: "Disponível" ou "Indisponível"
- ✓ Preço do domínio exibido

---

### 4. Selecionar Plano
```
1. Clicar em um plano (ex: "Plano Básico")
2. Plano fica destacado
```

**Esperado:**
- ✓ Plano selecionado visualmente
- ✓ Preço do plano exibido
- ✓ Botão "Continuar" habilitado

---

### 5. Continuar para Pagamento
```
1. Clicar: "Continuar"
```

**Esperado:**
- ✓ Redirecionado para página de pagamento
- ✓ Resumo do pedido exibido
- ✓ Plano + Domínio + Preço total
- ✓ Botão "Pagar com Mercado Pago"

---

### 6. Realizar Pagamento
```
1. Clicar: "Pagar com Mercado Pago"
2. Será aberto checkout do Mercado Pago
```

**Esperado:**
- ✓ Redirecionado para Mercado Pago
- ✓ Formulário de pagamento exibido
- ✓ Itens do pedido listados

---

### 7. Usar Cartão de Teste
```
Cartão: 4111 1111 1111 1111
Expiração: 11/25
CVV: 123
Nome: Teste
```

**Esperado:**
- ✓ Pagamento processado
- ✓ Redirecionado para página de sucesso

---

### 8. Verificar Sucesso
```
URL: https://julian-interprotoplasmic-lanette.ngrok-free.dev/sitesparaeempresas/dashboard/payment-success.php
```

**Esperado:**
- ✓ Mensagem de sucesso exibida
- ✓ Número do pedido exibido
- ✓ Opção para voltar ao dashboard

---

### 9. Verificar Banco de Dados
```bash
php test-payment-complete.php
```

**Esperado:**
```
✓ Usuários: 5
✓ Pagamentos: 1 (aumentou)
✓ Assinaturas: 1 (aumentou)
```

---

### 10. Verificar Email
```
Caixa de entrada: test@example.com
```

**Esperado:**
- ✓ Email de confirmação recebido
- ✓ Contém informações do pedido
- ✓ Contém informações do domínio

---

## 🔍 Checklist de Teste

### Dashboard
- [ ] Página carrega sem erros
- [ ] Menu funciona
- [ ] Informações do usuário exibidas
- [ ] Planos exibidos corretamente

### Validação de Domínio
- [ ] API responde corretamente
- [ ] Domínios disponíveis retornam "Disponível"
- [ ] Domínios indisponíveis retornam "Indisponível"
- [ ] Preço do domínio exibido

### Seleção de Plano
- [ ] Plano pode ser selecionado
- [ ] Plano fica destacado
- [ ] Preço exibido corretamente

### Pagamento
- [ ] Redirecionado para Mercado Pago
- [ ] Pagamento processado
- [ ] Redirecionado para sucesso

### Banco de Dados
- [ ] Pagamento registrado
- [ ] Assinatura criada
- [ ] Dados corretos

### Email
- [ ] Email enviado
- [ ] Contém informações corretas
- [ ] Formatação correta

---

## 🐛 Troubleshooting

### Problema: Página não carrega
**Solução:**
1. Verificar se ngrok está rodando
2. Verificar URL do ngrok
3. Limpar cache do navegador

### Problema: Domínio sempre retorna erro
**Solução:**
1. Verificar console do navegador (F12)
2. Verificar logs do PHP
3. Testar API diretamente: `php api/test-check-domain.php`

### Problema: Pagamento não processa
**Solução:**
1. Verificar credenciais Mercado Pago
2. Verificar webhook URL
3. Verificar logs do webhook

### Problema: Email não é enviado
**Solução:**
1. Verificar credenciais SMTP
2. Verificar logs de erro
3. Testar função: `php test-payment-complete.php`

---

## 📊 Resultado Esperado

✅ **Dashboard funcionando perfeitamente**
✅ **Validação de domínio funcionando**
✅ **Pagamento processado com sucesso**
✅ **Banco de dados atualizado**
✅ **Email enviado**
✅ **Sistema pronto para produção**

**Tudo está funcionando!** 🚀
