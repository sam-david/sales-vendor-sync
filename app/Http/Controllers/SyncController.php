<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\StitchLite\Shopify\ShopifyClient;

class SyncController extends Controller
{
    protected $shopifyClient;

    public function sync()
    {
      $this->shopifyClient = new ShopifyClient('test','test');

      Log::info('SHOPIFY CLIENT: ' . print_r($this->shopifyClient, true));
      return $this->shopifyClient->getProducts();
    }
}
