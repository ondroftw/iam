<?php
/**
 * @author Adam Ondrejkovic
 * Created by PhpStorm.
 * Date: 24/09/2019
 * Time: 14:02
 */

namespace m7\Iam;


use App\User;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class Manager
{
    const IAM_TOKEN_SESSION_KEY = 'iam_token';

    const RESPONSE_COLUMNS = [
        "user_id" => "iam_uid",
        "name" => "name",
        "email" => "email",
        "surname" => "surname",
    ];

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

                Session::put(self::IAM_TOKEN_KEY, $token);

                $user = $this->createOrUpdateUser();
                Auth::login($user);

                return $user;

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
     * @return bool|User
     * @author Adam Ondrejkovic
     */
    public function createOrUpdateUser()
    {
        try {
            $responseObject = $this->getUserResponse();
            $data = $this->getEloquentDataArray($responseObject);

            if ($user = User::whereIamUid($responseObject->user_id)->first()) {
                $user->update($data);
            } else {
                $user = User::create($data);
            }

            return $user;

        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
            return false;

        }
    }

    /**
     * @return array
     * @author Adam Ondrejkovic
     */
    public function getUserScopes()
    {
        try {
            $scopes = [];

            foreach ($this->getUserResponse()->groups as $group) {
                foreach ($group->scopes as $scope) {
                    if (!in_array($scope, $scopes)) {
                        $scopes[] = $scope;
                    }
                }
            }

            return $scopes;

        } catch (GuzzleException $exception) {

            Log::error($exception->getMessage());
            return [];

        }
    }

    /**
     * @return mixed
     * @throws GuzzleException
     * @author Adam Ondrejkovic
     */
    public function getUserResponse()
    {
        $response = $this->client->request("GET", "{$this->serverUrl}/users/whoAmI", [
            "headers" => [
                "Authorization" => $this->getAuthorizationHeader(),
            ]
        ]);

        return json_decode($response->getBody());
    }

    /**
     * @param $responseObject
     *
     * @return array
     * @author Adam Ondrejkovic
     */
    public function getEloquentDataArray($responseObject)
    {
        $eloquentDataArray = [];

        foreach (self::RESPONSE_COLUMNS as $responsecolumn => $dbcolumn) {
            $eloquentDataArray[$dbcolumn] = $responseObject->{$responsecolumn};
        }
        $eloquentDataArray["password"] = "n/a";

        return $eloquentDataArray;
    }

    /**
     * @return mixed
     * @author Adam Ondrejkovic
     */
    public function getAccessToken()
    {
        return Session::get(self::IAM_TOKEN_SESSION_KEY);
    }
}
