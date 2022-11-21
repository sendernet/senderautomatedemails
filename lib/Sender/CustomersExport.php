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

    public function export(array $customers)
    {
        $storeId = Configuration::get('SPM_SENDERAPP_STORE_ID');
        $endPoint = "stores/".$storeId."/import_shop_data";

        if (!$response = $this->makeExportCurlRequest($endPoint, ['customers' => $customers])){
            return [
                'success' => false,
                'message' => 'Unable to export customers',
            ];
        }

        $now = date("Y-m-d H:i:s");
        Configuration::updateValue('SPM_SENDERAPP_SYNC_LIST_DATE', $now);

        $response = json_decode($response, true);
        $response['time'] = $now;

        return $response;
    }

    /**
     * @param string $endpoint
     * @param $data
     * @return bool|mixed|string
     */
    private function makeExportCurlRequest($endpoint, $data)
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

        curl_setopt($ch, CURLOPT_URL, $this->senderBaseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formedData);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $status === 200 ? $server_output : false;
    }

}