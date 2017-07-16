<?php namespace App\StitchLite\Shopify;

class ShopifyClient {
  protected $apiKey;
  protected $apiPassword;

  public function __construct($apikey, $apiPassword) {
      $this->apiKey = $apikey;
      $this->apiPassword = $apiPassword;
  }


  public function getProducts() {
    // pull in products and store as local products
    // name, sku, quantity, price
    return ["Test JSON"];
  }

  public function getProduct($id) {
    // pull product by id?
  }

  public function updateProduct($id) {
    // pull product by id for quantity, etc.
  }

  public function syncWithVendor() {

  }
}
