<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Services\UserService;
use App\Services\InvitationService;
use App\Services\UploadFilesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Stripe\StripeClient;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $service;
    protected $stripeMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake dependencies
        $invitationMock = Mockery::mock(InvitationService::class);
        $uploadFilesMock = Mockery::mock(UploadFilesService::class);

        $accountsMock = Mockery::mock();
        $accountsMock->shouldReceive('create')
            ->andReturn((object)['id' => 'acct_test_123']);

        $this->stripeMock = Mockery::mock(StripeClient::class);
        $this->stripeMock->accounts = $accountsMock;

        $this->service = new UserService(
            $invitationMock,
            $uploadFilesMock,
            $this->stripeMock
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ensure_owner_stripe_account_creates_a_new_account_if_absent(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'stripe_account_id' => null,
            'identifier' => '12345678A',
            'user_type' => 'individual',
            'phone_number' => '600000001',
            'address' => 'C/Falsa 123',
        ]);

        $accountId = $this->service->ensureOwnerStripeAccount($owner);

        $this->assertEquals('acct_test_123', $accountId);
        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'stripe_account_id' => 'acct_test_123',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ensure_owner_stripe_account_returns_existing_id(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'stripe_account_id' => 'acct_existing',
            'identifier' => '87654321B',
            'user_type' => 'individual',
            'phone_number' => '600000002',
            'address' => 'C/Falsa 124',
        ]);

        $accountId = $this->service->ensureOwnerStripeAccount($owner);

        $this->assertEquals('acct_existing', $accountId);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ensure_tenant_stripe_customer_creates_customer_if_missing(): void
    {
        $customersMock = Mockery::mock();
        $customersMock->shouldReceive('create')
            ->once()
            ->andReturn((object)['id' => 'cus_test_789']);

        $this->stripeMock->customers = $customersMock;

        $tenant = User::factory()->create([
            'role' => 'tenant',
            'stripe_customer_id' => null,
            'identifier' => '23456789C',
            'user_type' => 'individual',
            'phone_number' => '600000003',
            'address' => 'C/Falsa 125',
        ]);

        $customerId = $this->service->ensureTenantStripeCustomer($tenant);

        $this->assertEquals('cus_test_789', $customerId);
        $this->assertDatabaseHas('users', [
            'id' => $tenant->id,
            'stripe_customer_id' => 'cus_test_789',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function find_user_by_email_returns_correct_user(): void
    {
        $user = User::factory()->create([
            'email' => 'unique@mail.com',
            'identifier' => '34567890D',
            'user_type' => 'individual',
            'phone_number' => '600000004',
            'address' => 'C/Falsa 126',
        ]);

        $found = $this->service->findUserByEmail('unique@mail.com');

        $this->assertTrue($user->is($found));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
