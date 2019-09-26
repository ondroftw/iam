<?php

namespace m7\Iam;

use App\User;
use Firebase\JWT\JWT;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use \UnexpectedValueException;

class Manager
{
    const   IAM_ACCESS_TOKEN_SESSION_KEY = 'iam_access_token',
            IAM_REFRESH_TOKEN_SESSION_KEY = 'iam_refresh_token';

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
     * @author Adam Ondrejkovic
     * @var
     */
    private $keyFile;

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        $this->client = new GuzzleClient();
        $this->serverUrl = config('iammanager.server');
        $this->keyFile = file_get_contents(base_path(config('iammanager.public_key')));
    }

    /**
     * @return string
     * @author Adam Ondrejkovic
     */
    public function getAuthorizationHeader()
    {
        $token = Session::get(self::IAM_ACCESS_TOKEN_SESSION_KEY);
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

            if ($responseObject->access_token) {

                $this->setSessionValues($responseObject);

                $user = $this->createOrUpdateUser();
                Auth::login($user);

                $user->getScopes();

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
     * @author Adam Ondrejkovic
     */
    public function logout()
    {
        $this->removeSessionValues();
        Auth::logout();
    }

    /**
     * @param $responseObject
     *
     * @author Adam Ondrejkovic
     */
    public function setSessionValues($responseObject)
    {
        Session::put(self::IAM_ACCESS_TOKEN_SESSION_KEY, $responseObject->access_token);
        Session::put(self::IAM_REFRESH_TOKEN_SESSION_KEY, $responseObject->refresh_token);
    }

    /**
     * @author Adam Ondrejkovic
     */
    public function removeSessionValues()
    {
        Session::remove(self::IAM_ACCESS_TOKEN_SESSION_KEY);
        Session::remove(self::IAM_REFRESH_TOKEN_SESSION_KEY);
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
            return explode(" ", $this->getAccessTokenDecoded()->scope);

        } catch (\Exception $exception) {

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
        return Session::get(self::IAM_ACCESS_TOKEN_SESSION_KEY);
    }

    /**
     * @return mixed
     * @author Adam Ondrejkovic
     */
    public function getRefreshToken()
    {
        return Session::get(self::IAM_REFRESH_TOKEN_SESSION_KEY);
    }

    /**
     * @return object
     * @throws \Exception
     * @author Adam Ondrejkovic
     */
    public function getAccessTokenDecoded()
    {
        if (!$this->issetValidAccessToken()) {
            throw new \Exception("Invalid access token is set");
        }

        return JWT::decode($this->getAccessToken(), $this->keyFile, ['RS256']);
    }

    /**
     * @return bool
     * @author Adam Ondrejkovic
     */
    public function refreshToken()
    {
        try {
            $response = $this->client->request('POST', "{$this->serverUrl}/oauth/token", [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'client_id' => config('iammanager.client_id'),
                    'client_secret' => config('iammanager.client_secret'),
                    'refresh_token' => $this->getRefreshToken(),
                ]
            ]);

            $responseObject = json_decode($response->getBody());

            if ($responseObject->access_token) {
                $this->setSessionValues($responseObject);
                return true;
            }

            return false;
        } catch (GuzzleException $exception) {
            Log::error($exception->getMessage());
            return false;
        }
    }

    /**
     * @return bool
     *
     * @throws UnexpectedValueException     Provided JWT was invalid
     * @throws SignatureInvalidException    Provided JWT was invalid because the signature verification failed
     * @throws BeforeValidException         Provided JWT is trying to be used before it's eligible as defined by 'nbf'
     * @throws BeforeValidException         Provided JWT is trying to be used before it's been created as defined by 'iat'
     * @throws ExpiredException             Provided JWT has since expired, as defined by the 'exp' claim
     *
     * @author Adam Ondrejkovic
     */
    public function issetValidAccessToken()
    {
        try {
            JWT::decode($this->getAccessToken(), $this->keyFile, ['RS256']);
            return true;
        } catch (ExpiredException $exception) {
            return $this->refreshToken();
        } catch (UnexpectedValueException $exception) {
            Log::error($exception->getMessage());
            return false;
        }
    }
}
