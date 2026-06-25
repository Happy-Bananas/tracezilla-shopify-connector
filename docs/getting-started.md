---
title: Getting Started
nav_order: 2
layout: default
---

# Getting Started

Welcome! This guide will help you get the Tracezilla Shopify Connector running on your local machine.

By the end of this guide, you will have:

* The application running locally.
* PostgreSQL up and running.
* The documentation site available locally.
* The project configured and ready to connect to Shopify and Tracezilla.

No prior knowledge of Shopify or Tracezilla is required.

⸻

## Prerequisites

To follow this guide, you will need:

* Git
* Docker (recommended)

The project ships with a Docker-based development environment to make getting started easy. If you prefer, you can run the application using your own PHP and database setup.

⸻

## Clone the Repository

Clone the repository and enter the project directory:

```
git clone https://github.com/Happy-Bananas/tracezilla-shopify-connector.git
cd tracezilla-shopify-connector
```

⸻

## Start the Application

Start all services:

```
docker compose up
```

This will start:

Service	URL
* Laravel Application	http://localhost:8000
* Documentation Site	http://localhost:4000
* PostgreSQL	localhost:5432

The first startup may take a few moments while Docker builds the containers.

⸻

# Configure the Application

Create your local environment file:

```
cp .env.example .env
```

The application can be configured through environment variables, including:

* Application settings
* Database connection
* Shopify credentials
* Tracezilla credentials

The exact configuration values will be covered in the next guides.

⸻

Verify the Installation

You should now be able to:

* ✅ Open the Laravel application.
* ✅ Open the documentation site.
* ✅ Connect to PostgreSQL.
* ✅ Continue configuring the Shopify and Tracezilla integrations.

If something is not working as expected, see the Troubleshooting guide.

⸻

Next Steps

Your local development environment is now ready.

Continue with:

1. Create a Shopify Development Store
2. Create a Shopify Custom App
3. Configure Tracezilla Credentials
4. Run Your First Product Synchronization

Happy coding! 🚀

