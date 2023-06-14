<?php

class TestIntegration
{
    private array $postFields = [];
    private CustomLogger $logger;
    private DB $db;

    private const BROKER_URL = 'https://crm.belmar.pro/api/v1/';
    private const TOKEN = 'ba67df6a-a17c-476f-8e95-bcdb75ed3958';

    public function __construct(CustomLogger $logger, DB $db)
    {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function sendLead($data): array
    {
        $this->setPostFields($data);

        $this->logger->writeLog('Debug', 'Send lead - ' . json_encode($this->postFields, JSON_UNESCAPED_UNICODE));

        $this->db->insert("leads", [
            'email' => $this->postFields['email'], 'first_name' => $this->postFields['firstName'],
            'last_name' => $this->postFields['lastName'], 'phone' => $this->postFields['phone'],
            'password' => $this->postFields['password'], 'status' => 'New', 'ftd' => 0, 'landing_url' => $this->postFields['landingUrl'],
            'country_code' => $this->postFields['countryCode'], 'language' => $this->postFields['language'],
            'ip' => $this->postFields['ip']
        ]);

        return $this->sendLeadToBroker();
    }

    protected function sendLeadToBroker(): array
    {
        $ch = curl_init(self::BROKER_URL . 'addlead');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->postFields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'token:' . self::TOKEN,
        ]);

        $result = curl_exec($ch);
        $curlInfo = curl_getinfo($ch);
        $errorCURL = curl_errno($ch);

        $this->logger->writeLog('Debug', 'Curl info - ' . json_encode($curlInfo, JSON_UNESCAPED_UNICODE));
        $this->logger->writeLog('Debug', 'Curl error - ' . $errorCURL);
        $this->logger->writeLog('Debug', 'Result - ' . $result);

        $result = json_decode($result, true);

        curl_close($ch);

        $response['Broker'] = 'TestIntegration';

        if ($errorCURL) {
            $response['Status'] = 'Error';
            if ($errorCURL == 28) {
                $response['Error'] = 'TIME_OUT';
            } else {
                $response['Error'] = 'CURL_ERROR ' . $errorCURL;
            }
        } else {

            if (isset($result['status']) && $result['status'] === true) {
                $response['Status'] = 'Success';

                if (isset($result['autologin'])) {
                    $response['Link'] = $result['autologin'];
                }

                if (isset($result['id'])) {
                    $response['Id'] = $result['id'];
                }
            } else {
                $response['Status'] = 'Error';
                if (isset($result['error'])) {
                    $response['Error'] = $result['error'];
                } else {
                    $response['Error'] = 'Error';
                }
            }
        }

        $this->logger->writeLog('Response', $response);

        return $response;
    }

    private function setPostFields($data)
    {
        $phone = mb_substr($data['phone'], 2);
        $code = substr($data['phone'], 0, 2);

        $this->postFields['firstName'] = htmlentities($data['first_name']);
        $this->postFields['lastName'] = htmlentities($data['last_name']);
        $this->postFields['phone'] = htmlentities($data['phone']);
        $this->postFields['email'] = htmlentities($data['email']);
        $this->postFields['countryCode'] = 'RU';
        $this->postFields['box_id'] = 28;
        $this->postFields['offer_id'] = 3;
        $this->postFields['landingUrl'] = 'http://testintegration/';
        $this->postFields['password'] = 'qwerty12';
        $this->postFields['language'] = 'ru';
        $this->postFields['ip'] = '102.38.250.0';

    }

    public function getLeadStatuses()
    {
        $oneMonthAgo = date("Y-m-d", strtotime("-1 month", strtotime(date("Y-m-d"))));
        $monthAddDay = date("Y-m-d", strtotime("+1 day", strtotime(date("Y-m-d"))));

        $data = [
            'date_from' => $oneMonthAgo,
            'date_to' => $monthAddDay,
            'page' => 0,
            'limit' => 500
        ];

        $this->logger->writeLog('Status Debug', 'getLeadStatuses' . json_encode($data, JSON_UNESCAPED_UNICODE));

        $ch = curl_init(self::BROKER_URL . 'getstatuses');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'token:' . self::TOKEN,
        ]);

        $result = curl_exec($ch);
        $curlInfo = curl_getinfo($ch);
        $errorCURL = curl_errno($ch);

        $this->logger->writeLog('Status Debug', 'Curl info - ' . $curlInfo);
        $this->logger->writeLog('Status Debug', 'Curl error - ' . $errorCURL);
        $this->logger->writeLog('Status Debug', 'Result - ' . $result);

        $result = json_decode($result, true);

        curl_close($ch);

        return $result['data'];
    }

    private function searchItemInArray($id, $array)
    {
        foreach ($array as $val) {
            if ($val['email'] == $id) {
                return $val;
            }
        }
        return null;
    }

    public function updateStatuses()
    {
        $result = $this->getLeadStatuses();

        if (!$result) {
            $this->logger->writeLog('Debug', '1');
            return false;
        }

        $leadsDB = $this->db->select('leads', 'id, email, status, ftd');

        foreach ($leadsDB as $leadDB) {
            $leadBroker = $this->searchItemInArray($leadDB['email'], $result);
            $this->logger->writeLog('Debug', '1.1' . json_encode($leadBroker));

            if (isset($leadBroker['status'])) {
                $this->logger->writeLog('Debug', '2');

                $emailBroker = strtolower($leadBroker['email']);
                $statusBroker = strtolower($leadBroker['status']);
                $ftdBroker = strtolower($leadBroker['ftd']);

                if (!empty($statusBroker)) {
                    $this->logger->writeLog('Debug', '3');

                    $lead = $this->db->update('leads', ['status' => $statusBroker, 'ftd' => $ftdBroker], "email = '$emailBroker'");

                    if (!$lead)
                        return false;
                }
            }
        }
    }
}