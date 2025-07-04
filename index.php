<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

// Iniciar sesión antes de cualquier otra cosa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configura la carpeta de vistas si no está configurada
Flight::set('views', __DIR__ . '/views');// Ajusta el camino si es necesario


// Obtener la URL solicitada
$request_uri = $_SERVER['REQUEST_URI'];

require 'app/helpers.php';

// Cargar rutas de la aplicación
require 'app/routes.php';

// Iniciar FlightPHP
Flight::start();
