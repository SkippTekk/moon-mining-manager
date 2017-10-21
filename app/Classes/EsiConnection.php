<?php

namespace App\Classes;

use Ixudra\Curl\Facades\Curl;
use Seat\Eseye\Configuration;
use Seat\Eseye\Containers\EsiAuthentication;
use Seat\Eseye\Eseye;
use App\User;

/**
 * Generic class for use by controllers or queued jobs that need to request information
 * from the ESI API.
 */
class EsiConnection
{

    public $esi; // Eseye object for performing all ESI requests
    public $character_id; // reference to the prime user's character ID
    public $corporation_id; // reference to the prime user's corporation ID
    public $token; // reference to the renewed token, needed by the raw curl check for X-Pages header

    /**
     * Class constructor. Create an ESI API object to handle all requests.
     */
    public function __construct()
    {

        // Set config datasource using environment variable.
        $configuration = Configuration::getInstance();
        $configuration->datasource = env('ESEYE_DATASOURCE', 'tranquility');

        // Create authentication with app details and refresh token from nominated prime user.
        $user = User::where('eve_id', env('ESI_PRIME_USER_ID', 0))->first();

        // Need to request a new valid access token from EVE SSO using the refresh token of the original request.
        $url = 'https://sisilogin.testeveonline.com/oauth/token';
        $response = Curl::to($url)
            ->withData(array(
                'grant_type' => "refresh_token",
                'refresh_token' => $user->refresh_token
            ))
            ->withHeaders(array(
                'Authorization: Basic ' . base64_encode(env('TESTEVEONLINE_CLIENT_ID') . ':' . env('TESTEVEONLINE_CLIENT_SECRET'))
            ))
            ->enableDebug('logFile.txt')
            ->post();
        $new_token = json_decode($response);
        $user->refresh_token = $new_token->refresh_token;
        $user->save();

        $authentication = new EsiAuthentication([
            'secret'        => env('TESTEVEONLINE_CLIENT_SECRET'),
            'client_id'     => env('TESTEVEONLINE_CLIENT_ID'),
            'access_token'  => $new_token->access_token,
            'refresh_token' => $user->refresh_token,
            'scopes'        => [
                                'esi-industry.read_corporation_mining.v1',
                                'esi-wallet.read_corporation_wallet.v1',
                                'esi-mail.send_mail.v1',
                                'esi-universe.read_structures.v1',
                            ],
            'token_expires' => date('Y-m-d H:i:s', time() + $new_token->expires_in),
        ]);

        // Create ESI API object.
        $this->esi = new Eseye($authentication);

        // Retrieve the prime user's character details.
        $character = $this->esi->invoke('get', '/characters/{character_id}/', [
            'character_id' => $user->eve_id,
        ]);

        // Set object variables for use by other classes.
        $this->character_id = $user->eve_id;
        $this->corporation_id = $character->corporation_id;
        $this->token = $new_token->access_token;
                
    }
    
}
