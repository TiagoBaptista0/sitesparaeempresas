# Sites para Empresas

Plataforma web para criação e gerenciamento de sites para pequenas e médias empresas.

## Funcionalidades

- ✅ Autenticação de usuários com email
- ✅ Confirmação de email
- ✅ Busca e verificação de domínios
- ✅ Dashboard de usuário
- ✅ Gerenciamento de planos e pagamentos
- ✅ Suporte ao cliente
- ✅ Painel administrativo

## Tecnologias

- **Backend**: PHP 7.4+
- **Banco de Dados**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Email**: PHPMailer com SMTP
- **Composer**: Gerenciamento de dependências

## Instalação

### Pré-requisitos

- PHP 7.4 ou superior
- MySQL/MariaDB
- Composer
- Git

### Passos

1. Clone o repositório:
```bash
git clone https://github.com/TiagoBaptista0/sitesparaeempresas.git
cd sitesparaeempresas
```

2. Instale as dependências:
```bash
composer install
```

3. Configure o arquivo `.env`:
```bash
cp .env.example .env
```

4. Configure o banco de dados em `config/db.php`

5. Importe o schema do banco:
```bash
mysql -u seu_usuario -p seu_banco < db/schema.sql
```

6. Inicie o servidor:
```bash
php -S localhost:8000
```

## Estrutura do Projeto

```
├── admin/              # Painel administrativo
├── api/                # Endpoints da API
├── assets/             # CSS, JS, imagens
├── config/             # Configurações
├── dashboard/          # Dashboard do usuário
├── db/                 # Schema do banco de dados
├── includes/           # Headers e footers
├── uploads/            # Arquivos enviados
├── vendor/             # Dependências Composer
└── index.php           # Página inicial
```

## Configuração de Email

O projeto usa PHPMailer para envio de emails. Configure as variáveis de ambiente:

```env
MAIL_HOST=seu_smtp_host
MAIL_PORT=587
MAIL_USERNAME=seu_email
MAIL_PASSWORD=sua_senha
MAIL_FROM=noreply@seudominio.com
```

## Contribuindo

1. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
2. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
3. Push para a branch (`git push origin feature/AmazingFeature`)
4. Abra um Pull Request

## Licença

Este projeto está sob a licença MIT.

## Autor

Tiago Baptista - [@TiagoBaptista0](https://github.com/TiagoBaptista0)
