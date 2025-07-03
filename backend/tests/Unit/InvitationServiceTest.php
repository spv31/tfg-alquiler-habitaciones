<?php

namespace Tests\Unit;

use App\Mail\InvitationRegistrationMail;
use App\Models\Invitation;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\InvitationService;
use App\Services\MailService;
use App\Services\PropertyTenantService;
use Error;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvitationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InvitationService $service;
    protected $tenantServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->tenantServiceMock = Mockery::mock(PropertyTenantService::class);
        $this->tenantServiceMock->shouldIgnoreMissing();

        $this->service = new InvitationService($this->tenantServiceMock);

        $this->app->instance(MailService::class, new MailService);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_an_invitation_and_sends_mail(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'identifier' => '11111111A',
            'user_type' => 'individual',
            'phone_number' => '600111111',
            'address' => 'C/Falsa 3',
        ]);

        $property = Property::factory()->create(['status' => 'available']);

        $data = [
            'email' => 'guest@example.com',
            'property_id' => $property->id,
        ];

        $invitation = $this->service->createInvitation($owner, $data);

        $this->assertEquals('pending', $invitation->status);
        Mail::assertSent(
            InvitationRegistrationMail::class,
            fn($mail) => $mail->hasTo('guest@example.com')
        );
        $this->assertDatabaseHas('invitations', [
            'email' => 'guest@example.com',
            'status' => 'pending',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_if_rentable_unavailable(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'identifier' => '22222222B',
            'user_type' => 'individual',
            'phone_number' => '600222222',
            'address' => 'Dir'
        ]);
        $property = Property::factory()->create(['status' => 'unavailable']);

        $this->expectException(Error::class);

        $this->service->createInvitation($owner, [
            'email' => 'guest2@example.com',
            'property_id' => $property->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_if_duplicate_invitation_exists(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'identifier' => '33333333C',
            'user_type' => 'individual',
            'phone_number' => '600333333',
            'address' => 'Dir'
        ]);
        $property = Property::factory()->create(['status' => 'available']);

        Invitation::factory()->create([
            'email' => 'dup@example.com',
            'rentable_id' => $property->id,
            'rentable_type' => Property::class,
            'status' => 'pending',
        ]);

        $this->expectException(Error::class);

        $this->service->createInvitation($owner, [
            'email' => 'dup@example.com',
            'property_id' => $property->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_and_accepts_an_invitation(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'identifier' => '44444444D',
            'user_type' => 'individual',
            'phone_number' => '600444444',
            'address' => 'Dir'
        ]);
        $property = Property::factory()->create(['status' => 'available']);

        $invitation = Invitation::factory()->create([
            'email' => 'tenant@example.com',
            'token' => 'sometoken',
            'rentable_id'  => $property->id,
            'rentable_type' => Property::class,
            'owner_id' => $owner->id,
            'status' => 'pending',
        ]);

        // validate
        $found = $this->service->validateInvitation('tenant@example.com', 'sometoken');
        $this->assertTrue($invitation->is($found));

        $tenant = User::factory()->create([
            'role' => 'tenant',
            'identifier' => '55555555E',
            'user_type' => 'individual',
            'phone_number' => '600555555',
            'address' => 'Dir'
        ]);

        $this->tenantServiceMock
            ->shouldReceive('assignTenant')
            ->andReturnTrue();


        $this->service->acceptInvitation($invitation, $tenant);

        $this->assertEquals('accepted', $invitation->refresh()->status);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
