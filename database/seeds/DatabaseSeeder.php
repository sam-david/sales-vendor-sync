<?php

use Illuminate\Database\Seeder;
use App\Vendor;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $shopifyVendor = new Vendor;
      $shopifyVendor->name = "Shopify";
      $shopifyVendor->save();

      $vendVendor = new Vendor;
      $vendVendor->name = "Vend";
      $vendVendor->save();
    }
}
