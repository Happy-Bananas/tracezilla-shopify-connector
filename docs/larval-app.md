---
title: Larval App 
nav_order: 6
layout: default
---

# Larval Application
This is an introduction to the **Shopify Larval tracezilla** demo application. The goal is to let you ters you configurations and see how you can implement an integration, we will look into the source code, you can use that as a starting point for your integration

---

## Requirenments:
- a **Shopify** account
- a **Shopify** store
- a **Shopify** app connected to the store
- a **tracezilla** demo account
- a valid **Tracezille** API key
- The application running on local host.


---

## Commands
Inside the larval project you will find the following files, that are examples you can use from the terminal

```
app
 └─── Console
        └─── Commands
            ├── CheckLocationsInShopify.php
            ├── FinishOpenOrdersInTracezilla.php
            ├── PullCatalogFromShopify.php
            ├── PullOrdersFromShopifyCollected.php
            ├── PullOrdersFromShopifyIndividual.php
            ├── PushCatalogToShopify.php
            ├── TracezillaSkusFromShopifyCommand.php
            └── UpdateInventoryInShopify.php
     
```

For your convience there is a collection of ready to use **Services** you can copy to your own projects.
The list is not complete but use them as an inspiration and create your own, please send them to me or create a pull request and they will be added for the common good 
```
app/Services/
├── ShopifyCatalogService.php
└── TracezillaSkuService.php
```

The demo app uses the services. you can have a look at them in the following controllers
```
app/Http/Controllers/
├── Controller.php
├── ShopifyTestController.php
└── TracezillaTestController.php
```