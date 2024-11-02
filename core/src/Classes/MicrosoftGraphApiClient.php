<?php

namespace Classes;

use Utils\SessionUtils;

class MicrosoftGraphApiClient
{
    protected $client_id;
    protected $client_secret;
    protected $redirect_uri;
    protected $scopes = 'User.Read';
    protected $access_token;
    protected $tenant_id;

    /**
     * @param $client_id
     * @param $client_secret
     * @param $redirect_uri
     * @param $tenant_id
     */
    public function __construct($client_id, $client_secret, $redirect_uri, $tenant_id)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri = $redirect_uri;
        $this->tenant_id = $tenant_id;
    }


    public function performAuthorizationRedirect()
    {
        header("Location: " . $this->getAuthorizationUrl());
        exit();
    }

    public function getAuthorizationUrl()
    {
        $url = 'https://login.microsoftonline.com/' . $this->tenant_id . '/oauth2/v2.0/authorize?';
        $url .= 'client_id=' . $this->client_id;
        $url .= '&response_type=code';
        $url .= '&redirect_uri=' . urlencode($this->redirect_uri);
        $url .= '&response_mode=query';
        $url .= '&scope=' . urlencode($this->scopes);
        $url .= '&state='. $this->getState();

        return $url;
    }


    public function sendAccessTokenRequest($code)
    {
        $url = 'https://login.microsoftonline.com/' . $this->tenant_id . '/oauth2/v2.0/token';

        $response = $this->sendNonAuthenticatedPostRequest($url, [
            'client_id' => $this->client_id,
            'scope' => $this->scopes,
            'code' => $code,
            'redirect_uri' => $this->redirect_uri,
            'grant_type' => 'authorization_code',
            'client_secret' => $this->client_secret,
        ]);

        $data = json_decode($response, true);
        if (!isset($data['access_token'])) {
            throw new \Exception('Invalid access token response');
        }

        $this->access_token = $data['access_token'];

        return $this->access_token;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    public function getProfile()
    {
        $url = 'https://graph.microsoft.com/v1.0/me';
        $response = $this->sendGetRequest($url);
        $data = json_decode($response, true);
        if (!isset($data['id'])) {
            throw new \Exception('Invalid profile response');
        }

        return $data;
    }

    private function getState()
    {
        $state = SessionUtils::getSessionObject('microsoft_auth_state');
        if (empty($state)) {
            $state = bin2hex(random_bytes(32));
            SessionUtils::saveSessionString('microsoft_auth_state', $state);
        }

        return $state;
    }

    public function sendGetRequest($url)
    {
        return $this->sendNonAuthenticatedGetRequest($url, ['Authorization: Bearer ' . $this->access_token]);
    }

    public function sendNonAuthenticatedGetRequest($url, $headers = [])
    {
        $ch = curl_init($url);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function sendNonAuthenticatedPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
