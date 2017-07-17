<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\StitchLite\Shopify\ShopifyClient;
use App\StitchLite\Vend\VendClient;

class SyncController extends Controller
{
    protected $shopifyClient;
    protected $vendClient;

    public function sync()
    {
      // get shopify products
      // $this->shopifyClient = new ShopifyClient();
      // sync with current inv
      // $this->shopifyClient->syncWithVendor();
      // get vend products
      // sync with current inv
      $this->vendClient = new VendClient();
      $this->vendClient->syncWithVendor();
      return ["SYNC COMPLETE"];
    }
}
