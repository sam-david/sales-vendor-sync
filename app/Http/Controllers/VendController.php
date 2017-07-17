<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\StitchLite\Vend\VendClient;

class VendController extends Controller
{
  protected $vendClient;

  public function callback(Request $request)
  {
    $this->vendClient = new VendClient();
    $code = $request->all()['code'];
    $domainPrefix = $request->all()['domain_prefix'];
    $accessTokenData = $this->vendClient->getAccessToken($code, $domainPrefix);

    $this->vendClient->currentVendor->update([
      'access_token' => $accessTokenData->access_token,
      'refresh_token' => $accessTokenData->refresh_token,
      'access_token_expires' => gmdate("Y-m-d\TH:i:s\Z", $accessTokenData->expires),
      'domain_prefix' => $domainPrefix
    ]);

    return response()->json([
      'status' => 'complete',
      'message' => 'Access token saved'
    ]);
  }

  public function index() {
    $clientId = env('VEND_CLIENT_ID');
    $authLinkSrc = "https://secure.vendhq.com/connect?response_type=code&client_id=$clientId&redirect_uri=http://localhost:8000/vend/callback";

    return view('vend.index', ['authLinkSrc' => $authLinkSrc]);
  }
}
