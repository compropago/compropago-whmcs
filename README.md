Plugin para WHMCS - ComproPago
===================================
## Descripción
Este modulo provee el servicio de ComproPago para poder generar intenciones de pago dentro de la plataforma WHMCS. 

Con ComproPago puede recibir pagos en OXXO, 7Eleven y muchas tiendas más en todo México.

[Registrarse en ComproPago ] (https://compropago.com)


## Ayuda y Soporte de ComproPago

- [Centro de ayuda y soporte](https://compropago.com/ayuda-y-soporte)
- [Solicitar Integración](https://compropago.com/integracion)
- [Guía para Empezar a usar ComproPago](https://compropago.com/ayuda-y-soporte/como-comenzar-a-usar-compropago)
- [Información de Contacto](https://compropago.com/contacto)

## Requerimientos
* [WHMCS 6.3.x] (https://download.whmcs.com/)
* ComproPago Views Sdk 1.0.0
* [PHP >= 5.5](http://www.php.net/)
* [PHP JSON extension](http://php.net/manual/en/book.json.php)
* [PHP cURL extension](http://php.net/manual/en/book.curl.php)

## Instalación:

1. Subir el contenido de la carpeta **modules** del plugin en en la carpeta modules de su sitio WHMCS manteniendo la estructura de carpetas.
2. Registrar como nuevo metodo de pago en su panel WHMCS


## ¿Cómo trabaja el modulo?
Una vez que el cliente sabe que comprar y continua con el proceso de compra entrará a la opción de elegir metodo de pago
justo aqui aparece la opción de pagar con ComproPago.

Posteriormente aparecera una pantalla con el logo de ComproPago de la cual sera redireccionado hacio el recibo
proporcionado por WHMCS. Dentro de este recibo bajo la leyenda **Unpaid** aparecera el boton **Pagar en efectivo**,
en el cual sus clientes podran dar click para seleccionar la tienda en la cual desea realizar su pago, esto le mostrara un recibo propio de compropago con las instrucciones restantes para poder realizar el pago de su compra.

Los webhooks de compropago se encargaran de notificarle a su tienda cuando el pago de su cliente sea aprobado. y modificaran el estatus de la orden.

---

## Configurar el plugin

1. Navegar hacia: WooCommerce -> Settings -> Payment Gateways, elegir ComproPago llenar los campos Public_key and Private_key.

---

## Sincronización con la notificación Webhook
1. Ir al área de Webhooks en ComproPago https://compropago.com/panel/webhooks
2. Introducir la dirección que aparece en el campo **webhook** de la configuracion del plugin
3. Dar click en el botón "Probar" y verificamos que el servidor de la tienda esta respondiendo, debera aparecer el mismo objeto que se envío. 

Una vez completados estos pasos el proceso de instalación queda completado.

## Documentación
### Documentación ComproPago Plugin WooCommerce

### Documentación de ComproPago
**[API de ComproPago] (https://compropago.com/documentacion/api)**

ComproPago te ofrece un API tipo REST para integrar pagos en efectivo en tu comercio electrónico o tus aplicaciones.


**[General] (https://compropago.com/documentacion)**

Información de Comisiones y Horarios, como Transferir tu dinero y la Seguridad que proporciona ComproPAgo


**[Herramientas] (https://compropago.com/documentacion/boton-pago)**
* Botón de pago
* Modo de pruebas/activo
* WebHooks
* Librerías y Plugins
* Shopify
