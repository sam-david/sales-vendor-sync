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
      $this->shopifyClient = new ShopifyClient(env('SHOPIFY_API_KEY'), env('SHOPIFY_PASSWORD'));
      // get shopify products
      // sync with current inv
      $this->shopifyClient->syncWithVendor();
      // get vend products
      // sync with current inv
      return ["TOTALLY SYNCED"];
    }
}
