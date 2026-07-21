<?php

namespace App\Console\Commands;

use App\Connectors\ShopifyConnection;
use App\Connectors\TracezillaConnection;
use Carbon\Carbon;
use Illuminate\Console\Command;
use TracezillaSDK\Resources\SalesOrder;

class PullOrdersFromShopifyIndividual extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pull-orders-from-shopify-individual';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull orders from Shopify and load them into tracezilla as individual sales orders';

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
                'fields' => 'id,status,line_items,billing_address,shipping_address,phone,email,poNumber,note,created_at',
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
            $orderDate = Carbon::parse($order['created_at'])->setTimezone('UTC')->toDateString();

            $ordered = [];

            foreach ($order['line_items'] as $lineItem) {
                $skuCode = $lineItem['sku'];

                if (!isset($ordered[$skuCode])) {
                    $ordered[$skuCode] = [
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

                $ordered[$skuCode]['quantity'] += $lineItem['quantity'];
                $ordered[$skuCode]['revenue'] += ($lineItem['quantity'] * $lineItem['price']) - $discount - $vat;
            }

            $shippingAddress = $order['shipping_address'];

            $deliverTo = [
                'recipient_name'    => !empty($shippingAddress['company']) ? $shippingAddress['company'] : $shippingAddress['name'],
                'address'           => $shippingAddress['address1'],
                'address_line_2'    => $shippingAddress['address2'],
                'zip'               => $shippingAddress['zip'],
                'city'              => $shippingAddress['city'],
                'state'             => $shippingAddress['province'],
                'state_code'        => $shippingAddress['province_code'],
                'country'           => $shippingAddress['country_code'],
                'contact'           => (!empty($shippingAddress['company']) ? $shippingAddress['name'] : '') . ' ' . $shippingAddress['phone'],
                'is_person'         => empty($shippingAddress['company']),
            ];

            $salesOrder = $tracezilla->connection()->SalesOrder()->setOrderHeader([
                    'ext_ref'               => $orderRefPrefix . $order['id'],
                    'marking'               => !empty($order['poNumber']) ? $order['poNumber'] : null,
                    'delivery_notify_cell'  => $order['phone'],
                    'delivery_notify_email' => $order['email'],
                    'remark'                => $order['note'],
                    'exchange_rate'         => $exchangeRate,
                    'order_date'            => $orderDate,
                    'pickup_date'           => $orderDate,
                    'delivery_date'         => $orderDate,
                    'status'                => 'from_edi',
                    'tag_ids'               => [$orderTagId]
                ])
                ->addPartnerWithRole('customer', $customerPartnerId, $customerLocationId)
                ->addPartnerWithRole('pickup_from', $warehousepartnerId, $warehouseLocationId)
                ->addPartnerWithRole('deliver_to', $customerPartnerId, $deliverTo);

            foreach ($ordered as $skuCode => $soldItem) {
                $quantity = $soldItem['quantity'];
                $unitPrice = $soldItem['revenue'] / $quantity;

                $traceable = $alwaysNoneTraceable ? false : null;

                $salesOrder->addSoldSkuLine('sku_code', $skuCode, $quantity, $unitPrice, $traceable);
            }

            $salesOrder->putSalesOrder(SalesOrder::PRICE_LOGIC_NONE, 
                SalesOrder::MISSING_LOT_SELECTION_ACTION_NONE, 
                SalesOrder::MISSING_INVENTORY_ACTION_NONE, 
                SalesOrder::POST_SAVE_ACTION_NONE, 
                true, 
                true);
        }

        $this->info("Finished pulling orders!");
    }
}
