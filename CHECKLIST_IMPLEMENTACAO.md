# ✅ Checklist de Implementação - Integração Mercado Pago + Namecheap

## 📋 Fase 1: Preparação

- [ ] Ler `INTEGRACAO_RESUMO.md`
- [ ] Ler `SETUP_RAPIDO.md`
- [ ] Ler `MERCADOPAGO_NAMECHEAP_INTEGRATION.md`
- [ ] Ter acesso ao painel Mercado Pago
- [ ] Ter acesso ao painel Namecheap
- [ ] Ter acesso ao painel Hostinger
- [ ] Ter acesso ao servidor (SSH/FTP)

## 🗄️ Fase 2: Banco de Dados

- [ ] Fazer backup do banco de dados
  ```bash
  mysqldump -u usuario -p sitesparaempresas > backup_$(date +%Y%m%d).sql
  ```

- [ ] Executar migração
  ```bash
  php migrate-namecheap.php
  ```

- [ ] Verificar colunas adicionadas
  ```sql
  DESCRIBE clientes;
  DESCRIBE pagamentos;
  ```

- [ ] Verificar se tabela `logs` existe
  ```sql
  SELECT * FROM logs LIMIT 1;
  ```

## 🔧 Fase 3: Configuração de Ambiente

- [ ] Copiar `.env.example` para `.env` (se não existir)
  ```bash
  cp .env.example .env
  ```

- [ ] Adicionar ao `.env`:
  ```env
  MP_ACCESS_TOKEN=seu_token_aqui
  NAMESERVERS=dns1.hostinger.com,dns2.hostinger.com
  BASE_URL=https://seu-dominio.com
  ```

- [ ] Verificar se `.env` está no `.gitignore`
  ```bash
  grep ".env" .gitignore
  ```

- [ ] Testar leitura de variáveis de ambiente
  ```bash
  php -r "echo getenv('MP_ACCESS_TOKEN');"
  ```

## 🌐 Fase 4: Configuração Mercado Pago

- [ ] Acessar https://www.mercadopago.com.br/developers/panel
- [ ] Ir em **Notificações** → **Webhooks**
- [ ] Clicar em **Adicionar novo webhook**
- [ ] Preencher:
  - URL: `https://seu-dominio.com/api/mercadopago_webhook.php`
  - Eventos: `payment.created`, `payment.updated`
- [ ] Clicar em **Salvar**
- [ ] Copiar ID do webhook (para referência)
- [ ] Testar webhook (Mercado Pago fornece botão de teste)

## 🧪 Fase 5: Testes

- [ ] Executar teste de integração
  ```bash
  php test-integration.php
  ```

- [ ] Verificar se todos os testes passaram
  - [ ] Banco de dados conectado
  - [ ] Todas as tabelas existem
  - [ ] Todas as colunas existem
  - [ ] Namecheap API respondendo
  - [ ] Todos os arquivos existem
  - [ ] Variáveis de ambiente configuradas

- [ ] Testar verificação de domínio
  ```bash
  curl "http://localhost/api/check_domain.php?domain=teste123.com"
  ```

- [ ] Testar registro de domínio (manual)
  ```bash
  curl "http://localhost/api/register_domain.php?domain=teste123.com&firstName=Tiago&lastName=Baptista&email=test@example.com"
  ```

- [ ] Testar configuração de DNS (manual)
  ```bash
  curl "http://localhost/api/set_dns.php?domain=teste123.com&nameservers=dns1.hostinger.com,dns2.hostinger.com"
  ```

- [ ] Testar webhook (simulado)
  ```bash
  curl -X POST http://localhost/api/mercadopago_webhook.php \
    -H "Content-Type: application/json" \
    -d '{"type": "payment", "data": {"id": "123456789"}}'
  ```

## 🔄 Fase 6: Teste de Fluxo Completo (Sandbox)

- [ ] Acessar `http://localhost/escolher-dominio.php`
- [ ] Fazer login com usuário de teste
- [ ] Escolher um domínio (ex: `meutestedominiotiago.com`)
- [ ] Clicar em "Verificar"
- [ ] Verificar se domínio aparece como disponível
- [ ] Clicar em "Continuar para Pagamento"
- [ ] Verificar se vai para `dashboard/payment.php`
- [ ] Clicar em "Pagar com Mercado Pago"
- [ ] Completar pagamento no Mercado Pago (usar cartão de teste)
- [ ] Retornar para `payment-success.php`
- [ ] Verificar se pagamento foi registrado no banco
  ```sql
  SELECT * FROM pagamentos ORDER BY id DESC LIMIT 1;
  ```
- [ ] Verificar se cliente foi atualizado
  ```sql
  SELECT * FROM clientes WHERE dominio = 'meutestedominiotiago.com';
  ```
- [ ] Verificar se domínio foi registrado (Namecheap)
  ```sql
  SELECT * FROM logs WHERE acao = 'dominio_registrado' ORDER BY id DESC LIMIT 1;
  ```
- [ ] Verificar se DNS foi configurado
  ```sql
  SELECT * FROM logs WHERE acao = 'dns_configurado' ORDER BY id DESC LIMIT 1;
  ```

## 📊 Fase 7: Verificação de Dados

- [ ] Verificar pagamentos
  ```sql
  SELECT id, usuario_id, dominio, valor, status, data_criacao 
  FROM pagamentos 
  ORDER BY id DESC LIMIT 5;
  ```

- [ ] Verificar clientes
  ```sql
  SELECT id, user_id, dominio, status, namecheap_domain_id, namecheap_order_id 
  FROM clientes 
  ORDER BY id DESC LIMIT 5;
  ```

- [ ] Verificar logs
  ```sql
  SELECT usuario_id, acao, descricao, data_criacao 
  FROM logs 
  WHERE acao LIKE '%dominio%' OR acao LIKE '%pagamento%'
  ORDER BY id DESC LIMIT 10;
  ```

- [ ] Verificar assinaturas criadas
  ```sql
  SELECT * FROM assinaturas ORDER BY id DESC LIMIT 5;
  ```

## 🚀 Fase 8: Produção

- [ ] Fazer backup completo do servidor
- [ ] Atualizar `.env` com credenciais de produção
- [ ] Mudar URL Namecheap para produção em `api/namecheap_helper.php`
  ```php
  define('NAMECHEAP_API_URL', 'https://api.namecheap.com/xml.response');
  ```
- [ ] Testar com domínio real (não registrado)
- [ ] Monitorar logs por 24 horas
- [ ] Verificar se domínios estão sendo registrados corretamente
- [ ] Verificar se DNS está apontando corretamente

## 🔍 Fase 9: Monitoramento

- [ ] Configurar alertas de erro
- [ ] Monitorar `error_log` do PHP
- [ ] Monitorar tabela `logs` do banco
- [ ] Verificar se webhooks estão sendo recebidos
- [ ] Verificar se domínios estão sendo registrados
- [ ] Verificar se DNS está configurado

## 📞 Fase 10: Suporte

- [ ] Documentar processo para suporte
- [ ] Criar guia de troubleshooting
- [ ] Criar guia de recuperação de erros
- [ ] Treinar equipe de suporte

## 🎯 Checklist Final

- [ ] Todos os testes passaram
- [ ] Fluxo completo funcionando
- [ ] Dados sendo salvos corretamente
- [ ] Domínios sendo registrados
- [ ] DNS sendo configurado
- [ ] Logs sendo registrados
- [ ] Webhook recebendo notificações
- [ ] Sistema pronto para produção

## 📝 Notas Importantes

### Sandbox vs Produção
- **Sandbox**: Domínios não são realmente registrados
- **Produção**: Domínios são registrados e cobrados

### Nameservers
- **Sandbox**: Use `ns1.testeempresa.com,ns2.testeempresa.com`
- **Produção**: Use `dns1.hostinger.com,dns2.hostinger.com`

### Credenciais
- Nunca commite `.env` no Git
- Nunca compartilhe credenciais por email
- Use variáveis de ambiente em produção

### Backup
- Faça backup antes de qualquer migração
- Mantenha backups por pelo menos 30 dias
- Teste restauração de backups regularmente

## 🆘 Troubleshooting

### Webhook não recebe notificações
1. Verificar URL no painel Mercado Pago
2. Verificar se URL é acessível externamente
3. Verificar logs em `error_log`
4. Testar com curl

### Domínio não registra
1. Verificar credenciais Namecheap
2. Verificar dados do usuário no banco
3. Verificar logs de atividade
4. Verificar se API Namecheap está respondendo

### DNS não configura
1. Verificar nameservers do Hostinger
2. Verificar formato do domínio
3. Verificar logs de atividade
4. Verificar se domínio foi registrado primeiro

## 📞 Contato

Para dúvidas ou problemas:
- Consulte `MERCADOPAGO_NAMECHEAP_INTEGRATION.md`
- Consulte `SETUP_RAPIDO.md`
- Verifique logs em `error_log`
- Verifique tabela `logs` do banco
