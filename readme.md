# 💸 Desafio Técnico - API de Transferência entre Usuários (Estilo PicPay)

Este projeto é uma API RESTful desenvolvida em PHP (puro) que simula uma plataforma de pagamentos entre usuários comuns e lojistas, com autenticação, validações de saldo e regras de negócio específicas.

---

## 🚀 Tecnologias Ut# 💸 Desafio Técnico - API de Transferência entre Usuários (PHP Puro)

Este é um desafio técnico para criar uma API RESTful utilizando **PHP puro**, simulando um sistema de transferências entre usuários, similar ao modelo de pagamentos como PicPay. A aplicação inclui autenticação JWT, validações de saldo, regras de negócio específicas e persistência com MySQL.

---

## 🚀 Tecnologias Utilizadas

- ✅ PHP 8+
- ✅ MySQL 5.7 / 8.0
- ✅ Composer
- ✅ JWT (firebase/php-jwt)
- ✅ Postman ou Insomnia (para testes)

---

## 📦 Instalação e Execução

### Pré-requisitos

- PHP 8+
- MySQL
- Composer instalado

### Passos para rodar localmente

```bash
# Clone o repositório
git clone https://github.com/seu-usuario/desafio-transferencia-php.git
cd desafio-transferencia-php

# Instale as dependências
composer install

# Configure o banco de dados
cp .env.example .env

# Crie o banco de dados manualmente no MySQL (nome: desafio_php_api)

# Execute o script SQL
mysql -u root -p desafio_php_api < database.sql

# Rode o servidor embutido do PHP
php -S localhost:8000
