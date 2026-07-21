<?php

namespace App\Console\Commands;

use App\Connectors\ShopifyConnection;
use App\Connectors\TracezillaConnection;
use App\Connectors\TracezillaSDK;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class UpdateInventoryInShopify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-inventory-in-shopify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update inventory in Shopify to levels in tracezilla';

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
        $this->info("Starting to update inventory!");

        $skipShopifyProcesses       = true;

        $tracezilla                 = new TracezillaConnection(['customer_location_include' => ['partner', 'partner.sku_partner_codes']]);
        $shopify                    = new ShopifyConnection();

        $alwaysNoneTraceable        = env('TRACEZILLA_ALWAYS_NONE_TRACEABLE', true);

        $shopifyLocationId          = env('SHOPIFY_LOCATION_ID', null);
        $warehouseLocationId        = $tracezilla->warehouseLocation()->id();

        $customerPartnerResource    = $tracezilla->customerPartner()->resource();

        $productVariants = [];
        $sinceId = null;

        if (!$skipShopifyProcesses) {
            $this->info("Loading product variants from Shopify!");

            do {
                $productVariantsPaging = $shopify->connection()->ProductVariant()->get(['fields' => 'id,sku,inventory_item_id', 'limit' => 250, 'since_id' => $sinceId]);
                $sinceId = collect($productVariantsPaging)->max('id');
                $productVariants = array_merge($productVariantsPaging, $productVariants);
            } while (count($productVariantsPaging) > 249);

            $shopifySkuInventoryMapping = Arr::pluck($productVariants, 'sku', 'inventory_item_id');

            $this->info("Finished loading product variants from Shopify!");

            $this->info("Loading current inventory levels from Shopify!");

            $inventoryLevels = [];
            $sinceId = null;

            $inventoryLevelChunks = [];

            $chunkPos = 0;
            $productVariantPos = 0;

            foreach ($productVariants as $productVariant) {
                if (!isset($inventoryLevelChunks[$chunkPos])) {
                    $inventoryLevelChunks[$chunkPos] = [];
                }

                if (empty($productVariant['inventory_item_id'])) {
                    continue;
                }

                $inventoryLevelChunks[$chunkPos][] = $productVariant['inventory_item_id'];

                if ($productVariantPos > 40) {
                    $productVariantPos = 0;
                    $chunkPos++;
                } else {
                    $productVariantPos++;
                }
            }

            $inventoryLevelChunkCount = count($inventoryLevelChunks);

            foreach ($inventoryLevelChunks as $chunkPos => $inventoryLevelChunk) {
                if (!count($inventoryLevelChunk)) {
                    continue;
                }

                $inventoryLevelsPaging = $shopify->connection()->InventoryLevel->get([
                    'fields' => 'id,available,inventory_item_id',
                    'location_ids' => $shopifyLocationId,
                    'inventory_item_ids' => implode(',', $inventoryLevelChunk),
                    'limit' => 249
                ]);

                if (count($inventoryLevelsPaging)) {
                    $inventoryLevels = array_merge($inventoryLevelsPaging, $inventoryLevels);
                }

                $this->info("Loaded chunk $chunkPos / $inventoryLevelChunkCount!");
            }

            $this->info("Finished loading current inventory levels from Shopify!");
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

        $this->info("Loading current inventory levels from tracezilla!");

        $inventory = $tracezilla->connection()->Inventory()->index([
            'partner_location' => ['eq' => $warehouseLocationId]
        ], ['sku', 'sku.tags']);

        foreach ($inventory->results() as $record) {
            foreach ($record['sku']['tags'] as $skuTag) {
                /**
                 * This is a bundle sku and should not be
                 */
                if (strtolower(trim($skuTag['name'])) === 'bundle') {
                    $this->info("Skipping inventory check for bundle SKU {$record['sku']['sku_code']}!");
                    continue 2;
                }
            }

            if (isset($skuPartnerCodeUoms[$record['sku_id']])) {
                $skuCodeToUse = $skuPartnerCodeUoms[$record['sku_id']];
            } else if (isset($skuPartnerCodes[$record['sku_id']])) {
                $skuCodeToUse = $skuPartnerCodes[$record['sku_id']];
            } else {
                $skuCodeToUse = $record['sku_code'];
            }

            $traceableUoms = $record['sku']['traceable'] ?
                $record['traceable_quantity_available'] * $record['sku']['default_uom_conversion'] :
                0;

            $noneTraceableUoms = $record['none_traceable_quantity_available'] * $record['sku']['none_traceable_uom_conversion'];

            if ($alwaysNoneTraceable || !$record['sku']['traceable']) {
                $availableStockAll[$skuCodeToUse] = $traceableUoms + $noneTraceableUoms;
            } else {
                $availableStockAll[$skuCodeToUse] = $traceableUoms / $record['sku']['default_uom_conversion'];
            }
        }

        $this->info("Finished loading current inventory levels from tracezilla!");

        if (!$skipShopifyProcesses) {
            foreach ($inventoryLevels as $inventoryLevel) {
                $skuCode = $shopifySkuInventoryMapping[$inventoryLevel['inventory_item_id']];

                if ($skuCode === 'SHOPBUNDLE') {
                    continue;
                }

                if (!isset($availableStockAll[$skuCode])) {
                    continue;
                }

                if ($inventoryLevel['available'] <> $availableStockAll[$skuCode]) {
                    $this->info("Will update inventory for $skuCode to {$availableStockAll[$skuCode]} - currently {$inventoryLevel['available']} is available!");
                    continue;

                    try {
                        $shopify->connection()->InventoryLevel->set([
                            'location_id' => $shopifyLocationId,
                            'inventory_item_id' => $inventoryLevel['inventory_item_id'],
                            'available' => $availableStockAll > 0 ?
                                round($availableStockAll[$skuCode], 0) :
                                0
                        ]);
                    } catch (\Exception $e) {
                        $this->error($e->getMessage());
                    }
                }
            }
        }

        $this->info("Finished pulling orders!");
    }
}
