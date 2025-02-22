<?php

namespace App\Invitations\Exceptions;

use Exception;

class InvitationAlreadyExistsException extends Exception
{
    protected $code = 1002;
    protected $message = 'Ya existe una invitación pendiente para este usuario en este alquiler.';
}