# Roasting-Reels — Developer README

Web application for **movie cataloging and reviews**. Users can browse or add movies, write reviews, and view trailers. The app integrates with **TMDB API** for movie data and trailers, and uses **OpenAI GPT-4** for overview translations.

> Tech stack: **Symfony 6.x (PHP)**, **MySQL** with **Doctrine ORM**, **Twig**, **FOSRestBundle + JMS Serializer** for REST API, **LexikJWT + GesdinetJWT** for authentication, **GuzzleHTTP**, deployed on a Symfony environment.

## Features

- **Movie search & autocomplete** using TMDB API.  
- **Trailers** embedded via fetched YouTube IDs.  
- **Review system** with role-based permissions.  
- **REST API** with serialization groups for movies & reviews.  
- **JWT authentication & refresh tokens**.  
- **Overview translation** using OpenAI GPT-4 API.  

## Repository layout (key folders)

```
src/
  Controller/        # APIController, MovieController, ReviewController, Security, Registration, etc.
  Entity/            # User, Movie, Review, RefreshToken
  Form/              # AddMovieFormType, ReviewFormType, LoginFormType, RegistrationFormType
  Services/          # TmdbService, Translator, JWTCreatedSubscriber, PasswordHasher
templates/           # Twig views (home, movie, review, registration, security)
migrations/          # Doctrine migrations
public/              # front controller (index.php), assets, uploads
config/              # Symfony config, bundles, routes, JWT settings
```

## Prerequisites

- **PHP 8.2+** with extensions: `pdo_mysql`, `mbstring`, `intl`, `ctype`, `openssl`, `xml`, `curl`, `json`.  
- **Composer 2.6+**  
- **MySQL 8.x** (or MariaDB 10.6+)  
- **Symfony CLI** (recommended)  
- **TMDB API key** and **OpenAI API key**  

## Local setup

```bash
git clone <your-fork-or-repo-url>.git
cd roasting-reels

# 1) Install PHP dependencies
composer install

# 2) Configure environment
cp .env .env.local
# Edit .env.local:
# APP_SECRET=change_me_32_chars
# DATABASE_URL="mysql://user:pass@127.0.0.1:3306/roastingreels?serverVersion=8.0"
# TMDB_API_KEY=your_tmdb_key
# OPENAI_API_KEY=sk-...

# 3) Create database & run migrations
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n

# 4) Run the dev server
symfony serve   # or php -S 127.0.0.1:8000 -t public
```

Visit **http://127.0.0.1:8000**

## Configuration

- `DATABASE_URL` — MySQL DSN  
- `TMDB_API_KEY` — used by `App\Services\TmdbService`  
- `OPENAI_API_KEY` — used by `App\Services\Translator`  
- `APP_SECRET` — 32-char secret for cookies & CSRF  

### Roles

- **ROLE_USER** — default movie browsing & reviews  
- **ROLE_EDITOR** — required for editing/deleting movies/reviews  

## Common commands

```bash
# Clear cache
php bin/console cache:clear

# Run migrations
php bin/console doctrine:migrations:migrate -n

# Reset DB (development only)
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n
```

## Testing

```bash
php bin/phpunit
```

Add feature and integration tests in the `tests/` folder.

## Security notes

- Never commit `.env.local` or real API keys.  
- Rotate any secrets if exposed.  
- Always enable HTTPS in production.  

## Troubleshooting

- **SQL errors** → check `DATABASE_URL` and MySQL service.  
- **TMDB requests failing** → confirm API key and HTTPS.  
- **GPT-4 translation not working** → check `OPENAI_API_KEY`.  
- **JWT login issues** → verify keypair in `config/jwt/`.  

---

## Authorship & Contact
Developed by **Jose Wong**  
j.wong@mail.com
