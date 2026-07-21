---
layout: default
parent: tracezilla Configuration
title: Authorize API 
---

Before you can connect the application to tracezilla, you need to
collect a few credentials from your tracezilla account.

In the root of the project you will find a file named `.env.example`.
Rename it to `.env`.

This file contains the environment variables required by the **Shopify
tracezilla Connector** demo application. In this guide you will update
the required values.

``` text
TRACEZILLA_BASE_URL=https://app.tracezilla.com
TRACEZILLA_TEAM_SLUG=null
TRACEZILLA_ORDER_REF_PREFIX=null
TRACEZILLA_CUSTOMER_LOCATION_NUMBER=null
TRACEZILLA_WAREHOUSE_LOCATION_NUMBER=null
TRACEZILLA_ORDER_TAG=null
TRACEZILLA_SKU_TAG=null
```

---

## Find the Team Slug

After signing in to tracezilla, you can find your **Team Slug** in the
browser URL.

<p align="left">
  <img
    src="{{ '/assets/images/tracezilla/team-slug.png' | relative_url }}"
    alt="Team Slug"
    width="632%">
</p>

Update your `.env` file:

``` text
TRACEZILLA_BASE_URL=https://app.tracezilla.com
TRACEZILLA_TEAM_SLUG=happy-bananas-ltd-nj4
```

---

## Order Reference Prefix

For this tutorial we'll use the prefix **SHP** for imported Shopify
orders.

``` text
TRACEZILLA_ORDER_REF_PREFIX=SHP
```

------------------------------------------------------------------------

## Customer and Warehouse Location Numbers

Click the avatar in the upper-right corner and select **Company settings**.

<p align="left">
<img
    src="{{ '/assets/images/tracezilla/select-company-settings.png' | relative_url }}"
    alt="Company settings"
    width="958">
</p>
Company settings
<p>
<img
    src="{{ '/assets/images/tracezilla/customer-location-number.png' | relative_url }}"
    alt="Customer Location"
    width="958">
</p>
Under **Basic information**, locate the location number and use it as
`TRACEZILLA_WAREHOUSE_LOCATION_NUMBER`.

Under **Locations**, locate the customer location number and use it as
`TRACEZILLA_CUSTOMER_LOCATION_NUMBER`.

Your `.env` file should now contain:

``` text
TRACEZILLA_CUSTOMER_LOCATION_NUMBER=1
TRACEZILLA_WAREHOUSE_LOCATION_NUMBER=1
```

------------------------------------------------------------------------

## Tags

Choose tags that identify data imported from **Shopify**.

``` text
TRACEZILLA_ORDER_TAG=Shopify
TRACEZILLA_SKU_TAG=Shopify
```

`Shopify` is used throughout this tutorial, but you can choose any tag
names you prefer.

------------------------------------------------------------------------

## Create an API Token

Click your avatar in the upper-right corner and select **My Account**.

<p align="left">
<img
    src="{{ '/assets/images/tracezilla/my-account.png' | relative_url }}"
    alt="My Account"
    width="958">
</p>
Open **API Tokens**.

<p align="left">
<img
    src="{{ '/assets/images/tracezilla/select-api-token.png' | relative_url }}"
    alt="API Tokens"
    width="958">
</p>
Create a new API token. In this tutorial the token is created with the
**Admin** role.

<p align="left">
`<img
    src="{{ '/assets/images/tracezilla/createl-api-token.png' | relative_url }}"
    alt="Create API Token"
    width="958">
</p>
After the token has been created, copy it immediately.

<p align="left">
<img
    src="{{ '/assets/images/tracezilla/api-token.png' | relative_url }}"
    alt="API Token"
    width="958">
</p>

> ⚠️ **Important**\
> The API token is shown only once. Store it somewhere safe.
>
> Never commit your `.env` file to Git, and make sure only authorized
> users have access to it.

Add the token to your `.env` file:

``` text
TRACEZILLA_API_KEY=eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...
```

------------------------------------------------------------------------

## Test the Configuration

If you're running the application inside Docker, open Laravel Tinker:

``` bash
docker compose exec app php artisan tinker
```

Verify that the environment variables are loaded:

``` php
config('app.name');
env('TRACEZILLA_BASE_URL');
env('TRACEZILLA_TEAM_SLUG');
env('TRACEZILLA_API_KEY') ? 'set' : 'missing';
env('TRACEZILLA_WAREHOUSE_LOCATION_NUMBER');
```

------------------------------------------------------------------------

## Test the tracezilla Client

From Tinker:

``` php
$client = new App\Clients\tracezillaClient();
$client->connection();

$location = $client->warehouseLocation();
$location->data();
```

If everything is configured correctly, you should see output similar to:

``` text
= [
    "id" => "6f26e302-f1a1-42d4-b526-ccf5e5971155",
    "number" => 1,
    "partner_id" => "4bc787c0-9c82-4330-b70f-8c50cad3752f",
    "name" => "Growth and Happiness",
    "country" => "DK",
    ...
]
```
