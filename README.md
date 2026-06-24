# Tracezilla Shopify Connector

An example Laravel application demonstrating how to integrate Shopify with Tracezilla..

The project serves both as a production-ready integration service and as a reference implementation for developers integrating Shopify with Tracezilla.

## Features

* Shopify API integration
* Scheduled catalog synchronization
* Product and catalog import into Tracezilla
* Docker-based development environment
* PostgreSQL database
* Automated tests
* Extensible service-oriented architecture

## Technology Stack

* PHP 8.4
* Laravel 13
* PostgreSQL 17
* Docker Compose
* Shopify Admin API
* Tracezilla API

# Requirements

* Docker
* Docker Compose
* OrbStack (recommended on macOS)
* Git

No local installation of PHP, Composer, PostgreSQL, or Node.js is required.


## Getting Started

Clone the Repository

git clone git@github.com:trz-open/tracezilla-shopify-connector.git
cd tracezilla-shopify-connector

Configure Environment

Create your environment file:

cp .env.example .env

Update the database settings if necessary:

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=tracezilla_shopify_connector_development
DB_USERNAME=postgres
DB_PASSWORD=postgres

Build and Start

docker compose up -d --build

Generate Application Key

docker compose exec app php artisan key:generate

Run Migrations

docker compose exec app php artisan migrate

Open the Application

http://localhost:8000

Development

Open a shell inside the application container:

docker compose exec app bash

Run tests:

docker compose exec app php artisan test

View logs:

docker compose logs app
docker compose logs db

Stop the application:

docker compose down

Shopify Configuration

The application uses a Shopify custom app installed on a Shopify store.

Example token request:

```
curl -X POST "https://your-store.myshopify.com/admin/oauth/access_token" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_CLIENT_SECRET"
```

Roadmap

* Shopify authentication
* Product synchronization
* Catalog synchronization
* Scheduled imports
* Tracezilla API client
* Webhook support
* Import monitoring and logging
* Production deployment documentation

Contributing

Contributions, bug reports, and feature requests are welcome.

License

MIT License.