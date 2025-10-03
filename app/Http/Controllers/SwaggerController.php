<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Prueba tecnica - API de Transacciones",
 *      description="Documentación de ejemplo para Laravel 12 con Swagger",
 *      @OA\Contact(
 *          email="soporte@midominio.com"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Servidor de pruebas"
 * )
 */
class SwaggerController extends Controller
{
    // Este controlador no necesita métodos, solo sirve para las anotaciones globales
}
