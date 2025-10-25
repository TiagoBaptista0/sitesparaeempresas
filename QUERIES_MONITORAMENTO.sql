# 📊 Queries SQL para Monitoramento

## 🔍 Monitoramento de Pagamentos

### Todos os pagamentos
```sql
SELECT 
    p.id,
    p.usuario_id,
    u.email,
    p.dominio,
    p.valor,
    p.valor_plano,
    p.valor_dominio,
    p.status,
    p.mercadopago_payment_id,
    p.data_criacao
FROM pagamentos p
JOIN usuarios u ON p.usuario_id = u.id
ORDER BY p.id DESC;
```

### Pagamentos aprovados
```sql
SELECT 
    p.id,
    p.usuario_id,
    u.email,
    p.dominio,
    p.valor,
    p.status,
    p.data_pagamento
FROM pagamentos p
JOIN usuarios u ON p.usuario_id = u.id
WHERE p.status = 'aprovado'
ORDER BY p.data_pagamento DESC;
```

### Pagamentos pendentes
```sql
SELECT 
    p.id,
    p.usuario_id,
    u.email,
    p.dominio,
    p.valor,
    p.status,
    p.data_criacao
FROM pagamentos p
JOIN usuarios u ON p.usuario_id = u.id
WHERE p.status = 'pendente'
ORDER BY p.data_criacao DESC;
```

### Pagamentos recusados
```sql
SELECT 
    p.id,
    p.usuario_id,
    u.email,
    p.dominio,
    p.valor,
    p.status,
    p.data_criacao
FROM pagamentos p
JOIN usuarios u ON p.usuario_id = u.id
WHERE p.status IN ('recusado', 'cancelado')
ORDER BY p.data_criacao DESC;
```

### Total de receita
```sql
SELECT 
    DATE(p.data_pagamento) as data,
    COUNT(*) as total_pagamentos,
    SUM(p.valor) as receita_total,
    SUM(p.valor_plano) as receita_planos,
    SUM(p.valor_dominio) as receita_dominios
FROM pagamentos p
WHERE p.status = 'aprovado'
GROUP BY DATE(p.data_pagamento)
ORDER BY data DESC;
```

## 🌐 Monitoramento de Domínios

### Todos os domínios
```sql
SELECT 
    c.id,
    c.user_id,
    u.email,
    c.dominio,
    c.status,
    c.namecheap_domain_id,
    c.namecheap_order_id,
    c.data_criacao
FROM clientes c
JOIN usuarios u ON c.user_id = u.id
ORDER BY c.id DESC;
```

### Domínios registrados
```sql
SELECT 
    c.id,
    c.user_id,
    u.email,
    c.dominio,
    c.status,
    c.namecheap_domain_id,
    c.namecheap_order_id,
    c.data_criacao
FROM clientes c
JOIN usuarios u ON c.user_id = u.id
WHERE c.status IN ('dominio_registrado', 'dns_configurado', 'ativo')
ORDER BY c.data_criacao DESC;
```

### Domínios com erro
```sql
SELECT 
    c.id,
    c.user_id,
    u.email,
    c.dominio,
    c.status,
    c.data_criacao
FROM clientes c
JOIN usuarios u ON c.user_id = u.id
WHERE c.status IN ('aguardando_dominio_registro', 'aguardando_pagamento')
AND c.data_criacao < DATE_SUB(NOW(), INTERVAL 1 HOUR)
ORDER BY c.data_criacao DESC;
```

### Domínios por status
```sql
SELECT 
    c.status,
    COUNT(*) as total,
    COUNT(CASE WHEN c.namecheap_domain_id IS NOT NULL THEN 1 END) as com_namecheap_id
FROM clientes c
GROUP BY c.status
ORDER BY total DESC;
```

## 📋 Monitoramento de Assinaturas

### Assinaturas ativas
```sql
SELECT 
    a.id,
    a.usuario_id,
    u.email,
    p.nome as plano,
    a.status,
    a.data_inicio,
    a.data_fim,
    a.data_proximo_pagamento,
    a.valor
FROM assinaturas a
JOIN usuarios u ON a.usuario_id = u.id
JOIN planos p ON a.plano_id = p.id
WHERE a.status = 'ativa'
ORDER BY a.data_proximo_pagamento ASC;
```

### Assinaturas expirando
```sql
SELECT 
    a.id,
    a.usuario_id,
    u.email,
    p.nome as plano,
    a.data_fim,
    DATEDIFF(a.data_fim, NOW()) as dias_restantes
FROM assinaturas a
JOIN usuarios u ON a.usuario_id = u.id
JOIN planos p ON a.plano_id = p.id
WHERE a.status = 'ativa'
AND a.data_fim BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
ORDER BY a.data_fim ASC;
```

### Assinaturas canceladas
```sql
SELECT 
    a.id,
    a.usuario_id,
    u.email,
    p.nome as plano,
    a.status,
    a.data_fim,
    a.data_atualizacao
FROM assinaturas a
JOIN usuarios u ON a.usuario_id = u.id
JOIN planos p ON a.plano_id = p.id
WHERE a.status IN ('cancelada', 'suspensa', 'expirada')
ORDER BY a.data_atualizacao DESC;
```

## 📊 Monitoramento de Logs

### Todos os logs
```sql
SELECT 
    l.id,
    l.usuario_id,
    u.email,
    l.acao,
    l.descricao,
    l.data_criacao
FROM logs l
LEFT JOIN usuarios u ON l.usuario_id = u.id
ORDER BY l.id DESC
LIMIT 100;
```

### Logs de domínio
```sql
SELECT 
    l.id,
    l.usuario_id,
    u.email,
    l.acao,
    l.descricao,
    l.dados_adicionais,
    l.data_criacao
FROM logs l
LEFT JOIN usuarios u ON l.usuario_id = u.id
WHERE l.acao LIKE '%dominio%'
ORDER BY l.id DESC
LIMIT 50;
```

### Logs de pagamento
```sql
SELECT 
    l.id,
    l.usuario_id,
    u.email,
    l.acao,
    l.descricao,
    l.dados_adicionais,
    l.data_criacao
FROM logs l
LEFT JOIN usuarios u ON l.usuario_id = u.id
WHERE l.acao LIKE '%pagamento%'
ORDER BY l.id DESC
LIMIT 50;
```

### Logs de erro
```sql
SELECT 
    l.id,
    l.usuario_id,
    u.email,
    l.acao,
    l.descricao,
    l.dados_adicionais,
    l.data_criacao
FROM logs l
LEFT JOIN usuarios u ON l.usuario_id = u.id
WHERE l.acao LIKE '%erro%' OR l.descricao LIKE '%erro%'
ORDER BY l.id DESC
LIMIT 50;
```

### Atividades por usuário
```sql
SELECT 
    l.usuario_id,
    u.email,
    COUNT(*) as total_atividades,
    COUNT(DISTINCT l.acao) as tipos_atividades,
    MAX(l.data_criacao) as ultima_atividade
FROM logs l
LEFT JOIN usuarios u ON l.usuario_id = u.id
WHERE l.usuario_id IS NOT NULL
GROUP BY l.usuario_id
ORDER BY total_atividades DESC;
```

## 🔗 Monitoramento de Relacionamentos

### Fluxo completo de um cliente
```sql
SELECT 
    'Usuário' as tipo,
    u.id,
    u.email,
    u.status,
    u.data_cadastro as data
FROM usuarios u
WHERE u.id = ?

UNION ALL

SELECT 
    'Cliente' as tipo,
    c.id,
    u.email,
    c.status,
    c.data_criacao as data
FROM clientes c
JOIN usuarios u ON c.user_id = u.id
WHERE c.user_id = ?

UNION ALL

SELECT 
    'Pagamento' as tipo,
    p.id,
    u.email,
    p.status,
    p.data_criacao as data
FROM pagamentos p
JOIN usuarios u ON p.usuario_id = u.id
WHERE p.usuario_id = ?

UNION ALL

SELECT 
    'Assinatura' as tipo,
    a.id,
    u.email,
    a.status,
    a.data_criacao as data
FROM assinaturas a
JOIN usuarios u ON a.usuario_id = u.id
WHERE a.usuario_id = ?

ORDER BY data DESC;
```

### Clientes com pagamento e domínio
```sql
SELECT 
    u.id,
    u.email,
    u.nome,
    c.dominio,
    c.status as cliente_status,
    p.valor,
    p.status as pagamento_status,
    a.status as assinatura_status,
    p.data_criacao
FROM usuarios u
LEFT JOIN clientes c ON u.id = c.user_id
LEFT JOIN pagamentos p ON u.id = p.usuario_id
LEFT JOIN assinaturas a ON u.id = a.usuario_id
WHERE c.dominio IS NOT NULL
ORDER BY p.data_criacao DESC;
```

## 📈 Estatísticas

### Resumo geral
```sql
SELECT 
    (SELECT COUNT(*) FROM usuarios) as total_usuarios,
    (SELECT COUNT(*) FROM clientes) as total_clientes,
    (SELECT COUNT(*) FROM pagamentos WHERE status = 'aprovado') as pagamentos_aprovados,
    (SELECT COUNT(*) FROM clientes WHERE status = 'ativo') as clientes_ativos,
    (SELECT COUNT(*) FROM assinaturas WHERE status = 'ativa') as assinaturas_ativas,
    (SELECT SUM(valor) FROM pagamentos WHERE status = 'aprovado') as receita_total,
    (SELECT COUNT(*) FROM logs) as total_logs;
```

### Crescimento por dia
```sql
SELECT 
    DATE(u.data_cadastro) as data,
    COUNT(DISTINCT u.id) as novos_usuarios,
    COUNT(DISTINCT c.id) as novos_clientes,
    COUNT(DISTINCT p.id) as novos_pagamentos,
    SUM(p.valor) as receita_dia
FROM usuarios u
LEFT JOIN clientes c ON u.id = c.user_id AND DATE(c.data_criacao) = DATE(u.data_cadastro)
LEFT JOIN pagamentos p ON u.id = p.usuario_id AND DATE(p.data_criacao) = DATE(u.data_cadastro) AND p.status = 'aprovado'
WHERE u.data_cadastro >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(u.data_cadastro)
ORDER BY data DESC;
```

## 🚨 Alertas

### Pagamentos não processados
```sql
SELECT 
    p.id,
    p.usuario_id,
    u.email,
    p.dominio,
    p.valor,
    p.status,
    TIMESTAMPDIFF(HOUR, p.data_criacao, NOW()) as horas_pendente
FROM pagamentos p
JOIN usuarios u ON p.usuario_id = u.id
WHERE p.status = 'pendente'
AND p.data_criacao < DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY p.data_criacao ASC;
```

### Domínios não registrados
```sql
SELECT 
    c.id,
    c.user_id,
    u.email,
    c.dominio,
    c.status,
    TIMESTAMPDIFF(HOUR, c.data_criacao, NOW()) as horas_aguardando
FROM clientes c
JOIN usuarios u ON c.user_id = u.id
WHERE c.status IN ('aguardando_dominio_registro', 'aguardando_pagamento')
AND c.data_criacao < DATE_SUB(NOW(), INTERVAL 1 HOUR)
ORDER BY c.data_criacao ASC;
```

### Webhooks não recebidos
```sql
SELECT 
    p.id,
    p.usuario_id,
    u.email,
    p.dominio,
    p.valor,
    p.status,
    p.mercadopago_payment_id,
    TIMESTAMPDIFF(MINUTE, p.data_criacao, NOW()) as minutos_desde_criacao
FROM pagamentos p
JOIN usuarios u ON p.usuario_id = u.id
WHERE p.status = 'pendente'
AND p.mercadopago_payment_id IS NULL
AND p.data_criacao < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
ORDER BY p.data_criacao ASC;
```
