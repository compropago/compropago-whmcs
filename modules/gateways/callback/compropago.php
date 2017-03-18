<?php
/**
 * WHMCS Sample Payment Callback File
 *
 * This sample file demonstrates how a payment gateway callback should be
 * handled within WHMCS.
 *
 * It demonstrates verifying that the payment gateway module is active,
 * validating an Invoice ID, checking for the existence of a Transaction ID,
 * Logging the Transaction for debugging and Adding Payment to an Invoice.
 *
 * For more information, please refer to the online documentation.
 *
 * @see http://docs.whmcs.com/Gateway_Module_Developer_Docs
 *
 * @copyright Copyright (c) WHMCS Limited 2015
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */
// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

use WHMCS\Database\Capsule;

function init_webhook() {

    /**
     * Validacion del modulo
     */
    $gatewayModuleName = basename(__FILE__, '.php');
    $gatewayParams = getGatewayVariables($gatewayModuleName);
    $systemUrl = $gatewayParams['systemurl'];
    $admin     = $gatewayParams['admin_user'];
    $publickey = ($gatewayParams['mode'] == "Live") ? $gatewayParams['publickey_live'] : $gatewayParams['publickey_test'];
    $privatekey= ($gatewayParams['mode'] == "Live") ? $gatewayParams['privatekey_live'] : $gatewayParams['privatekey_test'];
    if (!$gatewayParams['type']) {
        die("Module Not Activated");
    }
    /**
     * Obtencion de la peticion
     */
    $request = @file_get_contents('php://input');
    if (!$jsonObj = json_decode($request)) {
        die('Tipo de Request no Valido');
    }
    /**
     * Validacion de peticiones de prueba
     */
    if ($jsonObj->id=="ch_00000-000-0000-000000") {
        die("Probando el WebHook?, Ruta correcta.");
    }
    /**
     * Verificando orden en el servidor
     */
    $response = verify_order($jsonObj->id, $publickey, $privatekey);

    /**
     * Get data
     */
    $token = $jsonObj->order_info->order_id;
    $token = explode('-', $token);
    $invoiceId = $token[0];
    $token = $token[1];
    $amount    = $response->order_info->order_price;
    $orderFee  = $response->fee;
    $hash = md5($invoiceId . $systemUrl . $publickey);
    /**
     * Validar si el pago corresponde a la tienda
     */
    if ($hash != $token) {
        die('Hash Verification Failure');
    }
    $invoiceId = checkCbInvoiceID($invoiceId, $gatewayModuleName);
    checkCbTransID($response->id);

    switch ($response->type) {
      case 'charge.success':
      $transactionStatus = 'Success';
      logTransaction($gatewayModuleName, $_POST, $transactionStatus);
      addInvoicePayment(
        $invoiceId,
        $response->short_id,
        $amount,
        $orderFee,
        $gatewayModuleName
    );

    echo "Changed ID: {$invoiceId} to Paid Status. Folio: {$response->short_id}.";
        break;
      case 'charge.pending':
      $transactionStatus = 'Failure';
      logTransaction($gatewayModuleName, $_POST, $transactionStatus);

      echo "Status: {$response->type} of ID: {$invoiceId}. Folio: {$response->short_id}.";
      break;
      default:
      echo "Status: {$response->type} of ID: {$otherID}. Folio: {$response->short_id}.";
        break;
    }
}

function verify_order ($order_id, $publickkey, $privatekey) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.compropago.com/v1/charges/$order_id/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
            "authorization: Basic ".base64_encode($privatekey.':'.$publickkey),
            "cache-control: no-cache",
            "content-type: application/json",
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response);
}
/**
 * Inicializa el proceso
 */
init_webhook();
