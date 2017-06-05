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
if (!defined("WHMCS")){
    die("This file cannot be accessed directly");
}
/**
 * Funcion que despluega los campos de configuracion para el modulo de ComproPago
 *
 * @return array
 */
function compropago_config(){
    global $CONFIG;
    $uri = explode("/",$_SERVER["REQUEST_URI"]);
    return array(
        "FriendlyName" => array(
            "Type"          => "System",
            "Value"         =>"ComproPago (OXXO, 7-Eleven, Coppel, etc.)"
        ),
        "publickey_live" => array(
            "FriendlyName"  => "Public Key - Live",
            "Type"          => "text",
            "Size"          => "30",
            "Description"   => "Esta llave está disponible en <a href='https://www.compropago.com/panel/configuracion' target='_blank'>https://www.compropago.com/panel/configuracion</a>.",
        ),
        "privatekey_live" => array(
            "FriendlyName"  => "Private Key - Live",
            "Type"          => "text",
            "Size"          => "30",
            "Description"   => "Esta llave está disponible en <a href='https://www.compropago.com/panel/configuracion' target='_blank'>https://www.compropago.com/panel/configuracion</a>.",
        ),
        "publickey_test" => array(
            "FriendlyName"  => "Public Key - Test",
            "Type"          => "text",
            "Size"          => "30",
            "Description"   => "Esta llave está disponible en <a href='https://www.compropago.com/panel/configuracion' target='_blank'>https://www.compropago.com/panel/configuracion</a>.",
        ),
        "privatekey_test" => array(
            "FriendlyName"  => "Private Key - Test",
            "Type"          => "text",
            "Size"          => "30",
            "Description"   => "Esta llave está disponible en <a href='https://www.compropago.com/panel/configuracion' target='_blank'>https://www.compropago.com/panel/configuracion</a>.",
        ),
        "admin_user" => array(
            "FriendlyName"  => "Admin User",
            "Type"          => "text",
            "Size"          => "30",
            "Description"   => "Usuario administrador del panel WHMCS, necesario para manejo de API interna.",
        ),
        "mode" => array(
            "FriendlyName"  => "Active Mode",
            "Type"          => "radio",
            "Options"       => "Live,Test",
            "Description"   => "Seleccione si está en modo activo o modo de pruebas.",
            "default"       => "Live"
        ),
        "webhook" => array(
            "FriendlyName"  => "Webhook",
            "Type"          => "textarea",
            "Descripcion"   => "Copie esta dirección y agréguela en el panel de ComproPago en la sección <a href='https://www.compropago.com/panel/webhooks' target='_blank'>Webhooks</a>.",
            "Default"       => $CONFIG['SystemURL']."/modules/gateways/callback/compropago.php"
        ),
    );
}
function render_button($data){
    $button = file_get_contents(__DIR__ . '/../cpvendor/button.html');
    foreach ($data as $key => $value) {
        $button = str_replace($key, $value, $button);
    }
    return $button;
}
/**
 * Ejecucion del proceso de pago
 *
 * @param $params
 * @return null
 */
function compropago_link($params) {
    global $CONFIG;
    /**
     * Recuperación del template del formulario
     *
     * @param $data
     * @return mixed|string
     */
    $aux = null;
    $file = explode("/",$_SERVER["REQUEST_URI"]);
    $file = $file[sizeof($file) - 1];
    $publickey = ($params['mode'] == "Live") ? $params['publickey_live'] : $params['publickey_test'];
    $privatekey = ($params['mode'] == "Live") ? $params['privatekey_live'] : $params['privatekey_test'];
    $hash = md5($params['invoiceid'] . $params['systemurl'] . $publickey);
    if(preg_match('/viewinvoice.php/',$file)){
        $data = array(
            "{{publickey}}"         => $publickey,
            "{{order_id}}"          => $params['invoiceid'].'-'.$hash,
            "{{order_name}}"        => $params['description'],
            "{{order_price}}"       => $params['amount'],
            "{{customer_name}}"     => $params['clientdetails']['firstname']." ".$params['clientdetails']['lastname'],
            "{{customer_email}}"    => $params['clientdetails']['email'],
            "{{currency}}"          => $params['currency'],
            "{{success_url}}"       => $params['returnurl'],
            "{{failure_url}}"       => $params['returnurl'],
            "{{client_name}}"       => "WHMCS",
            "{{version}}"           => $CONFIG['Version']
        );
        $aux = render_button($data);
    }else{
        $aux = '<img src="https://media.licdn.com/media/p/5/005/02d/277/3e9dd1a.png"><br>';
    }
    return $aux;
}