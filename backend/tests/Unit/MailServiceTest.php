<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Services\MailService;
use App\Mail\OwnerRegistrationMail;
use App\Mail\TenantRegistrationMail;
use App\Mail\InvitationRegistrationMail;
use App\Models\User;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class MailServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MailService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->service = new MailService;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sends_owner_registration_mail(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'identifier' => '12345678A',
            'user_type' => 'individual',
            'phone_number' => '600000000',
            'address' => 'C/ Falsa 1',
        ]);

        $this->service->sendOwnerRegistrationMail($owner);

        Mail::assertSent(
            OwnerRegistrationMail::class,
            fn($mail) => $mail->hasTo($owner->email)
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sends_tenant_registration_mail(): void
    {
        $tenant = User::factory()->create([
            'role' => 'tenant',
            'identifier' => '87654321B',
            'user_type' => 'individual',
            'phone_number' => '600000001',
            'address' => 'C/ Falsa 2',
        ]);

        $this->service->sendTenantRegistrationMail($tenant);

        Mail::assertSent(
            TenantRegistrationMail::class,
            fn($mail) => $mail->hasTo($tenant->email)
        );
    }

    #[Test]
    public function it_sends_invitation_mail(): void
    {
        $invitation = Invitation::factory()->create();
        $email = 'guest@example.net';

        $this->service->sendInvitationRegistrationMail($email, $invitation);

        Mail::assertSent(
            InvitationRegistrationMail::class,
            fn($mail) => $mail->hasTo($email)
        );
    }
}
