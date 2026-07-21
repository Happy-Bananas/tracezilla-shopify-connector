<?php

namespace App\Http\Controllers;

use App\Clients\ShopifyClient;
use App\GraphQL\Queries\GetProducts;
use Throwable;

class ShopifyTestController extends Controller
{
    public function createTestSkus(CreateTracezillaTestSkusFromShopify $action)
{
    try {
        $result = $action->handle();

        return view('tracezilla.test', [
            'config' => config('services.tracezilla'),
            'result' => [
                'message' => 'Finished creating test SKUs from Shopify catalog.',
                'response' => $result,
            ],
            'error' => null,
        ]);
    } catch (Throwable $e) {
        return view('tracezilla.test', [
            'config' => config('services.tracezilla'),
            'result' => null,
            'error' => $e->getMessage(),
        ]);
    }
}
    public function show()
    {
        return view('shopify.test', [
            'config'   => config('services.shopify'),
            'result'   => null,
            'products' => null,
            'error'    => null,
        ]);
    }

    public function test()
    {
        try {
            $client = new ShopifyClient();

            return view('shopify.test', [
                'config'   => config('services.shopify'),
                'result'   => [
                    'message'    => 'Shopify connection created successfully.',
                    'connection' => get_class($client->http()),
                ],
                'products' => null,
                'error'    => null,
            ]);
        } catch (Throwable $e) {
            return view('shopify.test', [
                'config'   => config('services.shopify'),
                'result'   => null,
                'products' => null,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    public function listProducts()
    {
        try {
            $client = new ShopifyClient();

            $result = $client->graphql(
                GetProducts::QUERY,
                [
                    'first' => 10,
                ]
            );

            $products = $result['data']['products']['nodes'];

            return view('shopify.test', [
                'config'   => config('services.shopify'),
                'result'   => null,
                'products' => $products,
                'error'    => null,
            ]);
        } catch (Throwable $e) {
            return view('shopify.test', [
                'config'   => config('services.shopify'),
                'result'   => null,
                'products' => null,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}