<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Se lanza cuando una operación rompe una regla de negocio (transición de
 * estado no permitida, borrar una categoría con productos, etc.).
 * El mensaje siempre está en español y es seguro mostrarlo al usuario:
 * el panel lo muestra como aviso y la API lo responderá con 422.
 */
class BusinessRuleException extends RuntimeException {}
