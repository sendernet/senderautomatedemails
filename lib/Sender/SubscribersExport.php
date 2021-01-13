<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SubscribersExport extends SenderApiClient
{
    public function __construct()
    {
        parent::__construct();
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

    public function makeHttpRequest($requestConfig, $data)
    {
        $client = new Client();
        try {
            $response = $client->post($this->senderBaseUrl . $requestConfig['method'], [
                'headers' => [
                    'Authorization' => $this->prefixAuth . Configuration::get('SPM_API_KEY'),
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
            $client->post($this->senderBaseUrl . $method, [
                'json' => $dataEnd,
                'headers' => [
                    'Authorization' => $this->prefixAuth . Configuration::get('SPM_API_KEY'),
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

}