<?php

namespace App\Http\Controllers;

use App\Clients\ShopifyClient;
use Throwable;

class ShopifyTestController extends Controller
{
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
                    'connection' => get_class($client->connection()),
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

            $products = $client
                ->connection()
                ->Product
                ->get([
                    'limit' => 10,
                ]);

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