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
use Illuminate\Support\Facades\Session;

class Manager
{
    /**
     * @var GuzzleClient
     */
    private $client;

    /**
     * @author Adam Ondrejkovic
     * @var string
     */
    private $serverUrl;

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        $this->client = new GuzzleClient();
        $this->serverUrl = config('iammanager.server');
    }

    /**
     * @return string
     * @author Adam Ondrejkovic
     */
    public function getAuthorizationHeader()
    {
        $token = Session::get('access_token');
        return "Bearer {$token}";
    }

    /**
     * @param string $username
     * @param string $password
     *
     * Requests access token an saves it in session if request was successful
     * Updates or creates user which token belongs to
     *
     * @return bool
     * @author Adam Ondrejkovic
     */
    public function login(string $username, string $password)
    {
        try {
            $response = $this->client->request('POST', "{$this->serverUrl}/oauth/token", [
                'form_params' => [
                    'grant_type' => config('iammanager.grant_type'),
                    'username' => $username,
                    'password' => $password,
                    'scope' => '',
                    'client_id' => config('iammanager.client_id'),
                    'client_secret' => config('iammanager.client_secret'),
                ]
            ]);

            $responseObject = json_decode($response->getBody());

            if ($token = $responseObject->access_token) {
                Session::put("access_token", $token);
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

    /**
     * @return bool
     * @author Adam Ondrejkovic
     */
    public function createOrUpdateUser()
    {
        try {
            $response = $this->client->request("GET", "{$this->serverUrl}/users/whoAmI", [
                "headers" => [
                    "Authorization" => $this->getAuthorizationHeader(),
                ]
            ]);

            dd(json_decode($response->getBody()));
            return true;

        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
            return false;

        }
    }
}
