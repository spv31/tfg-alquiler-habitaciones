<?php

namespace App\Invitations\Exceptions;

use Exception;

class RentableNotAvailableException extends Exception
{
    protected $code = 1002; 
    protected $message = 'El alquiler no está disponible para invitaciones.';
}
