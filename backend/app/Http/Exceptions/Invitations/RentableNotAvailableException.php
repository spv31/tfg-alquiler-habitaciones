<?php

namespace App\Invitations\Exceptions;

use Exception;

class RentableNotAvailableException extends Exception
{
    protected $code = 1001; // Código único
    protected $message = 'El rentable no está disponible para invitaciones.';
}
