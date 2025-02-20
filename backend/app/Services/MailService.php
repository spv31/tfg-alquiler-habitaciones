<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\OwnerRegistrationMail;
use App\Mail\TenantRegistrationMail;
use App\Mail\InvitationRegistrationMail;
use App\Models\User;
use App\Models\Invitation;

use Exception;

class MailService
{
	public function sendOwnerRegistrationMail(User $owner)
	{
		Mail::to($owner->email)->send(new OwnerRegistrationMail($owner));
	}

	public function sendTenantRegistrationMail(User $tenant)
	{
		Mail::to($tenant->email)->send(new TenantRegistrationMail($tenant));
	}

	public function sendInvitationRegistrationMail($recipient, Invitation $invitation)
	{
		Mail::to($recipient)->send(new InvitationRegistrationMail($recipient, $invitation));
	}
}
