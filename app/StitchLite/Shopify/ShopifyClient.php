<?php namespace App\StitchLite\Shopify;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\VariantReference;
use App\ProductReference;
use App\Product;
use App\Variant;
use App\Vendor;

class ShopifyClient {
  protected $apiKey;
  protected $apiPassword;
  protected $currentVendor;

  public function __construct() {
      $this->apiKey = env('SHOPIFY_API_KEY');
      $this->apiPassword = env('SHOPIFY_PASSWORD');
      $this->currentVendor = Vendor::where('name', 'Shopify')->first();
  }

  public function getProducts() {
    $client = new Client(); //GuzzleHttp\Client
    $res = $client->request('GET', 'https://sam-d-test-store.myshopify.com/admin/products.json', [
      'auth' => [$this->apiKey, $this->apiPassword]
    ]);

    // Log::info("PRODUCTS: ". $res->getBody());

    $decodedBody = json_decode($res->getBody());

    // Log::info("Decoded: ". print_r($decodedBody->products, true));

    return $decodedBody->products;
  }

  public function getProduct($id) {
    // pull product by id?
  }

  public function updateProductVariant($productId, $variantId, Variant $currentVariant) {
    Log::info("UPDATING PRODUCT:". $productId . " Variantid : ". $variantId. " Variant: ". $currentVariant->id);
    $client = new Client(); //GuzzleHttp\Client
    $res = $client->request('PUT', "https://sam-d-test-store.myshopify.com/admin/products/$productId.json", [
      'auth' => [$this->apiKey, $this->apiPassword],
      'form_params' => [
        'product' => [
          'id' => $productId,
          'variants' => [
            'id' => $variantId,
            'price' => $currentVariant->price,
            'inventory_quantity' => $currentVariant->quantity
          ]
        ]
      ]
    ]);

    $decodedBody = json_decode($res->getBody());
    return $decodedBody;
  }

  public function syncWithVendor() {
    $currentProducts = $this->getProducts();

    foreach ($currentProducts as $product) {
      Log::info("PRODUCT TITLE: ". $product->title);

      $currentProduct = Product::where('name', $product->title)->first(); // good id?

      if ($currentProduct == NULL) {
        $currentProduct = new Product;
        $currentProduct->name = $product->title;
        $currentProduct->save();
        Log::info("CREATED PRODUCT: ". $currentProduct->id);
      }

      $currentProductReference = ProductReference::where('external_id', $product->id)->first();

      if ($currentProductReference == NULL) {
        $currentProductReference = new ProductReference;
        $currentProductReference->external_id = $product->id;
        $currentProductReference->product_id = $currentProduct->id;
        $currentProductReference->save();
        Log::info("CREATED PRODUCT REFERENCE: ". $currentProductReference->id);
      }

      foreach ($product->variants as $variant) {
        Log::info("VARIANT: ". $variant->id);
        $currentVariantReference = VariantReference::where('external_id', $variant->id)->first();

        if ($currentVariantReference == NULL) {
          $currentVariant = Variant::where('sku', $variant->sku)->first();
          // if no variant, create variant and product, variant ref
          if ($currentVariant == NULL) {
            $newVariant = new Variant;
            $newVariant->sku = $variant->sku;
            $newVariant->price = $variant->price;
            $newVariant->quantity = intval($variant->quantity);
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
            $currentVariant->quantity = $variant->inventory_quantity;
            $currentVariant->save();

          }
        } else {
          // Update quantity and price from shopify to db
          $currentVariant = $currentVariantReference->variant;

          Log::info("UPDATING VARIANT: ". $currentVariant->id);
          $currentVariant->price = $variant->price;
          $currentVariant->quantity = $variant->inventory_quantity;
          $currentVariant->save();
        }
      }
    }
  }
}
