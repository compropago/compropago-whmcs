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
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';


$gatewayModuleName = basename(__FILE__, '.php');
$gatewayParams = getGatewayVariables($gatewayModuleName);
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}


$request = @file_get_contents('php://input');
$jsonObj = json_decode($request);
if(!$jsonObj = json_decode($request)){
    die('Tipo de Request no Valido');
}


if($jsonObj->id=="ch_00000-000-0000-000000" || $jsonObj->short_id =="000000"){
    die("Probando el WebHook?, <b>Ruta correcta.</b>");
}

$invoiceId = $jsonObj->order_info->order_id;
$systemUrl = $gatewayParams['systemurl'];
$amount    = $jsonObj->order_info->order_price;
$orderFee  = $jsonObj->fee;

$admin     = $gatewayParams['admin_user'];

$publickey = ($gatewayParams['mode'] == "Live") ? $gatewayParams['publickey_live'] : $gatewayParams['publickey_test'];
$privatekey= ($gatewayParams['mode'] == "Live") ? $gatewayParams['privatekey_live'] : $gatewayParams['privatekey_test'];

$hash = md5($invoiceId . $systemUrl . $publickey);

if($hash != $jsonObj->order_info->order_name){
    die('Hash Verification Failure');
}


$invoiceId = checkCbInvoiceID($invoiceId,$gatewayModuleName);
checkCbTransID($jsonObj->id);


if($jsonObj->type == 'charge.success'){
    $action              = 'acceptorder';
    $values['orderid']   = $invoiceId;
    $values['sendemail'] = false;
    $values["autosetup"] = true;

    $response = localAPI($action, $values, $admin);

    if($response['result'] == 'error'){
        die($response['message']);
    }

    addInvoicePayment($invoiceId,$jsonObj->id,$amount,$orderFee,$gatewayModuleName);
}