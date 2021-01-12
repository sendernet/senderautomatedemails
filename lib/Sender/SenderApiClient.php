<?php
/**
 * 2010-2018 Sender.net
 *
 * Sender.net Api Client
 *
 * @author Sender.net <info@sender.net>
 * @copyright 2010-2018 Sender.net
 */

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SenderApiClient
{

    public static $version = '2.0';
    public static $baseUrl = 'https://api.sender.net/v2';
    private $apiKey;
    private $apiEndpoint;
    private $apiEndpointChecker;
    private $commerceEndpoint;
    private $prefixAuth = 'Bearer ';
    private $limit = '?limit=100';
    private $appUrl = 'https://app.sender.net';

    public function __construct($apiKey = null)
    {
        $this->apiKey = null;
        $this->apiEndpoint = self::$baseUrl;
        $this->apiEndpointChecker = self::$baseUrl . '/me';
//        $this->commerceEndpoint = self::$baseUrl . '/commerce/v1';

        if ($apiKey) {
            $this->apiKey = $apiKey;
        }
    }

    /**
     * Returns current Api key
     *
     * @return type
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Return base URL
     *
     * @return type
     */
    public static function getBaseUrl()
    {
        return self::$baseUrl;
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
            $client = new Client();
            $response = $client->get($this->apiEndpointChecker, [
                'headers' => ['Authorization' => $this->prefixAuth . $this->apiKey]
            ]);

            if ($response->getStatusCode() === 200) {
                return true;
            }

        } catch (Exception $e) {
            return false;
        }
//        $response = $this->ping();
//        if (!isset($response->api_key) || !$this->getApiKey() || $response->api_key != $this->getApiKey()) { // Wrong api key
//
//            return false;
//        }
//        return $response;
    }

    /**
     * Generate authentication URL
     *
     * @param string $baseUrl [website base url]
     * @param string $returnUrl [url to return with api key attached]
     */
    public static function generateAuthUrl($baseUrl, $returnUrl)
    {
        $query = http_build_query(array(
            'return' => $returnUrl . '&response_key=API_KEY',
            'return_cancel' => self::$baseUrl,
            'store_baseurl' => $baseUrl,
            'store_currency' => 'EUR'
        ));

        //Make here connection to endpoint which would verify that apiKey is valid
        return self::$baseUrl . 'me';
//        return self::$baseUrl . '/commerce/auth/?' . $query;
    }

    /**
     * Setup commerce request
     * @param array $params
     * @param string $method
     * @return array
     */
    private function makeCommerceRequest($requestConfig, $params, $method)
    {
        $params['api_key'] = $this->getApiKey();
        if (function_exists('curl_version')) {
            return $this->makeCurlRequest($requestConfig, $params, $this->apiEndpoint);
//            return $this->makeCurlRequest(http_build_query(array('data' => $params)), $this->commerceEndpoint . '/' . $method);
        }
        return $this->makeHttpRequest($params, $this->commerceEndpoint . '/' . $method);
    }

    /**
     * Setup api request
     * @param array $params
     * @return array
     */
    private function makeApiRequest($requestConfig, $params)
    {
        if (function_exists('curl_version')) {
            return $this->makeCurlRequest($requestConfig, $params, $this->apiEndpoint);
        }
    }

    /**
     * Make api request through CURL
     * @param $requestConfig
     * @param $data
     * @param $endpoint
     * @return false|mixed
     */
    private function makeCurlRequest($requestConfig, $data, $endpoint)
    {
        #Forming data for curl request
        $formedData = [];
        if (!empty($data)) {
            $formedData = http_build_query($data);
        }
        #Init curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: ' . $this->prefixAuth . $this->apiKey,
        ));

        #Cases get, post
        $httpMethod = $requestConfig['http'] ? $requestConfig['http'] : 'get';
        switch ($httpMethod) {
            case "get":
                curl_setopt($ch, CURLOPT_URL, $endpoint . '/' . $requestConfig['method'] . $this->limit);
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                break;
            case "post":
//                curl_setopt($ch, CURLOPT_URL,'https://api.sender.net/v2/subscribers');
                curl_setopt($ch, CURLOPT_URL, $endpoint . '/' . $requestConfig['method']);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $formedData);
                break;
            case "patch":
                curl_setopt($ch, CURLOPT_URL, $endpoint . '/' . $requestConfig['method']);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $formedData);
                break;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);



        if($status === 200){
            return json_decode($server_output);
        }else{
//            return htmlentities($server_output ,ENT_QUOTES);
            dump('Curl error: ' . curl_error($ch));
            dump($status);
            dump(json_encode($server_output));
        }
        curl_close($ch);
        return false;
    }

    /**
     * @param $customers
     * @param $columns
     * @return array
     */
    public function textImport($customers, $columns)
    {
        $requestConfig = [
            "http" => 'post',
            "method" => "subscribers/text_import",
        ];

        //Send subscribers as per text_import route required field
        $data['customers'] = $customers;
        $columnsFormed = $this->prepareStartImportColumns($columns);

        $textImport = $this->makeHttpRequest($requestConfig, $data);
        return $this->startImport($columnsFormed, $textImport['fileName'], $textImport['rowCount']);
    }

    public function makeHttpRequest($requestConfig, $data)
    {
        $client = new Client();
        try {
            $response = $client->post($this->apiEndpoint . '/' . $requestConfig['method'], [
                'headers' => [
                    'Authorization' => $this->prefixAuth . $this->apiKey,
                    'Accept' => 'Application/json',
                    'Content-type' => 'Application/json'
                ],
                'json' => [
                    'subscribers' => $data['customers']
                ],
            ]);

            return $responseData = json_decode($response->getBody()->getContents(), true);
        }catch (Exception $e){
            dump($e->getMessage());
            exit();
        }
    }

    /**
     * @param $columns
     * @return array
     */
    public function prepareStartImportColumns($columns)
    {
        $columnsTypes = array_unique(array_reduce(array_map('array_keys', $columns), 'array_merge', []));
        foreach ($columnsTypes as $type) {
            $columnsFormed[] = ['type' => $type];
        }

        foreach ($columns as $key) {
            foreach ($key as $label => $value) {
                switch ($label) {
                    case 'email':
                        $columnsFormed[0]['examples'][] = $value;
                        break;
                    case 'firstname':
                        $columnsFormed[1]['examples'][] = $value;
                        break;
                    case 'lastname':
                        $columnsFormed[2]['examples'][] = $value;
                        break;
                }
            }
        }

        return $columnsTypes;
    }

    public function startImport($columns, $fileName, $rowCount)
    {
        $method = 'subscribers/start_import';

        $dataEnd = [
            "emailColumn" => 0,
            "firstnameColumn" => 1,
            "lastnameColumn" => 2,
            'customFieldColumns' => [],
            'columns' => $columns,
            'fileName' => $fileName,
            "source" => "Copy" . '/' . "paste list",
            'rowCount' => $rowCount,
            'tags' => [],
        ];

        try {
            $client = new Client();
            $client->post($this->apiEndpoint . '/' . $method, [
                'json' => $dataEnd,
                'headers' => [
                    'Authorization' => $this->prefixAuth . $this->apiKey,
                    'Accept' => 'Application/json',
                    'Content-type' => 'Application/json'
                ],
            ]);

            $now = date("Y-m-d H:i:s");
            Configuration::updateValue('SPM_SENDERAPP_SYNC_LIST_DATE', $now);

            return $data = [
                'success' => true,
                'message' => $now,
            ];
        } catch (RequestException $e) {
            return $data = [
                'success' => false,
                'message' => $e->getResponse()->getReasonPhrase(),
            ];
        }
    }

    /**
     * Convert cart
     *
     * @param $cartId
     * @return array
     */
    public function cartConvert($cartId)
    {
        $requestConfig = [
            'http' => 'post',
            'method' => "carts/$cartId/convert"
        ];
        $data = [];
//        return $this->makeCommerceRequest($params, 'cart_convert');
        return $response = $this->makeApiRequest($requestConfig, $data);
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

    public function isAlreadySubscriber($emailHash)
    {
        $requestConfig = [
            'http' => 'get',
            'method' => "subscribers/integrations/$emailHash"
        ];

        $data = [];
        $response = $this->makeApiRequest($requestConfig, $data);

        if ($response) {
            return $response->data;
        }
        return;
    }

    public function reactivateSubscriber($id)
    {;
        $requestConfig = [
            'http' => 'post',
            'method' => "subscribers/reactivate"
        ];

        $data['subscribers'] = [0 => $id];

        $response = $this->makeApiRequest($requestConfig, $data);
//        dump($response);
//        exit();
        if ($response) {
            return true;
        }
        return;
    }

    public function addToList($subscriberId, $tagId)
    {
        $requestConfig = [
            'http' => 'post',
            'method' => "subscribers/tags/$tagId"
        ];

        $data['subscribers'] = [0 => $subscriberId];

        $response = $this->makeApiRequest($requestConfig, $data);

        if ($response) {
            return $response;
        }
        return;
    }

    /**
     * Add user or info to mailinglist
     *
     * @param object $recipient
     * @param $listName
     * @return array
     */
    public function addSubscriberAndList($recipient, $listName)
    {
        $requestConfig = [
            'http' => 'post',
            'method' => 'subscribers'
        ];

        $data = [];
        foreach ($recipient as $key => $item) {
            $data[$key] = $item;
        }

        #Validation to not
        if (!empty($listName)) {
            foreach ($listName as $key => $item) {
                $data['tags'] = [$key => $item];
            }
        }

        $response = $this->makeApiRequest($requestConfig, $data);

        if ($response) {
            return $response->data;
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
            $this->module->logDebug('Unable to add fields to subscriber');
            $this->module->logDebug(json_encode($fields));
        }

    }

    public function getExtraCustomFields()
    {
        $data = [];
        $extraCustomFields = ['BIRTHDAY', 'GENDER', 'PARTNER'];
        foreach ($extraCustomFields as $field) {
            if (Configuration::get('SPM_CUSTOMER_FIELD_' . $field)) {
                array_push($data, strtolower($field));
            }
        }
        return $data;
    }

    public function trackCart($params)
    {
        $requestConfig = [
            'http' => 'post',
            'method' => 'carts'
        ];
        $data2 = [$params];
        $data = $params;

        return $response = $this->makeApiRequest($requestConfig, $data);
    }

    /**
     * Delete user from mailinglist
     *
     * @param object $recipient
     * @param int $listId
     * @return array
     */
    public function listRemove($recipient, $listId)
    {
        $data = array(
            "method" => "listRemove",
            "params" => array(
                "list_id" => $listId,
                "emails" => $recipient
            )
        );

        return $this->makeApiRequest($data);
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
        ];

        $data = [];

        $response = $this->makeApiRequest($requestConfig, $data);

        if (isset($response->data)) {
            return $response->data;
        }

        return $response;

//        return $response->data;


//        $params = array(
//            "cart_hash" => $cartHash
//        );
//        return $this->makeCommerceRequest($params, 'cart_get');
    }

    /**
     * Delete cart
     *
     * @param type $cartId
     * @return type
     */
    public function cartDelete($cartId)
    {
        $requestConfig = [
            'http' => "delete/$cartId",
            'method' => 'carts'
        ];

        $data = [];

        $response = $this->makeApiRequest($requestConfig, $data);
        dump($response);
        exit();

//        return $this->makeCommerceRequest($params, 'cart_delete');
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
//        return $response->data;
    }

    public function ping()
    {
        $data = array(
            "method" => "campaigns",
            "params" => array(
                "Authentication" => 'Bearer ' . $this->apiKey,

            )
        );
        return $this->makeApiRequest($data);
    }

}
