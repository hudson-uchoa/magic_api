![Logo Magic](https://media.wizards.com/2018/images/daily/gcYrmy5q9f.png)

![Development Time](https://img.shields.io/badge/Development%20Time-2h%2057m%2042s-green)
![PHP Version](https://img.shields.io/badge/PHP-7.4-blue)  
![MySQL Version](https://img.shields.io/badge/MySQL-5.7-orange)

# Descrição

API desenvolvida como parte do teste técnico para a empresa <a href="https://www.ligamagic.com.br/?adLink=GS19760009189&gad_source=1&gclid=Cj0KCQiAwtu9BhC8ARIsAI9JHakcgR7dxrRJXblzbjO-A0bX0lYNG0SnQW5nT9y2NjVhoK3UvBfNGVIaAsipEALw_wcB" target="_blank">Liga Magic</a>, seguindo o padrão RESTful e utilizando o **Slim Framework**. O projeto foi implementado em **2 horas, 57 minutos e 42 segundos**, atendendo aos requisitos definidos, como:

- Autenticação via JWT, permitindo login e logout.
- Cadastro e gerenciamento de edições do jogo Magic: The Gathering, incluindo nome, data de lançamento e quantidade de cartas.
- Cadastro e gerenciamento de cartas, com atributos como nome, cor, tipo, artista, raridade, imagem, preço e estoque.
- Listagem pública de cartas, com cache implementado para melhorar a performance e reduzir a carga no banco de dados.

A API foi estruturada inteiramente em Programação Orientada a Objetos (POO), garantindo um código mais modular, reutilizável e de fácil manutenção. Além disso, foram utilizadas boas práticas como injeção de dependências (PHP-DI) e cache com Redis, proporcionando maior escalabilidade e eficiência ao projeto.

Foi desenvolvida utilizando PHP 7.4 e MySQL 5.7, seguindo as diretrizes do teste, que exigiam um banco de dados relacional e a utilização mínima de frameworks, sendo permitido apenas o Slim Framework para gerenciar as rotas.

# Tecnologias e Bibliotecas Utilizadas

### 1. [**Slim Framework**](https://www.slimframework.com/)

- Micro-framework PHP para construção de APIs e aplicações web simples e performáticas.
- **Motivo da escolha**: Foi a única framework permitida, e a utilizei para agilizar o processo de desenvolvimento.

### 2. [**Slim-Psr7**](https://github.com/slimphp/Slim-Psr7)

- Implementação da PSR-7, padrão para manipulação de requisições e respostas HTTP.
- Motivo da escolha: Necessário para lidar com requests e responses de forma padronizada dentro do Slim Framework.

### 3. [**PHP-DI**](https://php-di.org/)

- Um contêiner de injeção de dependências para PHP, que gerencia as dependências do projeto de forma eficiente.
- **Motivo da escolha**: Garantir a flexibilidade e a desacoplamento do código, além de melhorar a testabilidade.

### 4. [**php-di/slim-bridge**](https://github.com/php-di/slim-bridge)

- Utilizada para integrar o **PHP-DI** (Container de Injeção de Dependências) com o **Slim Framework**. Essa biblioteca facilita a injeção de dependências e a organização do código.
- **Motivo da escolha**: Facilita a gestão de dependências, tornando o código mais modular e testável.

### 5. [**Redis**](https://redis.io/)

- Sistema de cache em memória utilizado para melhorar a performance da aplicação, armazenando respostas de requisições em cache.
- **Motivo da escolha**: O Redis foi escolhido para gerenciar o cache das requisições e evitar consultas repetitivas ao banco de dados.

### 6. [**Firebase-JWT**](https://github.com/firebase/php-jwt)

- Biblioteca utilizada para criar e validar tokens JWT, usada para a autenticação de usuários na API.
- **Motivo da escolha**: O JWT é uma solução moderna e segura para a autenticação, sem necessidade de manter sessões no servidor.

### 7. [**PHP dotenv**](https://github.com/vlucas/phpdotenv)

- Gerenciamento de variáveis de ambiente através de arquivos .env.
- Motivo da escolha: Facilita a configuração da aplicação sem expor credenciais diretamente no código.

---

# Como Rodar o Projeto

1. Clonar o repositório:

```bash
git clone https://github.com/hudson-uchoa/magic_api.git
cd magic_api
```

2. Instalar dependências:

```bash
composer install
```

3. Configurar as variáveis de ambiente:

Crie um arquivo .env na raiz do projeto com o seguinte conteúdo:

```env
# Banco de Dados
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=liga_magic
DB_USER=root
DB_PASS=root

# JWT (Autenticação)
JWT_SECRET=AXT123
JWT_EXPIRES_IN=3600   # Opcional
JWT_ALGO=HS256        # Opcional

# Redis (Cache)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_TTL=3600        # Opcional
```

4. Iniciar o Servidor Redis:
   Certifique-se de que o Redis esteja rodando. Para iniciar o Redis, execute:

```bash
redis-server
```

5. Criar o banco de dados e as tabelas:
   Acesse o MySQL e execute os seguintes comandos:

```sql
CREATE DATABASE liga_magic;
USE liga_magic;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE editions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_pt VARCHAR(255) NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    release_date DATE NOT NULL,
    card_count INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_pt VARCHAR(255) NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    color VARCHAR(50) NOT NULL,
    type VARCHAR(255) NOT NULL,
    artist VARCHAR(255) NOT NULL,
    rarity VARCHAR(50) NOT NULL,
    image_url TEXT NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL,
    edition_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (edition_id) REFERENCES editions(id) ON DELETE CASCADE
);
```

6. Rodar o servidor:

```bash
php -S localhost:8000 -t public
```

---

# Documentação - Endpoints da API

## 1. Autenticação

### 1.1 Registrar Usuário

- **Método**: `POST`
- **URL**: `/register`
- **Descrição**: Cria um novo usuário no sistema. Requer o envio de um nome, e-mail e senha válidos. O e-mail deve ser único e não pode estar cadastrado anteriormente. Retorna uma mensagem de sucesso ou erro.
- **Parâmetros**:
- - **name**: Nome do usuário (string, obrigatório).
- - **email**: E-mail do usuário (string, obrigatório).
- - **password**: Senha do usuário (string, obrigatório).
- **Resposta de Sucesso**:

```json
{
  "message": "Usuário registrado com sucesso"
}
```

- **Resposta de Erro**:
- - Se algum campo obrigatório não for preenchido:

```json
{
  "error": "Todos os campos são obrigatórios"
}
```

- - Se o e-mail for inválido:

```json
{
  "error": "E-mail inválido"
}
```

- - Se o e-mail já estiver cadastrado:

```json
{
  "error": "E-mail já cadastrado"
}
```

- - Se houver erro ao registrar o usuário:

```json
{
  "error": "Erro ao registrar usuário"
}
```

### 1.2 Login

- **Método**: `POST`
- **URL**: `/login`
- **Descrição**: Realiza o login do usuário. Para isso, é necessário fornecer um e-mail e uma senha válidos. Se as credenciais forem corretas, um token JWT será retornado para autenticação nas requisições subsequentes.
- **Parâmetros**:
- - **email**: E-mail do usuário (string, obrigatório).
- - **password**: Senha do usuário (string, obrigatório).
- **Resposta de Sucesso**:

```json
{
  "message": "Login realizado com sucesso",
  "token": "jwt_token_aqui"
}
```

- **Resposta de Erro**:
- - Se as credenciais forem inválidas:

```json
{
  "error": "Credenciais inválidas"
}
```

### 1.3 Logout

- **Método**: `POST`
- **URL**: `/logout`
- **Descrição**: Realiza o logout do usuário, invalidando o token JWT. O token será adicionado à blacklist no Redis, o que o torna inválido para futuras requisições.
- **Parâmetros**: Nenhum (o token JWT deve ser fornecido no cabeçalho da requisição).
- **Cabeçalho**:
- - **Authorization**: Bearer <token> (o token JWT gerado após o login).
- **Resposta de Sucesso**:

```json
{
  "message": "Deslogado com sucesso"
}
```

- **Resposta de Erro**:
- - Se o token não for fornecido ou for inválido:

```json
{
  "error": "Token não fornecido"
}
```

- - Se o token for inválido:

```json
{
  "error": "Token inválido"
}
```

- **Middleware**:
- - _GuestMiddleware_: As rotas de Login e Register são protegidas pelo middleware _GuestMiddleware_, impedindo que usuários autenticados acessem essas rotas, garantindo que apenas visitantes não logados possam utilizá-las.

---

## 2. Cartas

### 2.1 **Listar Todas as Cartas**

- **Método**: `GET`
- **URL**: `/cards`
- **Descrição**: Retorna a lista de todas as cartas. Utiliza cache com Redis para melhorar a performance, retornando dados armazenados se disponíveis.
- **Parâmetros**: Nenhum.
- **Resposta de Sucesso**:

```json
[
  {
    "id": 1,
    "name_pt": "Torre de Urz",
    "name_en": "Urza's Tower",
    "color": "colorless",
    "type": "Land",
    "artist": "Anson Maddocks",
    "rarity": "Uncommon",
    "image": "https://cards.scryfall.io/large/front/1/e/1e9f09b3-dd2d-4ba9-a57e-4f3c1793f752.jpg?1690006831",
    "description": "Urza's Tower taps for {C}. If you control Urza's Power Plant and Urza's Mine, Urza's Tower produces {C}{C}{C}.",
    "price": 10.5,
    "stock": 5,
    "edition_id": 2,
    "created_at": "2025-02-21 05:41:50",
    "updated_at": "2025-02-21 05:42:29"
  }
]
```

### 2.2 Criar uma Nova Carta

- **Método**: `POST`
- **URL**: `/cards`
- **Descrição**: Cria uma nova carta Magic.
- **Body Esperado (JSON)**:

```json
{
  "name_pt": "Nome em Português",
  "name_en": "Name in English",
  "color": "Cor da Carta",
  "type": "Tipo da Carta",
  "artist": "Nome do Artista",
  "rarity": "Raridade",
  "image": "URL da Imagem",
  "description": "Descrição da Carta",
  "price": 10.5,
  "stock": 5,
  "edition_id": 2
}
```

- **Resposta de Sucesso**:

```json
{
  "message": "Carta criada com sucesso"
}
```

### 2.3 Obter Detalhes de uma Carta Específica

- **Método**: `GET`
- **URL**: `/cards/{id}`
- **Descrição**: Retorna os detalhes de uma carta específica com base no ID fornecido.

- **Parâmetros**: **id (obrigatório)**: ID da carta que deseja obter.

- **Resposta de Sucesso**:

```json
{
  "id": 1,
  "name_pt": "Torre de Urz",
  "name_en": "Urza's Tower",
  "color": "colorless",
  "type": "Land",
  "artist": "Anson Maddocks",
  "rarity": "Uncommon",
  "image": "https://cards.scryfall.io/large/front/1/e/1e9f09b3-dd2d-4ba9-a57e-4f3c1793f752.jpg?1690006831",
  "description": "Urza's Tower taps for {C}. If you control Urza's Power Plant and Urza's Mine, Urza's Tower produces {C}{C}{C}.",
  "price": 10.5,
  "stock": 5,
  "edition_id": 2,
  "created_at": "2025-02-21 05:41:50",
  "updated_at": "2025-02-21 05:42:29"
}
```

- **Resposta de Erro (Carta não encontrada)**:

```json
{
  "error": "Carta não encontrada"
}
```

### 2.4 Atualizar uma Carta Existente

- **Método**: `PUT`
- **URL**: `/cards/{id}`
- **Descrição**: Atualiza os dados de uma carta existente com base no ID fornecido.
- **Parâmetros**: **id (obrigatório)**: ID da carta que deseja atualizar.
- **Body Esperado (JSON)**:

```json
{
  "name_pt": "Nome em Português Atualizado",
  "name_en": "Updated Name in English",
  "color": "Cor da Carta Atualizada",
  "type": "Tipo da Carta Atualizado",
  "artist": "Nome do Artista Atualizado",
  "rarity": "Raridade Atualizada",
  "image": "URL da Imagem Atualizada",
  "description": "Descrição da Carta Atualizada",
  "price": 15.0,
  "stock": 10,
  "edition_id": 2
}
```

- **Resposta de Sucesso**:

```json
{
  "message": "Carta atualizada com sucesso"
}
```

- **Resposta de Erro (Carta não encontrada ou erro ao atualizar)**:

```json
{
  "error": "Erro ao atualizar a carta"
}
```

### 2.5 Excluir uma Carta

- **Método**: `DELETE`
- **URL**: `/cards/{id}`
- **Descrição**: Exclui uma carta com base no ID fornecido.
- **Parâmetros**: **id (obrigatório)**: ID da carta que deseja excluir.
- **Resposta de Sucesso**:

```json
{
  "message": "Carta deletada com sucesso"
}
```

- **Resposta de Erro (Erro ao excluir a carta)**:

```json
{
  "error": "Erro ao deletar a carta"
}
```

- **Middleware**:
- - _AuthMiddleware_: O grupo de rotas de Cartas (exceto o [2.1 Listar Todas as Cartas](#21-listar-todas-as-cartas), que é publico) é protegido pelo middleware _AuthMiddleware_, permitindo o acesso apenas a usuários autenticados.

---

## 3. Edições

### 3.1 Listar Todas as Edições

- **Método**: `GET`
- **URL**: `/editions`
- **Descrição**: Retorna a lista de todas as edições do jogo Magic: The Gathering.
- **Parâmetros**: Nenhum.
  **Resposta de Sucesso**:

```json
[
  {
    "id": 1,
    "name_pt": "Edição do Urza",
    "name_en": "Urza's Edition",
    "release_date": "1997-11-01",
    "card_count": 300,
    "created_at": "2025-02-21 05:41:50",
    "updated_at": "2025-02-21 05:42:29"
  }
]
```

### 3.2 Criar uma Nova Edição

- **Método**: `POST`
- **URL**: `/editions`
- **Descrição**: Cria uma nova edição do jogo Magic: The Gathering.
- **Body Esperado (JSON)**:

```json
{
  "name_pt": "Nome em Português",
  "name_en": "Name in English",
  "release_date": "Data de Lançamento (AAAA-MM-DD)",
  "card_count": "Quantidade de Cartas"
}
```

- **Resposta de Sucesso**:

```json
{
  "message": "Edição criada com sucesso"
}
```

### 3.3 Mostrar Detalhes de uma Edição

- **Método**: `GET`
- **URL**: `/editions/{id}`
- **Descrição**: Retorna os detalhes de uma edição específica pelo ID.
  **Parâmetros**:
- - **id**: ID da edição a ser consultada.
- **Resposta de Sucesso**:

```json
{
  "id": 1,
  "name_pt": "Edição do Urza",
  "name_en": "Urza's Edition",
  "release_date": "1997-11-01",
  "card_count": 300,
  "created_at": "2025-02-21 05:41:50",
  "updated_at": "2025-02-21 05:42:29"
}
```

- **Resposta de Erro (Edição não encontrada)**:

```json
{
  "error": "Edição não encontrada"
}
```

### 3.4 Atualizar uma Edição

- **Método**: `PUT`
- **URL**: `/editions/{id}`
- **Descrição**: Atualiza os detalhes de uma edição existente.
- **Parâmetros**:
- - **id**: ID da edição a ser atualizada.
- **Body Esperado (JSON)**:

```json
{
  "name_pt": "Novo Nome em Português",
  "name_en": "New Name in English",
  "release_date": "Nova Data de Lançamento (AAAA-MM-DD)",
  "card_count": "Nova Quantidade de Cartas"
}
```

- **Resposta de Sucesso**:

```json
{
  "message": "Edição atualizada com sucesso"
}
```

- **Resposta de Erro (Erro ao atualizar a edição)**:

```json
{
  "error": "Erro ao atualizar a edição"
}
```

### 3.5 Deletar uma Edição

- **Método**: `DELETE`
- **URL**: `/editions/{id}`
- **Descrição**: Deleta uma edição do jogo Magic: The Gathering.
- **Parâmetros**:
- - **id**: ID da edição a ser deletada.
- Resposta de Sucesso:

```json
{
  "message": "Edição deletada com sucesso"
}
```

- **Resposta de Erro (Erro ao deletar a edição)**:

```json
{
  "error": "Erro ao deletar a edição"
}
```

- **Middleware**:
- - _AuthMiddleware_
    O grupo de rotas de Edições é protegido pelo middleware AuthMiddleware, permitindo o acesso apenas a usuários autenticados.
