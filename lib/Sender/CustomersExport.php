<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class CustomersExport extends SenderApiClient
{
    public function textImport($customers, $columns)
    {
        $requestConfig = [
            "http" => 'post',
            "method" => "subscribers/text_import",
        ];

        $data['customers'] = $customers;
        $columnsFormed = $this->prepareStartImportColumns($columns);

        $textImport = $this->makeTextImportRequest($requestConfig, $data['customers']);

        $this->logDebug('Text import completed');

        $dataStartImport = $this->formStartImportData($columnsFormed, $textImport['fileName'], $textImport['rowCount']);

        return $this->startImport($dataStartImport);
    }

    public function formStartImportData($columns, $fileName, $rowCount)
    {
        $tagId = Configuration::get('SPM_SENDERAPP_SYNC_LIST_ID');

        if ($tagId != 0) {
            $this->logDebug("Will add to $tagId");
            $tag[] = $this->getList($tagId);
        }

        return $dataStartImport = [
            "emailColumn" => 0,
            "firstnameColumn" => 1,
            "lastnameColumn" => 2,
            'customFieldColumns' => [],
            'columns' => $columns,
            'fileName' => $fileName,
            "source" => "Copy" . '/' . "paste list",
            'rowCount' => $rowCount,
            'tags' => isset($tag) ? $tag : [],
        ];
    }

    public function makeTextImportRequest($requestConfig, $data)
    {
        $client = new Client();
        try {
            $response = $client->post($this->senderBaseUrl . $requestConfig['method'], [
                'headers' => $this->getSenderHeaders(),
                'json' => [
                    'subscribers' => $data
                ],
            ]);

            return $responseData = json_decode($response->getBody()->getContents(), true);
        }catch (Exception $e){
            $this->logDebug($e->getMessage());
            exit();
        }
    }

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

    public function getSenderHeaders()
    {
        $headers = [
            'Authorization' => $this->prefixAuth . Configuration::get('SPM_API_KEY'),
            'Accept' => 'Application/json',
            'Content-type' => 'Application/json'
        ];

        return $headers;
    }

    public function startImport($data)
    {
        $method = 'subscribers/start_import';

        try {
            $client = new Client();
            $client->post($this->senderBaseUrl . $method, [
                'headers' => $this->getSenderHeaders(),
                'json' => $data,
            ]);

            $now = date("Y-m-d H:i:s");
            Configuration::updateValue('SPM_SENDERAPP_SYNC_LIST_DATE', $now);

            $this->logDebug('Completed import to Sender.net');
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