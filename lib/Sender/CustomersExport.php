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
 * Class CustomersExport
 */
class CustomersExport extends SenderApiClient
{
    public function textImport($customers, $columns)
    {
        $requestConfig = [
            "http" => 'post',
            "method" => "subscribers/text_import",
        ];

        $data = ['subscribers' => $customers];
        $textImport = json_decode($this->makeExportCurlRequest($requestConfig, $data));
        if (!$textImport){
            return $data = [
                'success' => false,
                'message' => 'Unable to export customers',
            ];
        }

        $columnsFormed = $this->prepareStartImportColumns($columns);
        $dataStartImport = $this->formStartImportData($columnsFormed, $textImport->fileName , $textImport->rowCount);

        $requestConfigStartImport = [
            'http' => 'post',
            'method' => 'subscribers/start_import'
        ];

        if (!$this->makeExportCurlRequest($requestConfigStartImport, $dataStartImport)){
            return $data = [
                'success' => false,
                'message' => 'Unable to export customers',
            ];
        }

        $now = date("Y-m-d H:i:s");
        Configuration::updateValue('SPM_SENDERAPP_SYNC_LIST_DATE', $now);

        return $data = [
            'success' => true,
            'message' => $now,
        ];
    }

    public function formStartImportData($columns, $fileName, $rowCount)
    {
        $tagId = Configuration::get('SPM_SENDERAPP_SYNC_LIST_ID');

        if ($tagId != 0) {
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

    /**
     * @param $requestConfig
     * @param $data
     * @return bool|mixed|string
     */
    private function makeExportCurlRequest($requestConfig, $data)
    {
        #Forming data for curl request
        $formedData = json_encode($data);

        #Init curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: ' . $this->prefixAuth . Configuration::get('SPM_API_KEY'),
            'Accept: Application/json',
            'Content-type: Application/json'
        ));

        curl_setopt($ch, CURLOPT_URL, $this->senderBaseUrl . $requestConfig['method']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formedData);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($status === 200){
            curl_close($ch);
            return $server_output;
        }else{
            curl_close($ch);
            return false;
        }
    }

}