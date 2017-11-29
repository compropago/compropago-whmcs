<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

use WHMCS\Database\Capsule;

/**
 * Main function of the webhook
 *
 * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
 */
function init_webhook() {
    $gatewayModuleName = basename(__FILE__, '.php');
    $gatewayParams     = getGatewayVariables($gatewayModuleName);
    $systemUrl         = $gatewayParams['systemurl'];
    $admin             = $gatewayParams['admin_user'];
    $publickey         = ($gatewayParams['mode'] == "Live") ? $gatewayParams['publickey_live'] : $gatewayParams['publickey_test'];
    $privatekey        = ($gatewayParams['mode'] == "Live") ? $gatewayParams['privatekey_live'] : $gatewayParams['privatekey_test'];

    if (!$gatewayParams['type']) {
        die(json_encode([
            'status' => 'error',
            'message' => 'compropago is not active',
            'short_id' => null,
            'reference' => null
        ]));
    }

    $request = @file_get_contents('php://input');
    if (!$jsonObj = json_decode($request)) {
        die(json_encode([
            'status' => 'error',
            'message' => 'invalid request',
            'short_id' => null,
            'reference' => null
        ]));
    }

    if ($jsonObj->short_id == '000000') {
        die(json_encode([
            'status' => 'success',
            'message' => 'OK - test',
            'short_id' => $jsonObj->short_id,
            'reference' => null
        ]));
    }

    $response = verify_order($jsonObj->id, $publickey, $privatekey);

    $token      = $jsonObj->order_info->order_id;
    $token      = explode('-', $token);
    $invoiceId  = $token[0];
    $token      = $token[1];
    $amount     = $response->order_info->exchange->origin_amount;
    $feeWhmcs   = 0;
    $hash = md5($invoiceId . $systemUrl . $publickey);
    
    if ($hash != $token) {
        die(json_encode([
            'status' => 'error',
            'message' => 'Hash verification failure',
            'short_id' => $jsonObj->short_id,
            'reference' => null
        ]));
    }

    $invoiceId  = checkCbInvoiceID($invoiceId, $gatewayModuleName);
    $getId      = explode("-",$jsonObj->order_info->order_id);
    $command    = 'GetOrders';
    $postData   = ['id' => $getId[0]];
    $results    = localAPI($command, $postData, $admin);
    checkCbTransID($response->id);

    switch ($response->type) {
        case 'charge.success':
            $transactionStatus = 'Success';
            logTransaction($gatewayModuleName, $_POST, $transactionStatus);
            if($results["orders"]["order"][0]["paymentstatus"] != "Paid"){
                addInvoicePayment(
                    $invoiceId,
                    $response->short_id,
                    $amount,
                    $feeWhmcs,
                    $gatewayModuleName
                );
            };

            die(json_encode([
                'status' => 'success',
                'message' => 'OK - ' . $response->type,
                'short_id' => $response->short_id,
                'reference' => $invoiceId
            ]));
            break;

        case 'charge.pending':
            $transactionStatus = 'Failure';
            logTransaction($gatewayModuleName, $_POST, $transactionStatus);

            die(json_encode([
                'status' => 'success',
                'message' => 'OK - ' . $response->type,
                'short_id' => $response->short_id,
                'reference' => $invoiceId
            ]));
            break;

        case 'charge.expired':
            die(json_encode([
                'status' => 'success',
                'message' => 'OK - ' . $response->type,
                'short_id' => $response->short_id,
                'reference' => $invoiceId
            ]));

        default:
            die(json_encode([
                'status' => 'error',
                'message' => 'invalid webhook type',
                'short_id' => $response->short_id,
                'reference' => $invoiceId
            ]));
    }
}

/**
 * Call Verify order API
 *
 * @param $order_id
 * @param $publickkey
 * @param $privatekey
 * @return Object
 *
 * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
 */
function verify_order($order_id, $publickkey, $privatekey) {
    $curl = curl_init();

    curl_setopt_array(
        $curl,
        [
            CURLOPT_URL => "https://api.compropago.com/v1/charges/$order_id/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => [
                "authorization: Basic ".base64_encode($privatekey.':'.$publickkey),
                "cache-control: no-cache",
                "content-type: application/json",
            ],
        ]
    );

    $response = curl_exec($curl);
    curl_close($curl);

    return json_decode($response);
}

init_webhook();
