# ComproPago, Views SDK 1.0.0

## Descripción
Libreria que le permite manejar los diferentes componentes graficos proporcionados por ComproPago.

## Ayuda y Soporte de ComproPago
- [Centro de ayuda y soporte](https://compropago.com/ayuda-y-soporte)
- [Solicitar Integración](https://compropago.com/integracion)
- [Guía para Empezar a usar ComproPago](https://compropago.com/ayuda-y-soporte/como-comenzar-a-usar-compropago)
- [Información de Contacto](https://compropago.com/contacto)

## Requerimientos
* [PHP >= 5.5](http://www.php.net/)
* [PHP JSON extension](http://php.net/manual/en/book.json.php)
* [PHP cURL extension](http://php.net/manual/en/book.curl.php)

## Instalacion de ComproPago Views SDK

### Instalación usando Composer
La manera recomenda de instalar la SDK de ComproPago es por medio de [Composer](http://getcomposer.org).
- [Como instalar Composer?](https://getcomposer.org/doc/00-intro.md)

Para instalar la última versión **Estable de Views SDK**, ejecuta el comando de Composer:

```bash
composer require compropago/views-sdk
```

Posteriormente o en caso de erro de carga de archivos, volvemos a crear el autoload:

```bash
composer dumpautoload -o
```

O agregando manualmente al archivo composer.json

```bash
"require": {
		"compropago/views-sdk":"^1.0"
	}
```

Y ejecutamos la instalacion de composer

```bash
composer install
```

Después de la instalación para poder hacer uso de la librería es **necesario incluir** el autoloader de Composer:

```php
require 'vendor/autoload.php';
```

## Guía básica de Uso
Se debe contar con una cuenta activa de ComproPago. [Registrarse en ComproPago](https://compropago.com)

### General

Para poder hacer uso de la librería es necesario incluir el autoloader
```php
require 'vendor/autoload.php';
```

El Namespace a utilizar dentro de la librería es **CompropagoViews**.
```php
use CompropagoViews\ChargeViews; // Clase para la carga de las vistas disponibles
```

Para el correcto despligue de las vistas es necesario incluir el archivo CSS que contiene los estilos necesarios
```html
<link rel="stylesheet" href="vendor/compropago/views-sdk/Views/Assests/css/cpstyle.css">
```

### Herramientas disponibles en Views SDK
* [Prueba de carga](#prueba-de-carga)
* [Seleccion de proveedores](#seleccion-de-proveedores)
* [Recivos de ordenes de compra](#recibos-de-ordenes-de-compra)
* [Botones dinamicos de pago](#botones-dinamicos-de-pago)

#### Prueba de carga
Para generar pruebas en la clase de carga y verificar su funcionamiento puede utilizar la vista **raw** de la siguiente
forma
```php
// Variable con informacion de prueba para la vista raw puede ser arreglo o un objeto cualquiera
$dataView = array(
    // Informacion de prueba
);

ChargeViews::getView('raw', $dataView);
```

#### Seleccion de proveedores
La vista **providers** proprociona un grupo de **input[type=radio name=compropagoProvider]** con los
logos de cada tienda, o en tambien un **select[name=compropagoProvider]** con el listado de proveedores
proprocionado en el arreglo de configuracion de la vista. Su forma de uso es la siguiente.
```php
// Arreglo de configuracion
$dataView = array(
    // Descripcion del servicio
    'description' => "ComproPago - Pagos en efectivo",
    // Instrucciones para la seleccion
    'instructions' => "Seleccione la tienda donde desee realizar el pago",
    // si deseas el select cambia el valor a 'no'
    'showLogo' => 'yes',
     // Arreglo de objetos con la informacion de los proveedores obtinido del metodo getProviders disponible en el paquete 'compropago/php-sdk' de composer
    'providers' => $providers
);

ChargeViews::getView('providers', $dataView);
```

#### Recivos de ordenes de compra
La vista **receipt** proporciona el despliegue de recivos de compra de ComproPago. Su forma de uso es la siguiente.
```php
// Id de la orden de compropago
$orderId = 'ch_0991dd38-e408-4f27';

ChargeViews::getView('receipt', $orderId);
```

#### Botones dinamicos de pago
La vista **button** le permite crear botones de pago dinamicamente (no recomendable para compras en cantidades mayores
a una unidad). Su forma de uso es la siguiente.
```php
$dataView = array(
    'publickey'      = $publickey;                       // Llave publica disponible en el panel de compropago
    'customer_name'  = "Eduardo Aguilar";                // Nombre del cliente (opcional)
    'customer_email' = "eduardo.aguilar@compropago.com"; // correo del cliente (opcional)
    'order_price'    = "123.456";                        // Monto de la compra
    'order_id'       = "23";                             // Numero de orden
    'order_name'     = "MacBook Pro";                    // Nombre del producto
);
ChargeViews::getView('button', $dataView);
```

## Guia de versiones

| Version | Status | Packagist            | Namespace       | PHP     |
|---------|--------|----------------------|-----------------|---------|
| 1.0.0   | stable | compropago/views-sdk | CompropagoViews | \>= 5.5 |