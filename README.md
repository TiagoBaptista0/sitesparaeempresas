# SitesParaEmpresas.com

Sistema completo de gestão de sites para empresas com integração Mercado Pago.

## 🚀 Funcionalidades

- Sistema de cadastro e login
- 3 planos (Básico, Profissional, Premium)
- Integração com Mercado Pago
- Dashboard do cliente
- Painel administrativo
- Sistema de suporte
- Gestão de pagamentos
- Controle de status dos sites

## 📋 Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Composer
- Conta no Mercado Pago

## 🔧 Instalação

### 1. Instale as dependências
composer install

### 2. Configure o banco de dados

Crie o banco de dados:
CREATE DATABASE sitesparaeempresas;

Importe o schema:
mysql -u root -p sitesparaeempresas < db/schema.sql

### 3. Inicie o servidor
php -S localhost:8000

### 4. Acesse o sistema

- Site: http://localhost:8000
- Admin: http://localhost:8000/admin/

Credenciais do Admin:
- Email: admin@sitesparaempresas.com
- Senha: password

## 💳 Integração Mercado Pago

O sistema está configurado com suas credenciais de teste no arquivo .env

## 📧 E-mail Configurado

noreply@sitesparaempresas.com

## 🎯 Fluxo de Uso

### Para Clientes:
1. Cadastro: Cliente escolhe um plano e se cadastra
2. Login: Acessa o dashboard
3. Pagamento: Realiza o pagamento via Mercado Pago
4. Aguarda: Admin entra em contato para criar o site
5. Acompanha: Visualiza status do site no dashboard

### Para Admin:
1. Login: Acessa o painel admin
2. Visualiza: Lista de novos clientes
3. Contata: Cliente via WhatsApp (link direto)
4. Atualiza: Status do site conforme progresso
5. Gerencia: Pagamentos e suporte

## 📄 Licença

Todos os direitos reservados © 2024 SitesParaEmpresas.com
