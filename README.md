# News Aggregator API

A Laravel-based REST API for aggregating news from various sources. Built with Laravel 11, Docker, and MySQL.

## Prerequisites

Before you begin, ensure you have installed:
- Docker Desktop
- Git
- VS Code or any preferred IDE

## Initial Setup

1. **Create Project Directory**

Create and navigate to project directory

mkdir news-aggregator
cd news-aggregator


2. **Clone Repository**

git clone https://github.com/janmithabangera/news-aggregator.git


3. **Create Docker Environment**

Copy example files
cp .env.example .env
cp docker-compose.example.yml docker-compose.yml
Update .env file with these values:
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=news_aggregator
DB_USERNAME=user
DB_PASSWORD=password


## Docker Setup

1. **Start Docker Desktop**
- Open Docker Desktop
- Wait for Docker Engine to start

2. **Build and Start Containers**

Build containers first time

docker compose build
Start containers
docker compose up -d

3. **Install Dependencies**

Install PHP dependencies
docker compose exec app composer install


## Laravel Setup

1. **Generate Application Key**

docker compose exec app php artisan key:generate

2. **Run Database Migrations**

docker compose exec app php artisan migrate

3. **Generate API Documentation**

docker compose exec app php artisan l5-swagger:generate

## Running Background Services

1. **Start Queue Worker**

In a new terminal, run queue worker
docker compose exec app php artisan queue:work


2. **Start Scheduler**

In another terminal, run scheduler
docker compose exec app php artisan schedule:work


3. **Verify Services**
Check queue status
docker compose exec app php artisan queue:status
List scheduled tasks
docker compose exec app php artisan schedule:list


## Verify Installation

1. **Check Application**

Application should be running at:
http://localhost

2. **Check API Documentation**

Swagger documentation should be at:
http://localhost/api/documentation


## Available Endpoints

- **Authentication**
  - POST `/api/register` - Register new user
  - POST `/api/login` - User login
  - POST `/api/logout` - User logout
  - POST `/api/reset-password` - User Reset password

- **Articles**
  - GET `/api/articles` - Get all articles and search 
  - GET `/api/articles/{id}` - Get specific article

- **User Preferences**
  - GET `/api/feed` - Get user preferences
  - POST `/api/preferences` - Update user preferences

## Common Commands

Start containers
docker compose up -d
Stop containers
docker compose down
View logs
docker compose logs -f
Run tests
docker compose exec app php artisan test
Clear cache
docker compose exec app php artisan cache:clear


## Troubleshooting

1. **Permission Issues**

Fix storage permissions
docker compose exec app chmod -R 777 storage


2. **Container Issues**
Rebuild containers
docker compose down
docker compose build --no-cache
docker compose up -d
