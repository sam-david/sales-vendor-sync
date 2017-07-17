<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
  protected $fillable = ['access_token', 'refresh_token', 'access_token_expires', 'domain_prefix'];
}
