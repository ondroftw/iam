<?php
/**
 * @author Adam Ondrejkovic
 * Created by PhpStorm.
 * Date: 24/09/2019
 * Time: 14:02
 */

namespace m7\Iam;


use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class Manager
{
    /**
     * @param string $username
     * @param string $password
     *
     * @return bool
     * @author Adam Ondrejkovic
     */
    public function loginWithCredentials(string $username, string $password)
    {
        $client = new GuzzleClient();

        try {
            $res = $client->request('POST', config('iammanager.server'), [
                'form_params' => [
                    'grant_type' => config('iammanager.grant_type'),
                    'username' => $username,
                    'password' => $password,
                    'scope' => '',
                    'client_id' => config('iammanager.client_id'),
                    'client_secret' => config('iammanager.client_secret'),
                ]
            ]);

            $responseObject = json_decode($res->getBody());
            if ($responseObject->access_token) {
                return true;
            } else {
                Log::error("Invalid response from IAM service");
                return false;
            }

        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
            return false;
        }
    }
}
