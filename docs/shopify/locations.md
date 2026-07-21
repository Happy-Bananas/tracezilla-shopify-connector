---
layout: default
nav_order: 120
parent: Shopify Configuration
title: Products & Locations
---

# Products & Locations


Before testing the integration, you need so securre there is few products and at least one location in your Shopify store.

A Location represents a physical place where inventory is stored, such as a warehouse, retail store, or distribution center. Products are assigned to locations through their inventory levels, allowing Shopify to keep track of stock at each location.

# Edit Locations

Open your Shopify [Admin](https://admin.shopify.com)
Select Settings from the bottom left corner

<p align="left">
  <img
    src="{{ '/assets/images/shopify/select-settings.png' | relative_url }}"
    alt="Select locations"
    width="552">
</p>


Select Locations.

<p align="left">
  <img
    src="{{ '/assets/images/shopify/select-locations.png' | relative_url }}"
    alt="Select locations"
    width="815">
</p>

If ther is no locations you have to add a location

<p align="left">
  <img
    src="{{ '/assets/images/shopify/add-location.png' | relative_url }}"
    alt="Add location"
    width="660">
</p>
---

## Understand How Products Are Assigned to Locations

A SKU belongs to a product variant, while inventory belongs to one or more locations.

```
Product
└── Variant
    ├── SKU
    └── Inventory Item
         ├── Happy Bananas Warehouse → Quantity: 25
         └── Happy Bananas Store     → Quantity: 8
```

This means the same SKU can exist in multiple locations with different inventory quantities.

⸻

Create Products

1. Open Products.
2. Select Add product.
3. Enter a product title.
4. Under Inventory, enter a unique SKU.
5. Save the product.

Repeat until you have created a small product catalog.

⸻

Assign Inventory to a Location

1. Open a product.
2. Scroll to the Inventory section.
3. Select the location.
4. Enter the available quantity.
5. Save the product.

The products are now ready to be retrieved through the Shopify API and synchronized with tracezilla.](Admin)
2. Select Settings from the bottom left corner
3. Select Locations.
4. Rename the default locations or create your own.


---

## Understand How Products Are Assigned to Locations

A SKU belongs to a product variant, while inventory belongs to one or more locations.

```
Product
└── Variant
    ├── SKU
    └── Inventory Item
         ├── Happy Bananas Warehouse → Quantity: 25
         └── Happy Bananas Store     → Quantity: 8
```

This means the same SKU can exist in multiple locations with different inventory quantities.

---

## Create Products

1. Open Products.
2. Select Add product.
3. Enter a product title.
4. Under Inventory, enter a unique SKU.
5. Save the product.

Repeat until you have created a small product catalog.

---

# Assign Inventory to a Location

1. Open a product.
2. Scroll to the Inventory section.
3. Select the location.
4. Enter the available quantity.
5. Save the product.

The products are now ready to be retrieved through the Shopify API and synchronized with tracezilla.