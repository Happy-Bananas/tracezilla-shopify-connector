---
title: Authorize API
parent: Shopify Configuration
nav_order: 220
layout: default
---

# Create custom app

Beforre you can connect the application to Shopify you need an app and to connect it to a shop.

[Go to the Shopify development dashboard](https://dev.shopify.com/dashboard){: .btn .btn-primary }

From the navigation sidebar to the left select **Apps**


Click on the link **Create app** in the bottom right corner
<p align="left">
  <img
    src="{{ '/assets/images/shopify/create-app.png' | relative_url }}"
    alt="Create app"
    width="1374">
</p>


If you already have an app. click on the create app botton at the top right.
<p align="left">
  <img
    src="{{ '/assets/images/shopify/create-app-button.png' | relative_url }}"
    alt="Create app"
    width="194">
</p>

---

## Create a version and assign scopes

You have to grant the app access to your Shopify data.

Click the Select scopes button, search for read_products, and select it. You can also type read_products into the search field.

<p align="left">
  <img
    src="{{ '/assets/images/shopify/create-version.png' | relative_url }}"
    alt="Create a new app version and assign scopes"
    width="1378">
</p>

Give the app version a meaningful name, then click the Release button in the top-right corner.

If you later need to change the scopes, you must create and release a new app version.

> ⚠️ If you update the scopes, you may need to rotate your credentials before the changes take effect.

---
## Get credentials / Rotate credentials

Once the app has been created and selected, you can retrieve the
credentials from the app's **Settings** page by selecting **Settings**
from the sidebar.

<img
    src="{{ '/assets/images/shopify/settings.png' | relative_url }}"
    alt="Settings"
    width="1586">

------------------------------------------------------------------------

This is where you will find the **Client ID** and **Client Secret**
required to integrate your catalog with the tracezilla API. Copy them
and store them in a safe place.

> ⚠️ Your app will not be authorized until it has been
installed on a store.

------------------------------------------------------------------------

## Install the app

Go back to the Partner Dashboard. Here you can see that the app has **0
installs**. Until the app has been installed on a store, it cannot
retrieve data from Shopify.

<img
    src="{{ '/assets/images/shopify/0-installs.png' | relative_url }}"
    alt="Install app"
    width="595">

When you click on the app, select **Install app**.

<img
    src="{{ '/assets/images/shopify/select-install-app.png' | relative_url }}"
    alt="Select Install app"
    width="815">

Select the store where you want to install the app. This is the store
from which the app will retrieve products.

<img
    src="{{ '/assets/images/shopify/select-store.png' | relative_url }}"
    alt="Select store"
    width="527">

Confirm the installation.

<img
    src="{{ '/assets/images/shopify/confirm-installation.png' | relative_url }}"
    alt="Confirm installation"
    width="642">


------------------------------------------------------------------------

## Test your endpoint

First, you need to find the URL of your store. Go to the Partner
Dashboard and select **Stores**.


<img
    src="{{ '/assets/images/shopify/store-url.png' | relative_url }}"
    alt="Store URL"
    width="652">

In the example above, the store name is `banannas-9kgelkqs.myshopify.com`, so the
endpoint URL is:

`https://banannas-9kgelkqs.myshopify.com/admin/oauth/access_token`

You can test the endpoint by updating the `curl` command below and
running it from your favorite terminal.

``` bash
curl -X POST "https://SHOPIFY_SHOP_URL/admin/oauth/access_token" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials" \
  -d "client_id=SHOPIFY_CLIENT_ID" \
  -d "client_secret=SHOPIFY_CLIENT_SECRET"
```

You should receive a response similar to this:

<img
    src="{{ '/assets/images/shopify/access-token.png' | relative_url }}"
    alt="Access token"
    width="831">


------------------------------------------------------------------------

{: .success}
> Congratulations! You have successfully created a working
endpoint for your app.

