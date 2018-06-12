<?php

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

use WHMCS\Database\Capsule;

class CompropagoWebhook
{
    const MODULE_NAME = 'compropago';

    private $data;
    private $gparams;
    private $privateKey;
    private $publicKey;
    private $mode;
    private $admin;
    private $systemUrl;

    /**
     * CompropagoWebhook Constructor
     */
    public function __construct()
    {
        $this->data = @file_get_contents('php://input');
        $this->gparams = getGatewayVariables(self::MODULE_NAME);
        $this->systemUrl = $this->gparams['systemurl'];
        $this->admin = $this->gparams['admin'];
        $this->mode = $this->gparams['mode'];
        $this->publicKey = ($this->mode == "Live") ? $this->gparams['publickey_live'] : $this->gparams['publickey_test'];
        $this->privateKey = ($this->mode == "Live") ? $this->gparams['privatekey_live'] : $this->gparams['privatekey_test'];
    }

    /**
     * Main action of the webhook
     * @throws Exception
     */
    public function execute()
    {
        $this->validateRequest();

        if ($this->isTestMode($this->data->short_id)) {
            echo json_encode([
                "status"    => "success",
                "message"   => "OK - TEST -" . $this->data->type,
                "short_id"  => $this->data->short_id,
                "reference" => $this->data->order_info->order_id
            ]);
            return;
        }

        $data = $this->verifyOrder($this->data->id);

        switch ($data['api']) {
            case 1:
                $this->withApi1($data['body']);
                break;
            case 2: 
                $this->withApi2($data['body']);
                break;
        }
    }

    /**
     * Process webhook as API 1 call
     * @param array $body
     * @throws Exception
     */
    public function withApi1($body) 
    {
        $token      = $body->order_info->order_id;
        $token      = explode('-', $token);
        $invoiceId  = $token[0];
        $token      = $token[1];
        $status     = $body->type; 

        $data = [
            'short_id' => $body->short_id,
            'amount' => $body->order_info->exchange->origin_amount,
            'fee_whmcs' => 0
        ];

        $this->hashVerification($invoiceId, $token);
        $this->updateOrderStatus($status, $invoiceId, $body->id, $data);
    }

    /**
     * Process webhook as API 1 call
     * @param array $body
     * @throws Exception
     */
    public function withApi2($body) 
    {
        $token      = $body->product->id;
        $token      = explode('-', $token);
        $invoiceId  = $token[0];
        $token      = $token[1];

        $data = [
            'short_id' => $body->shortId,
            'amount' => $body->product->exchange->originAmount,
            'fee_whmcs' => 0
        ];

        $status = '';

        switch ($body->status) {
            case 'PENDING':
                $status = 'charge.pendig';
                break;
            case 'ACCEPTED':
                $status = 'charge.success';
                break;
            case 'EXPIRED':
                $status = 'charge.expired';
                break;
        }

        $this->hashVerification($invoiceId, $token);
        $this->updateOrderStatus($status, $invoiceId, $body->id, $data);
    }

    /**
     * Verify hash integrity of the request
     * @param string $invoiceId
     * @param string $token
     * @throws Exception
     */
    private function hashVerification($invoiceId, $token)
    {
        $hash = md5($invoiceId . $this->systemUrl . $this->publicKey);

        if ($token != $hash) {
            $message = 'Hash verification failure';
            throw new Exception($message, 409);
        }
    }

    /**
     * Validate empty request
     * @throws Exception
     */
    private function validateRequest()
    {
        if (empty($this->data)) {
            $message = 'Invalid request: empty value';
            throw new \Exception($message);
        }

        $this->data = json_decode($this->data);

        if (!isset($this->data->id) || empty($this->data->id)) {
            $message = 'Invalid request: empty value';
            throw new \Exception($message);
        }
    }

    /**
     * Validate if the webhook request is a test request
     * @param string $reference
     * @return bool
     */
    private function isTestMode($reference)
    {
        if ($reference == '000000') {
            return true;
        }
        return false;
    }

    /**
     * Verify order in ComproPago
     * @param string $orderId
     * @return array
     * @throws Exception
     */
    private function verifyOrder($orderId)
    {
        try {
            $response = $this->verifyWithApi1($orderId);
            return [
                "body" => $response,
                "api" => 1
            ];
        } catch (\Exception $e) {
            $response = $this->verifyWithApi2($orderId);
            return [
                "body" => $response,
                "api" => 2
            ];
        }
    }

    /**
     * Verify order using API v1
     * @param string $orderId
     * @return mixed
     * @throws Exception
     */
    private function verifyWithApi1($orderId)
    {
        $url = "https://api.compropago.com/v1/charges/$orderId/";
        $headers = [
            "authorization: Basic ".base64_encode($this->privateKey.':'.$this->publicKey),
            "cache-control: no-cache",
            "content-type: application/json",
        ];

        $response = $this->execCurl('GET', $url, null, $headers);

        $body = json_decode($response);

        if (isset($body->type) && $body->type == 'error') {
            throw new \Exception($body->message);
        }

        return $body;
    }

    /**
     * Verify order using API v2
     * @param string $orderId
     * @return mixed
     * @throws Exception
     */
    private function verifyWithApi2($orderId)
    {
        $url = "https://api.compropago.com/v2/orders/$orderId/";
        $headers = [
            "authorization: Basic ".base64_encode($this->privateKey.':'.$this->publicKey),
            "cache-control: no-cache",
            "content-type: application/json",
        ];

        $response = $this->execCurl('GET', $url, null, $headers);

        $body = json_decode($response);

        if (isset($body->status) && $body->status == 'error') {
            throw new \Exception($body->message);
        }

        return $body->data;
    }

    /**
     * Exec curl request
     * @param string $method
     * @param string $url
     * @param array $data
     * @param array $headers
     * @return string
     */
    private function execCurl($method, $url, $data, $headers)
    {
        $curl = curl_init();

        if (!empty($data)) {
            $data = json_encode($data);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS => "",
                CURLOPT_HTTPHEADER => $headers,
            ]
        );

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    private function updateOrderStatus($status, $invoiceId, $compropagoId, $data)
    {
        $verifyId  = checkCbInvoiceID($invoiceId, self::MODULE_NAME);
        $command    = 'GetOrders';
        $postData   = ['id' => $invoiceId];
        $results    = localAPI($command, $postData, $this->admin);
        checkCbTransID($compropagoId);

        switch ($status) {
            case 'charge.pending':
                # do nothing
                break;
            case 'charge.success':
                $transactionStatus = 'Success';
                logTransaction(self::MODULE_NAME, $_POST, $transactionStatus);

                if ($results["orders"]["order"][0]["paymentstatus"] != "Paid") {
                    addInvoicePayment(
                        $verifyId,
                        $data['short_id'],
                        $data['amount'],
                        $data['fee_whmcs'],
                        self::MODULE_NAME
                    );
                }
                break;
            case 'charge.expired':
                $transactionStatus = 'Failure';
                logTransaction(self::MODULE_NAME, $_POST, $transactionStatus);
                break;
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'OK - ' . $status,
            'short_id' => $data['short_id'],
            'reference' => $invoiceId
        ]);
        return;
    }
}

try {
    header('Content-Type: application/json');
    $webhook = new CompropagoWebhook();
    $webhook->execute();
} catch(\Exception $e) {
    die(json_encode([
        "status"     => "error",
        "message"    => $e->getMessage(),
        "short_id"   => null,
        "reference"  => null
    ]));
}
