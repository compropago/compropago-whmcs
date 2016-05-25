<?php
/**
 * Copyright 2015 Compropago.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
/**
 * Compropago WHMCS plugin
 * @author Eduardo Aguilar <eduardo.aguilar@compropago.com>
 */

require_once __DIR__ ."/../../includes/functions.php";
require_once __DIR__ ."/../vendor/autoload.php";

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use CompropagoViews\ChargeView;

/**
 * Funcion que despluega los campos de configuracion para el modulo de ComproPago
 *
 * @return array
 */
function compropago_config()
{
    $uri = explode("/",$_SERVER["REQUEST_URI"]);
    $aux = "";
    foreach($uri as $value){
        if($value == 'admin'){
            break;
        }else{
            $aux .= $value."/";
        }
    }

    return array(
        "FriendlyName" => array(
            "Type"         => "System",
            "Value"        =>"ComproPago (Oxxo, 7Eleven, Coppel, etc.)"
        ),
        "publickey_live" => array(
            "FriendlyName" => "Public Key - Live",
            "Type"         => "text",
            "Size"         => "30",
            "Description"  => "Esta llave esta disponible en <a href='https://www.compropago.com/panel/configuracion'>
                               https://www.compropago.com/panel/configuracion</a>.",
        ),
        "privatekey_live" => array(
            "FriendlyName" => "Private Key - Live",
            "Type"         => "text",
            "Size"         => "30",
            "Description"  => "Esta llave esta disponible en <a href='https://www.compropago.com/panel/configuracion'>
                               https://www.compropago.com/panel/configuracion</a>.",
        ),
        "publickey_test" => array(
            "FriendlyName" => "Public Key - Test",
            "Type"         => "text",
            "Size"         => "30",
            "Description"  => "Esta llave esta disponible en <a href='https://www.compropago.com/panel/configuracion'>
                               https://www.compropago.com/panel/configuracion</a>.",
        ),
        "privatekey_test" => array(
            "FriendlyName" => "Private Key - Test",
            "Type"         => "text",
            "Size"         => "30",
            "Description"  => "Esta llave esta disponible en <a href='https://www.compropago.com/panel/configuracion'>
                               https://www.compropago.com/panel/configuracion</a>.",
        ),
        "admin_user" => array(
            "FriendlyName" => "Admin User",
            "Type"         => "text",
            "Size"         => "30",
            "Description"  => "Usuario administrador del panel WHMCS, necesario para manejo de API interna",
        ),
        "mode" => array(
            "FriendlyName" => "Active Mode",
            "Type" => "radio",
            "Options" => "Live,Test",
            "Description" => "Seleccione si esta en modo activo o modo de pruebas.",
            "default" => "Live"
        ),
        "webhook" => array(
            "FriendlyName" => "Webhook",
            "Type"         => "textarea",
            "Descripcion"  => "Copie esta direccion y agreguela en el panel de compropago en la seccion
                               <a href='https://www.compropago.com/panel/webhooks'>Webhooks</a>",
            "Default"      => $_SERVER['SERVER_NAME'].$aux."modules/gateways/callback/compropago.php"
        ),
    );
}


/**
 * Ejecucion del proceso de pago
 *
 * @param $params
 * @return null
 */
function compropago_link($params)
{
    $aux = null;

    $file = explode("/",$_SERVER["REQUEST_URI"]);
    $file = $file[sizeof($file) - 1];

    $publickey = ($params['mode'] == "Live") ? $params['publickey_live'] : $params['publickey_test'];
    $privatekey = ($params['mode'] == "Live") ? $params['privatekey_live'] : $params['privatekey_test'];

    $hash = md5($params['invoiceid'] . $params['systemurl'] . $publickey);

    if(preg_match('/viewinvoice.php/',$file)){
        $data = array(
            "{{publickey}}"       => $publickey,
            "{{client_name}}"     => "WHMCS",
            "{{version}}"         => "1.1",
            "{{customer_name}}"   => $params['clientdetails']['firstname']." ".$params['clientdetails']['lastname'],
            "{{customer_email}}"  => $params['clientdetails']['email'],
            "{{order_price}}"     => $params['amount'],
            "{{order_id}}"        => $params['invoiceid'],
            "{{order_name}}"      => $hash,
            "{{success_url}}"     => $params['returnurl'],
            "{{failure_url}}"     => $params['returnurl'],
        );

        $aux = "<style>".ChargeView::getSourceStyle()."</style>";
        $aux .= ChargeView::getView('button', $data, 'source', 'html');
    }else{
        $aux = '<img src="https://media.licdn.com/media/p/5/005/02d/277/3e9dd1a.png"><br>';
    }

    return $aux;
}

