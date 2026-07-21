<?php

namespace App\Console\Commands;

use App\Connectors\ShopifyConnection;
use App\Connectors\TracezillaConnection;
use Carbon\Carbon;
use Illuminate\Console\Command;
use TracezillaSDK\TracezillaSDK;

class FinishOpenOrdersInTracezilla extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finish-open-orders-in-tracezilla';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finish old open Shopify orders in tracezilla';

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

        $tracezilla                 = new TracezillaConnection();

        $customerLocationId         = $tracezilla->customerLocation()->id();

        $warehouseLocationId        = $tracezilla->warehouseLocation()->id();

        $finishWithInvoice          = env('FINISH_ORDERS_WITH_INVOICE', false);
        $autoSuggestLots            = env('AUTO_SUGGEST_LOTS', false);

        $daysToGoBack               = env('DAYS_TO_GO_BACK', 3);
        
        $closeOrdersOlderThan       = Carbon::now()->subDays($daysToGoBack)->format('Y-m-d');

        $openOrders = $tracezilla->connection()->SalesOrder()->index([
            'status' => ['in' => 'draft,order,from_edi'],
            'customer_location' => [
                'eq' => $customerLocationId,
            ],
            'pickup_from_location' => [
                'eq' => $warehouseLocationId
            ],
            'order_date' => [
                'lt' => $closeOrdersOlderThan
            ],
            'include' => 'lots_not_selected_count'
        ]);

        do {
            foreach ($openOrders->results() as $openOrder) {
                if ($openOrder['lots_not_selected_count'] > 0 && $autoSuggestLots) {
                    $tracezilla->connection()
                        ->SalesOrder()
                        ->get($openOrder['id'])
                        ->suggestLots();
                        
                    $openOrder = $tracezilla->connection()->SalesOrder()->get($openOrder['id'], true, ['lots_not_selected_count']);
                }
                
                if ($openOrder['lots_not_selected_count'] > 0) {
                    $this->warning("Skipping order # {$openOrder['number']} because of missing lot selection!");
                    continue;
                }

                if ($finishWithInvoice) {
                    $this->info("Finishing sales order # {$openOrder['number']} with invoice!");
                    $tracezilla->connection()->SalesOrder()->get($openOrder['id'])->invoice();
                }
                else {
                    $this->info("Finishing sales order # {$openOrder['number']} without invoice!");
                    $tracezilla->connection()->SalesOrder()->get($openOrder['id'])->finishWithoutInvoice();
                }
            }
        }
        while ($openOrders = $tracezilla->connection()->SalesOrder()->nextPage());
    }
}