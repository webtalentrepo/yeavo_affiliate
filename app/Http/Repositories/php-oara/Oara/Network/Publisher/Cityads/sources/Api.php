<?php
require_once 'Curl.php';

class CityAds_Api
{

    protected $host = 'http://api.cityads.com';
    protected $resourse = '/api/rest/webmaster/';
    protected $format = 'json';
    protected $accessToken;

    public function login($accessToken = null)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * First step - get OAuth authorization url
     *
     * @param int $clientId
     * @param string $redirectUri
     * @param string $responseType
     * @return string
     */
    public function getAuthorizeUrl($clientId, $redirectUri, $responseType = 'code')
    {
        return $this->host . '/auth/?' . http_build_query([
                'client_id'     => $clientId,
                'redirect_uri'  => $redirectUri,
                'response_type' => $responseType
            ]);
    }

    /**
     * Second step - request an access token by OAuth2 code returned from authorization url
     *
     * @param int $clientId
     * @param string $secretKey
     * @param string $code
     * @param string $redirectUri
     * @return mixed
     */
    public function requestAccessToken($clientId, $secretKey, $code, $redirectUri)
    {
        $post = [
            'client_id'     => $clientId,
            'client_secret' => $secretKey,
            'grant_type'    => 'authorizationCode',
            'code'          => $code,
            'redirect_uri'  => $redirectUri
        ];
        $client = new CityAds_Curl();
        $jsonResponse = $client->post($this->host . '/auth/token', $post);
        $response = json_decode($jsonResponse, true);
        return $response;
    }

    public function get($method, $params = [])
    {
        $client = new CityAds_Curl();
        $getParams = '';
        if (is_array($params) && !empty($params))
            $getParams = '&' . http_build_query($params);
        $methodUrl = $this->host . $this->resourse . $this->getFormat() . '/' . $method . '?remote_auth=' . $this->getAccessToken() . $getParams;
        $response = $client->get($methodUrl);
        return $this->checkedResponse($response);
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setFormat($format)
    {
        if (!in_array($format, ['xml', 'json']))
            $format = 'json';
        $this->format = $format;
        return $this;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    private function checkedResponse($response)
    {
        $format = $this->getFormat();
        switch ($format) {
            case 'json':
                $data = json_decode($response, true);
                break;
            case 'xml':
                $data = simplexml_load_string($response);
                break;
        }
        $this->statusProcess((array)$data);
        return $response;
    }

    private function statusProcess($data)
    {
        if (isset($data['status']) && $data['status'] != 200 && $data['status'] != 204) {
            throw new Exception((string)$data['error'], (int)$data['status']);
        }
    }

    public function post($method, $params = '')
    {
        $client = new CityAds_Curl();
        $methodUrl = $this->host . $this->resourse . $this->getFormat() . '/' . $method . '?remote_auth=' . $this->getAccessToken();
        $response = $client->post($methodUrl, $params);
        return $this->checkedResponse($response);
    }

    public function put($method, $params = '')
    {
        $client = new CityAds_Curl();
        $methodUrl = $this->host . $this->resourse . $this->getFormat() . '/' . $method . '?remote_auth=' . $this->getAccessToken();
        $response = $client->put($methodUrl, $params);
        return $this->checkedResponse($response);
    }
}
