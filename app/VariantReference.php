<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VariantReference extends Model
{
    public function variant()
    {
        return $this->belongsTo('App\Variant');
    }

    public function vendor()
    {
        return $this->belongsTo('App\Vendor');
    }
}
