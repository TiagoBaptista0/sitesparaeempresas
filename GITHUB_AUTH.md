# Autenticação GitHub - Próximos Passos

## ⚠️ IMPORTANTE: Autenticação Necessária

Para fazer push para o GitHub, você precisa se autenticar. Existem duas opções:

### Opção 1: Personal Access Token (Recomendado)

1. Vá para: https://github.com/settings/tokens
2. Clique em "Generate new token" → "Generate new token (classic)"
3. Configure:
   - **Note**: "Git Push Token"
   - **Expiration**: Escolha um período (90 dias, 1 ano, etc.)
   - **Scopes**: Marque `repo` (acesso completo a repositórios)
4. Clique em "Generate token"
5. **Copie o token** (você não verá novamente!)

Quando o Git pedir autenticação:
```
Username: seu_usuario_github
Password: cole_o_token_aqui
```

### Opção 2: SSH Key (Mais seguro)

1. Gere uma chave SSH:
```bash
ssh-keygen -t ed25519 -C "seu_email@example.com"
```

2. Pressione Enter para aceitar o local padrão
3. Digite uma senha (opcional)

4. Adicione a chave ao GitHub:
   - Vá para: https://github.com/settings/keys
   - Clique em "New SSH key"
   - Cole o conteúdo de `~/.ssh/id_ed25519.pub`

5. Mude a URL do repositório para SSH:
```bash
git remote set-url origin git@github.com:TiagoBaptista0/sitesparaeempresas.git
```

## Testando a Autenticação

Depois de configurar, teste com:
```bash
git push origin main
```

Se tudo estiver correto, você verá:
```
Enumerating objects: X, done.
Counting objects: 100% (X/X), done.
...
To https://github.com/TiagoBaptista0/sitesparaeempresas.git
   220b3f6..92c9d98  main -> main
```

## Salvando Credenciais (Windows)

Para não digitar credenciais toda vez:

```bash
git config --global credential.helper wincred
```

Ou para este repositório apenas:
```bash
git config credential.helper wincred
```

## Verificar Configuração

```bash
git config --list
git remote -v
```

## Troubleshooting

**Erro: "fatal: Authentication failed"**
- Verifique se o token/SSH key está correto
- Verifique se a URL do repositório está correta
- Tente fazer push novamente

**Erro: "Permission denied (publickey)"**
- Verifique se a chave SSH está adicionada ao GitHub
- Teste: `ssh -T git@github.com`

## Próximas Atualizações

Depois de autenticar, para cada atualização:

```bash
# 1. Fazer suas mudanças
# 2. Adicionar arquivos
git add .

# 3. Fazer commit
git commit -m "tipo: descrição"

# 4. Fazer push
git push origin main
```

Veja `GIT_WORKFLOW.md` para mais detalhes!
