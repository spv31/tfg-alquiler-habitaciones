<?php

namespace App\Exceptions;

use Exception;

class InvalidRentableTypeException extends Exception
{
	protected $code = 1003;
	protected $message = 'El tipo de alquiler no es compatible con esta operación.';
}
