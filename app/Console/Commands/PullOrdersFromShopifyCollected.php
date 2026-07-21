<?php

namespace App\Console\Commands;

use App\Connectors\ShopifyConnection;
use App\Connectors\TracezillaConnection;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PullOrdersFromShopifyCollected extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pull-orders-from-shopify-collected';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull orders from Shopify and load them into tracezilla';

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

        $tracezilla             = new TracezillaConnection();
        $shopify                = new ShopifyConnection();

        $daysToGoBack           = env('DAYS_TO_GO_BACK', 3);
        $exchangeRate           = env('EXCHANGE_RATE', 100);
        $orderRefPrefix         = env('TRACEZILLA_ORDER_REF_PREFIX', 'SHP');
        $alwaysNoneTraceable    = env('TRACEZILLA_ALWAYS_NONE_TRACEABLE', null);    

        $orderTagId             = $tracezilla->orderTag()->id();

        $customerPartnerId      = $tracezilla->customerPartner()->id();
        $customerLocationId     = $tracezilla->customerLocation()->id();

        $warehousepartnerId     = $tracezilla->warehousePartner()->id();
        $warehouseLocationId    = $tracezilla->warehouseLocation()->id();

        $ordered                = [];

        for ($d = 0; $d < $daysToGoBack; $d++) {
            $ordered[Carbon::now()->subDays($d)->setTimezone('UTC')->toDateString()] = [];
        }

        /**
         * Load orders from Shopify
         */
        $shopifyOrders = [];
        $sinceId = null;
        $createdAtMin = Carbon::now()
            ->subDays($daysToGoBack + 1)
            ->setTimezone('UTC')
            ->toIso8601ZuluString();

        do {
            $filters = [
                'status' => 'any',
                'created_at_min' => $createdAtMin,
                'fields' => 'id,status,line_items,created_at',
                'limit' => 250
            ];

            if ($sinceId) {
                $filters['since_id'] = $sinceId;
            } else {
                $filters['order'] = 'id asc';
            }

            $shopifyOrdersPaging = $shopify->connection()->Order()->get($filters);

            $sinceId = collect($shopifyOrdersPaging)->max('id');
            $shopifyOrders = array_merge($shopifyOrdersPaging, $shopifyOrders);
        } while (count($shopifyOrdersPaging) > 249);

        /**
         * Build summarised ordered quantities on a per order date basis
         */
        foreach ($shopifyOrders as $order) {
            if (!empty($order['cancelled_at'])) {
                continue;
            }
            
            $orderDate = Carbon::parse($order['created_at'])->setTimezone('UTC')->toDateString();

            if (!isset($ordered[$orderDate])) {
                continue;
            }

            foreach ($order['line_items'] as $lineItem) {
                $skuCode = $lineItem['sku'];

                if (!isset($ordered[$orderDate][$skuCode])) {
                    $ordered[$orderDate][$skuCode] = [
                        'quantity' => 0,
                        'revenue' => 0
                    ];
                }

                $discount = 0;

                foreach ($lineItem['discount_allocations'] as $discountAllocation) {
                    $discount += $discountAllocation['amount'];
                }

                $vat = 0;

                foreach ($lineItem['tax_lines'] as $taxLine) {
                    $vat += $taxLine['price'];
                }

                $ordered[$orderDate][$skuCode]['quantity'] += $lineItem['quantity'];
                $ordered[$orderDate][$skuCode]['revenue'] += ($lineItem['quantity'] * $lineItem['price']) - $discount - $vat;
            }
        }

        /**
         * Create/update daily summarised orders in tracezilla
         */
        foreach ($ordered as $orderDate => $quantities) {
            $orderRef = $orderRefPrefix . Carbon::parse($orderDate)->format('Ymd');

            $salesOrder = $tracezilla->connection()->SalesOrder()->setOrderHeader([
                    'ext_ref' => $orderRef,
                    'exchange_rate' => $exchangeRate,
                    'order_date' => $orderDate,
                    'pickup_date' => $orderDate,
                    'delivery_date' => $orderDate,
                    'status' => 'from_edi',
                    'tag_ids' => [$orderTagId]
                ])
                ->addPartnerWithRole('customer', $customerPartnerId, $customerLocationId)
                ->addPartnerWithRole('pickup_from', $warehousepartnerId, $warehouseLocationId)
                ->addPartnerWithRole('deliver_to', $customerPartnerId, $customerLocationId);

            foreach ($quantities as $skuCode => $soldItem) {
                $quantity = $soldItem['quantity'];
                $unitPrice = $soldItem['revenue'] / $quantity;

                $this->info("Adding SKU $skuCode: $quantity");

                $salesOrder->addSoldSkuLine('sku_code', $skuCode, $quantity, $unitPrice, $alwaysNoneTraceable);
            }

            $salesOrder->putSalesOrder('none', 'none', 'none', 'none', true, true);
        }

        $this->info("Finished pulling orders!");
    }
}
