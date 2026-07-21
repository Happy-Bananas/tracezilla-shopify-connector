<?php

namespace App\Console\Commands;

use App\Connectors\ShopifyConnection;
use App\Connectors\TracezillaConnection;
use Illuminate\Console\Command;

class PushCatalogToShopify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push-catalog-to-shopify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push catalog and prices in tracezilla to Shopify';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Starting to pull orders!");

        $tracezilla                 = new TracezillaConnection(['customer_location_include' => [
                'partner',
                'partner.price_list_sales',
                'partner.price_list_sales.prices',
                'partner.sku_partner_codes'
            ]]);

        $shopify                    = new ShopifyConnection();

        $productTagName             = env('TRACEZILLA_SKU_TAG', 'SHOPIFY');
        $productTagId               = $tracezilla->connection()->Tag()->firstOrCreateByName('Sku', $productTagName)->getId();
        $alwaysNoneTraceable        = env('TRACEZILLA_ALWAYS_NONE_TRACEABLE', true);

        $vatRate                    = env('VAT_RATE', 25);

        $customerPartnerResource    = $tracezilla->customerPartner()->resource();

        /**
         * Get products already in shopify
         */
        $productVariants = [];

        do {
            $productVariantsPaging = $shopify->connection()->ProductVariant()->get(['fields' => 'id,sku,price', 'limit' => 250, 'since_id' => $sinceId]);
            $sinceId = collect($productVariantsPaging)->max('id');
            $productVariants = array_merge($productVariantsPaging, $productVariants);
        } while (count($productVariantsPaging) > 249);

        $shopifySkuMapping = [];

        foreach ($productVariants as $variant) {
            $shopifySkuMapping[$variant['sku']] = [
                'variant_id' => $variant['id'],
                'price' => $variant['price']
            ];
        }

        /**
         * Check to see if there are any custom sku code mappings for the webshop partner
         */
        $skuPartnerCodes        = [];
        $skuPartnerCodeUoms     = [];
        $skuIdsByPartnerCode    = [];

        if (isset($customerPartnerResource['sku_partner_codes']) && is_array($customerPartnerResource['sku_partner_codes'])) {
            foreach ($customerPartnerResource['sku_partner_codes'] as $skuPartnerCode) {
                if (!empty($skuPartnerCode['sku_code_uom'])) {
                    $skuPartnerCodeUoms[$skuPartnerCode['sku_id']]          = $skuPartnerCode['sku_code_uom'];
                    $skuIdsByPartnerCode[$skuPartnerCode['sku_code_uom']]   = $skuPartnerCode['sku_id'];
                }

                if (!empty($skuPartnerCode['sku_code'])) {
                    $skuPartnerCodes[$skuPartnerCode['sku_id']]             = $skuPartnerCodes['sku_code'];
                    $skuIdsByPartnerCode[$skuPartnerCode['sku_code']]       = $skuPartnerCode['sku_id'];
                }
            }
        }

        /**
         * Retrieve skus
         */
        $skus = $tracezilla->connection()->Sku()->index([
            'tags' => ['eq' => $productTagId]
        ], [
            'tags'
        ]);

        $skusCount = 0;

        do {
            foreach ($skus->results() as $sku) {
                if (!isset($customerPartnerResource['price_list_sales']['prices'][$sku['id']])) {
                    continue;
                }

                $price = $customerPartnerResource['price_list_sales']['prices'][$sku['id']];

                /**
                 * Price in price list is per box unit, eg. colli.
                 */
                if ($alwaysNoneTraceable && $sku['traceable'] && !$sku['is_convertible']) {
                    $price = $price / $sku['uom_conversion'];
                }

                /**
                 * Apply VAT to price
                 */
                $price = $price * (1 + ($vatRate / 100));

                if (isset($skuPartnerCodeUoms[$sku['id']])) {
                    $skuCodeToUse = $skuPartnerCodeUoms[$sku['id']];
                } else if (isset($skuPartnerCodes[$sku['id']])) {
                    $skuCodeToUse = $skuPartnerCodes[$sku['id']];
                } else {
                    $skuCodeToUse = $sku['sku_code'];
                }

                if (!isset($shopifySkuMapping[$skuCodeToUse])) {
                    $skusCount++;

                    $productType = '';
                    $vendor = '';

                    foreach ($sku['tags'] as $tag) {
                        if (!$productType && $tag['class'] === 'product_group') {
                            $productType =  $tag['name'];
                        }

                        if (!$vendor &&  $tag['class'] === 'brand') {
                            $vendor =  $tag['name'];
                        }
                    }

                    $shopify->connection()->Product->post([
                        'title' => $sku->global_name,
                        'body_html' => $sku->descriptions ? $sku->descriptions->product_description : '',
                        'vendor' => $vendor,
                        'product_type' => $productType,
                        'variants' => [
                            [
                                'option1' => 'Default',
                                'price' => $price * (1 + ($vatRate / 100)),
                                'sku' => $skuCodeToUse,
                                'grams' => $sku['weight_factor_gross'] * 1000,
                                'inventory_quantity' => 0,
                                'inventory_management' => 'shopify',
                                'taxable' => true,
                                'requires_shipping' => true
                            ]
                        ]
                    ]);
                } else if ($price !== $shopifySkuMapping[$skuCodeToUse]['price']) {
                    $shopify->connection()->ProductVariant($shopifySkuMapping[$skuCodeToUse]['variant_id'])->put([
                        'price' => $price
                    ]);
                }
            }
        } while ($skus = $tracezilla->connection()->Sku()->nextPage());

        $this->info("Finished pushing catalog!");
    }
}
