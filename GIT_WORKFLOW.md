# Workflow Git - Guia Rápido

## Para fazer push de atualizações

Sempre que você fizer mudanças no projeto, siga estes passos:

### 1. Verificar status
```bash
git status
```

### 2. Adicionar arquivos modificados
```bash
# Adicionar arquivo específico
git add caminho/do/arquivo.php

# Ou adicionar todos os arquivos
git add .
```

### 3. Fazer commit com mensagem descritiva
```bash
git commit -m "tipo: descrição breve da mudança"
```

**Tipos de commit recomendados:**
- `feat:` - Nova funcionalidade
- `fix:` - Correção de bug
- `docs:` - Documentação
- `style:` - Formatação de código
- `refactor:` - Refatoração
- `test:` - Testes
- `chore:` - Tarefas de manutenção

**Exemplos:**
```bash
git commit -m "feat: Add user profile page"
git commit -m "fix: Fix email confirmation bug"
git commit -m "docs: Update installation instructions"
```

### 4. Fazer push para o GitHub
```bash
git push origin main
```

## Workflow Completo - Exemplo

```bash
# 1. Fazer suas mudanças nos arquivos
# (editar, criar, deletar arquivos)

# 2. Verificar o que mudou
git status

# 3. Adicionar as mudanças
git add .

# 4. Fazer commit
git commit -m "feat: Add new feature description"

# 5. Enviar para GitHub
git push origin main
```

## Criar uma nova branch para features

```bash
# Criar e mudar para nova branch
git checkout -b feature/nome-da-feature

# Fazer suas mudanças e commits
git add .
git commit -m "feat: Descrição da feature"

# Enviar a branch
git push origin feature/nome-da-feature

# Depois fazer um Pull Request no GitHub
```

## Atualizar seu repositório local

Se você fez mudanças no GitHub (via web), atualize localmente:

```bash
git pull origin main
```

## Desfazer mudanças

```bash
# Desfazer mudanças em um arquivo (antes de add)
git checkout -- caminho/do/arquivo.php

# Desfazer último commit (mantendo mudanças)
git reset --soft HEAD~1

# Desfazer último commit (perdendo mudanças)
git reset --hard HEAD~1
```

## Ver histórico de commits

```bash
# Ver últimos commits
git log --oneline -10

# Ver commits com detalhes
git log --oneline --graph --all
```

## Dicas Importantes

1. **Sempre faça commits pequenos e focados** - Cada commit deve representar uma mudança lógica
2. **Escreva mensagens claras** - Descreva o que foi feito, não como foi feito
3. **Faça push regularmente** - Não deixe muitos commits sem enviar
4. **Revise antes de fazer commit** - Use `git diff` para ver exatamente o que mudou
5. **Ignore arquivos sensíveis** - O `.gitignore` já está configurado para excluir `.env`, `vendor/`, etc.

## Arquivos que NÃO serão versionados (por segurança)

- `.env` - Variáveis de ambiente
- `vendor/` - Dependências do Composer
- `uploads/` - Arquivos enviados
- `sites-clientes/` - Dados de clientes
- Arquivos de teste

Estes arquivos estão no `.gitignore` e não serão enviados para o GitHub.
