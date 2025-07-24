<?php
/**
 * 2010-2021 Sender.net
 *
 * Sender.net Api Client
 *
 * @author Sender.net <info@sender.net>
 * @copyright 2010-2021 Sender.net
 */

class SenderExport extends SenderApiClient
{
    public function export(array $shopData)
    {
        $storeId = Configuration::get('SPM_SENDERAPP_STORE_ID');
        $endPoint = "stores/".$storeId."/import_shop_data";

        $response = $this->makeExportCurlRequest($endPoint, $shopData);

        if (!$response['success']) {
            return [
                'success' => false,
                'message' => 'Unable to export shop data',
                'http_status' => $response['status'] ?? null,
                'error' => $response['curl_error'] ?? null,
                'raw_response' => $response['raw'] ?? null,
            ];
        }

        $now = date("Y-m-d H:i:s");
        Configuration::updateValue('SPM_SENDERAPP_SYNC_LIST_DATE', $now);

        $response = json_decode($response, true);
        $response['time'] = $now;

        return $response;
    }

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
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($status === 200 && $server_output !== false) {
            return [
                'success' => true,
                'status' => $status,
                'raw' => $server_output,
            ];
        }

        return [
            'success' => false,
            'status' => $status,
            'curl_error' => $curlError ?: null,
            'raw' => $server_output ?: null,
        ];
    }

}