<?php
/**
 * 2010-2021 Sender.net
 *
 * Sender.net Api Client
 *
 * @author Sender.net <info@sender.net>
 * @copyright 2010-2021 Sender.net
 */

/**
 * Class SenderApiClient
 */
class SenderApiClient
{
    protected $senderBaseUrl = 'https://api.sender.net/v2/';
    protected $prefixAuth = 'Bearer ';
    protected $apiKey;

    private $limit = '?limit=100';
    private $appUrl = 'https://app.sender.net';
    private $senderStatsBaseUrl = 'https://stats.sender.net/';

    public function __construct($apiKey = null)
    {
        $this->apiKey = null;

        if ($apiKey) {
            $this->apiKey = $apiKey;
        }
    }

    /**
     * @return false|mixed
     */
    public function getApiKey()
    {
        if (!empty($this->apiKey)) {
            return $this->apiKey;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->senderBaseUrl;
    }

    /**
     * @return string
     */
    public function getAppUrl()
    {
        return $this->appUrl;
    }

    /**
     *
     * @param type $key
     * @return boolean
     */
    public function setApiKey($key = null)
    {
        if (!$key) {
            return false;
        }

        $this->apiKey = $key;

        return true;
    }

    /**
     * Try to make api call to check whether
     * the api key is valid
     * Make this to connect a test endpoint
     *
     * @return boolean | true if valid key
     */
    public function checkApiKey()
    {
        try {
            #Init curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: ' . $this->prefixAuth . $this->apiKey,
                'Accept: Application/json',
                'Content-type: Application/json'
            ));

            curl_setopt($ch, CURLOPT_URL, $this->generateAuthUrl());
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);

            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if($status === 200){
                return true;
            }
        }catch (Exception $e) {
            $this->logDebug($this->generateAuthUrl());
            $this->logDebug(json_encode('ch' . $ch));
            $this->logDebug($server_output);
            $this->logDebug($status);
            return false;
        }
    }

    /**
     * @return string
     */
    private function generateAuthUrl()
    {
        return $this->senderBaseUrl . 'me';
    }

    /**
     * Make api request through CURL
     * @param $requestConfig
     * @param $data
     * @param $endpoint
     * @return false|mixed
     */
    private function makeApiRequest($requestConfig, $data)
    {
        #Forming data for curl request
        $formedData = [];
        if (!empty($data)) {
            $formedData = http_build_query($data);
        }

        if (isset($requestConfig['stats']) && $requestConfig['stats']){
            $connectionUrl = $this->senderStatsBaseUrl;
        }else{
            $connectionUrl = $this->senderBaseUrl;
        }

        #Init curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: ' . $this->prefixAuth . $this->apiKey,
        ));

        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        #Cases get, post
        $httpMethod = $requestConfig['http'] ? $requestConfig['http'] : 'get';
        switch ($httpMethod) {
            case "get":
                curl_setopt($ch, CURLOPT_URL, $connectionUrl . $requestConfig['method'] . $this->limit);
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                break;
            case "post":
                curl_setopt($ch, CURLOPT_URL, $connectionUrl . $requestConfig['method']);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $formedData);
                break;
            case "patch":
                curl_setopt($ch, CURLOPT_URL, $connectionUrl . $requestConfig['method']);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $formedData);
                break;
            case "delete":
                curl_setopt($ch, CURLOPT_URL, $connectionUrl . $requestConfig['method']);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $formedData);
                break;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($status === 200) {
            curl_close($ch);
            return json_decode($server_output);
        } else {
            $this->logDebug($connectionUrl . $requestConfig['method']);
            $this->logDebug('cURL Info: ' . json_encode(curl_getinfo($ch)));
            $this->logDebug($server_output);
            $this->logDebug($status);
            curl_close($ch);
            return false;
        }
    }

    public function visitorRegistered($params)
    {
        $requestConfig = [
            'http' => 'post',
            'method' => 'attach_visitor',
            'stats' => true,
        ];

        if (is_null($params['newsletter'])){
            $params['newsletter'] = false;
        }

        if (empty($params['newsletter'])){
            $params['newsletter'] = 0;
        }

        $data = $params;
        return $this->makeApiRequest($requestConfig, $data);
    }

    public function trackCart($params)
    {
        $requestConfig = [
            'http' => 'post',
            'method' => 'carts',
            'stats' => true,
        ];

        return $this->makeApiRequest($requestConfig, $params);
    }

    /**
     * @param $data
     * @param $cartId
     * @return array|false
     */
    public function cartConvert($data, $cartId)
    {
        $requestConfig = [
            'http' => 'post',
            'method' => "carts/$cartId/convert",
            'stats' => true,
        ];

        return $this->makeApiRequest($requestConfig, $data);
    }

    /**
     * @param $data
     * @param $cartId
     * @return array|false
     */
    public function cartUpdateStatus($data, $cartId)
    {
        $requestConfig = [
            'http' => 'patch',
            'method' => "carts/$cartId/status",
            'stats' => true,
        ];

        return $this->makeApiRequest($requestConfig, $data);
    }

    /**
     * Get cart from sender
     *
     * @param type $cartHash
     * @return type
     */
    public function cartGet($cartHash)
    {
        $requestConfig = [
            'http' => "get",
            'method' => "carts/$cartHash",
            'stats' => true,
        ];

        $data = [];

        $response = $this->makeApiRequest($requestConfig, $data);

        if (isset($response->data)) {
            return $response->data;
        }

        return $response;
    }

    /**
     * @param $resourceKey
     * @param $cartId
     * @return array|false
     */
    public function cartDelete($resourceKey, $cartId)
    {
        $requestConfig = [
            'http' => 'delete',
            'method' => "carts/$cartId",
            'stats' => true,
        ];

        $data = [
            'resource_key' => $resourceKey
        ];

        return $this->makeApiRequest($requestConfig, $data);
    }

    /**
     * Retrieve all forms
     * @return mixed
     */
    public function getAllForms()
    {
        $requestConfig = [
            "http" => 'get',
            "method" => "forms",
        ];

        $data = [];

        $response = $this->makeApiRequest($requestConfig, $data);

        return $response->data;
    }

    /**
     * Retrieve specific form via ID
     *
     * @param type $id
     * @return type
     */
    public function getFormById($id)
    {
        $requestConfig = [
            "http" => "get",
            "method" => "forms/$id"
        ];

        $data = [];

        $response = $this->makeApiRequest($requestConfig, $data);

        if ($response) {
            return $response->data;
        }
    }

    /**
     * Retrieve all mailinglists
     *
     * @return type
     */
    public function getAllLists()
    {
        $requestConfig = [
            'http' => 'get',
            "method" => "tags",
        ];
        $data = '';

        $response = $this->makeApiRequest($requestConfig, $data);

        return $response->data;
    }

    /**
     * @param $listId
     * @return mixed
     */
    public function getList($listId)
    {
        $requestConfig = [
            'http' => 'get',
            "method" => "tags/$listId",
        ];
        $data = '';

        $response = $this->makeApiRequest($requestConfig, $data);
        if ($response) {
            return $response->data;
        }

        return;
    }

    public function isAlreadySubscriber($email)
    {
        $requestConfig = [
            'http' => 'get',
            'method' => "subscribers/$email"
        ];

        $data = [];
        $response = $this->makeApiRequest($requestConfig, $data);

        if ($response) {
            return $response->data;
        }
        return false;
    }

    public function reactivateSubscriber($id, $channel)
    {
        $requestConfig = [
            'http' => 'post',
            'method' => "subscribers/reactivate"
        ];

        $data['subscribers'] = [$id];
        $data['channel_status'] = strtoupper($channel);

        $response = $this->makeApiRequest($requestConfig, $data);
        if ($response) {
            return true;
        }
    }

    public function unsubscribe($subscriberId)
    {
        $requestConfig = [
            'http' => "post",
            'method' => "unsubscribes",
        ];

        $data['subscribers'] = [$subscriberId];
        $response = $this->makeApiRequest($requestConfig, $data);

        if ($response) {
            return $response;
        }
        return;
    }

    /**
     * @param $subscriberId
     * @param $fields
     * @return bool
     */
    public function addFields($subscriberId, $fields)
    {
        try {
            foreach ($fields as $fieldId => $value) {
                $requestConfig = [
                    'http' => "patch",
                    'method' => "subscribers/$subscriberId/fields/$fieldId"
                ];
                $data = ['value' => $value];
                $this->makeApiRequest($requestConfig, $data);
            }
            return true;
        } catch (Exception $exception) {
            return false;
        }

    }

    /**
     * Gets current account connected
     * @return mixed
     */
    public function getCurrentAccount()
    {
        $requestConfig = [
            "http" => "get",
            "method" => "accounts/current"
        ];

        $data = [];
        $response = $this->makeApiRequest($requestConfig, $data);

        if ($response) {
            return $response->data;
        }
        return false;
    }

    public function getCurrentUser()
    {
        $requestConfig = [
            "http" => "get",
            "method" => "me"
        ];

        $data = [];
        $response = $this->makeApiRequest($requestConfig, $data);

        if ($response) {
            return $response->data;
        }
        return false;
    }

    public function getCustomFields()
    {
        $requestConfig = [
            'http' => 'get',
            "method" => "fields",
        ];
        $data = [];

        $response = $this->makeApiRequest($requestConfig, $data);

        return $response->data;
    }

    public function addStore($data)
    {
        $requestConfig = [
            'http' => 'post',
            'method' => "stores",
            'stats' => false,
        ];

        $response = $this->makeApiRequest($requestConfig, $data);
        if ($response){
            Configuration::updateValue('SPM_SENDERAPP_STORE_ID', $response->data->id);
        }

    }
    
    public function removeStore()
    {
        $storeId = Configuration::get('SPM_SENDERAPP_STORE_ID');
        if ($storeId) {
            $requestConfig = [
                'http' => "delete",
                'method' => "stores/$storeId",
                'stats' => false
            ];
            $this->makeApiRequest($requestConfig, []);
        }
    }

    public function logDebug($message)
    {
        $this->debugLogger = new FileLogger(0);
        $rootPath = _PS_ROOT_DIR_ . __PS_BASE_URI__ . basename(_PS_MODULE_DIR_);
        $logPath = '/senderautomatedemails/log/sender_automated_emails_logs_' . date('Ymd') . '.log';
        $this->debugLogger->setFilename($rootPath . $logPath);
        $this->debugLogger->logDebug($message);
        $this->logDebugBackoffice($message);
    }

    public function logDebugBackoffice($message)
    {
        //Using 3 as severity to display in backoffice logs as error type
        PrestaShopLogger::addLog($message, 3);
    }
}
