<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Session\Middleware\StartSession;
use App\Models\User;
use App\Services\UserService;
use App\Services\InvitationService;
use Mockery;

class AuthFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userMock;

    protected function setUp(): void
    {
        parent::setUp();

        // habilitamos la sesión para las rutas  de la API
        $this->app['router']->pushMiddlewareToGroup('api', StartSession::class);

        $this->userMock = Mockery::mock(UserService::class);
        $invitationMock = Mockery::mock(InvitationService::class)->shouldIgnoreMissing();

        $this->instance(UserService::class, $this->userMock);
        $this->instance(InvitationService::class, $invitationMock);
    }

    public function test_owner_can_register_successfully(): void
    {
        $payload = [
            'name' => 'Ana',
            'email' => 'ana' . uniqid() . '@example.com',
            'password' => 'secret123',
            'user_type' => 'individual',
            'identifier' => 'ID' . uniqid(),
            'phone_number' => '600123123',
            'address' => 'Calle Falsa 123',
        ];

        $fakeUser = User::factory()->owner()->make(['email' => $payload['email']]);

        $this->userMock
            ->shouldReceive('registerOwner')
            ->once()
            ->with(Mockery::subset($payload))
            ->andReturn(['status' => 'created', 'user' => $fakeUser]);

        $this->postJson('/api/register/owner', $payload)
            ->assertCreated()
            ->assertJsonPath('message', 'owner_registered')
            ->assertJsonPath('user.email', $payload['email']);
    }

    public function test_login_succeeds_with_correct_credentials(): void
    {
        $user = User::factory()->owner()->create([
            'email' => 'owner@site.test',
            'password' => Hash::make('mypassword'),
        ]);

        $this->withSession([])
            ->postJson('/api/login', [
                'email' => 'owner@site.test',
                'password' => 'mypassword',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'login_successful')
            ->assertJsonPath('user.id', $user->id);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->owner()->create([
            'email' => 'foo@bar.test',
            'password' => Hash::make('goodpass'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'foo@bar.test',
            'password' => 'badpass',
        ])
            ->assertStatus(401)
            ->assertJsonPath('error_key', 'invalid_credentials');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
