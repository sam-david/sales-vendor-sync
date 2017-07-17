<?php namespace App\StitchLite\Vend;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\VariantReference;
use App\ProductReference;
use App\Product;
use App\Variant;
use App\Vendor;

class VendClient {
  protected $clientId;
  protected $clientSecret;
  protected $domainPrefix;
  public $currentVendor;

  public function __construct() {
      $this->clientId = env('VEND_CLIENT_ID');
      $this->clientSecret = env('VEND_CLIENT_SECRET');
      $this->currentVendor = Vendor::where('name', 'Vend')->first();
      $this->domainPrefix = $this->currentVendor->domain_prefix;
  }

  public function getAccessToken($code, $domainPrefix) {
    $client = new Client(); //GuzzleHttp\Client
    $res = $client->request('POST', "https://$domainPrefix.vendhq.com/api/1.0/token", [
        'form_params' => [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'http://localhost:8000/vend/callback'
        ]
    ]);

    return json_decode($res->getBody()->getContents());
  }

  public function getProductVariants() {
    Log::info("GETTING PRODUCTS WITH TOKEN". $this->currentVendor->access_token);
    $accessToken = $this->currentVendor->access_token;
    $client = new Client(); //GuzzleHttp\Client
    $res = $client->request('GET', "https://$this->domainPrefix.vendhq.com/api/products", [
        'headers' => [
          'Authorization' => "Bearer $accessToken"
        ]
      ]);

    // Log::info("PRODUCTS: ". $res->getBody());

    $decodedBody = json_decode($res->getBody());

    // Log::info("Decoded: ". print_r($decodedBody->products, true));

    return $decodedBody->products;
  }

  public function getProduct($id) {
    // pull product by id?
  }

  public function syncWithVendor() {
    $currentVariants = $this->getProductVariants();

    Log::info("PRODUCT COUNT: ". count($currentVariants));

    foreach ($currentVariants as $variant) {
      if ($variant->active && $variant->base_name != 'Discount') { // unable to delete discount item on site, workaround
        Log::info("CURRENT VARIANT SYNC: ". print_r($variant,true));
        $currentProduct = Product::where('name', $variant->base_name)->first();

        $variantInventoryCount = intval($variant->inventory[0]->count);
        Log::info("INVENTORY COUNT" . $variantInventoryCount);

        if ($currentProduct == NULL) {
          $currentProduct = new Product;
          $currentProduct->name = $variant->base_name;
          $currentProduct->save();
          Log::info("CREATED PRODUCT: ". $currentProduct->id);
        }

        // Product refernce skipped because no root product, only variants
        $currentVariantReference = VariantReference::where('external_id', $variant->id)->first();

        if ($currentVariantReference == NULL) {
          $currentVariant = Variant::where('sku', $variant->sku)->first();
          // if no variant, create variant and product, variant ref
          if ($currentVariant == NULL) {
            $newVariant = new Variant;
            $newVariant->sku = $variant->sku;
            $newVariant->price = $variant->price;
            $newVariant->quantity = $variantInventoryCount;
            $newVariant->product_id = $currentProduct->id;
            $newVariant->save();
            Log::info("CREATED VARIANT: ". $newVariant->id);

            $newVariantReference = new VariantReference;
            $newVariantReference->variant_id = $newVariant->id;
            $newVariantReference->external_id = $variant->id;
            $newVariantReference->vendor_id = $this->currentVendor->id;
            $newVariantReference->save();
            Log::info("CREATED VARIANT REFERENCE: ". $newVariantReference->id);
          } else {
            $newVariantReference = new VariantReference;
            $newVariantReference->variant_id = $currentVariant->id;
            $newVariantReference->external_id = $variant->id;
            $newVariantReference->vendor_id = $this->currentVendor->id;
            $newVariantReference->save();
            Log::info("CREATED VARIANT REFERENCE: ". $newVariantReference->id);

            // update variant from shopify to db
            $currentVariant->price = $variant->price;
            $currentVariant->quantity = $variantInventoryCount;
            $currentVariant->save();

          }
        } else {
          // Update quantity and price from shopify to db
          $currentVariant = $currentVariantReference->variant;

          Log::info("UPDATING VARIANT: ". $currentVariant->id);
          $currentVariant->price = $variant->price;
          $currentVariant->quantity = $variantInventoryCount;
          $currentVariant->save();
        }
      }
    }
  }
}
