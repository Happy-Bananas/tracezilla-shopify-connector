<?php

namespace App\Clients;

use GuzzleHttp;
use TracezillaSDK\TracezillaSDK;

class TracezillaClient
{
    /**
     * Base url of tracezilla
     */
    protected $baseUrl = "";

    /**
     * Team slug of team in tracezilla
     */
    protected $teamSlug = "";

    /**
     * Api key to use for authentication
     */
    protected $apiKey = "";

    /**
     * Number of location use for customer
     */
    protected $customerLocationNumber;

    /**
     * Number of location use for warehouse
     */
    protected $warehouseLocationNumber;

    /**
     * Name of tag to attach to orders
     */
    protected $orderTagName;

    /**
     * Array containing special options
     */
    protected $options = [];

    /**
     * TracezillaSDK connection
     */
    protected $connection;

    /**
     * Name of tag to look for when searching skus
     */
    protected $skuTagName;

    public function __construct(array $options = [])
    {
        $this->baseUrl                  = env('TRACEZILLA_BASE_URL');
        $this->teamSlug                 = env('TRACEZILLA_TEAM_SLUG');
        $this->apiKey                   = env('TRACEZILLA_API_KEY');
        $this->customerLocationNumber   = env('TRACEZILLA_CUSTOMER_LOCATION_NUMBER');
        $this->warehouseLocationNumber  = env('TRACEZILLA_WAREHOUSE_LOCATION_NUMBER');
        $this->orderTagName             = env('TRACEZILLA_ORDER_TAG');
        $this->skuTagName               = env('TRACEZILLA_SKU_TAG');
        $this->options                  = $options;

        $this->connection = (new TracezillaSDK($this->baseUrl, $this->teamSlug))
            ->connectUsingAccessToken($this->apiKey);
    }

    /**
     * Get connection
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * Get order tag
     */
    public function orderTag()
    {
        return $this->connection()
            ->Tag()
            ->firstOrCreateByName('Order', $this->orderTagName);
    }

    /**
     * Get customer location
     */
    public function customerLocation()
    {
        $include = isset($this->options['customer_location_include']) ? 
            $this->options['customer_location_include'] :
            ['partner'];

        return $this->connection()
            ->PartnerLocation()
            ->getByNumber($this->customerLocationNumber, $include);
    }

    /**
     * Get customer partner
     */
    public function customerPartner()
    {
        return $this->customerLocation()
            ->getPartner();
    }

    /**
     * Get warehouse location
     */
    public function warehouseLocation()
    {
        return $this->connection()
            ->PartnerLocation()
            ->getByNumber($this->warehouseLocationNumber, ['partner']);
    }

    /**
     * Get warehouse partner
     */
    public function warehousePartner()
    {
        return $this->warehouseLocation()
            ->getPartner();
    }
}
