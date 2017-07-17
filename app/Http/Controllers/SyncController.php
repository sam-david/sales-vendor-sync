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
      // Shopify
      $this->shopifyClient = new ShopifyClient();
      $this->shopifyClient->syncWithVendor();

      // Vend
      $this->vendClient = new VendClient();
      $this->vendClient->syncWithVendor();

      // Validate Vend access token
      $dateNow = date("Y-m-d H:i:s");
      if ($this->vendClient->currentVendor->access_token_expires < $dateNow) {

        return response()->json([
          'status' => 'error',
          'message' => 'Refresh Vend Access token'
        ]);
      }

      return response()->json([
          'status' => 'complete'
      ]);
    }
}
