<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoginResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('user_email')->nullable();
            $table->string('action');
            $table->string('module');
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();
        });
    }

    public function test_successful_login_returns_redirect_and_install_flags(): void
    {
        $user = User::create([
            'name' => 'Employe Menuiserie',
            'email' => 'employee@menuiserie.test',
            'password' => 'Employee12345',
            'role' => 'employee',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'employee@menuiserie.test',
            'password' => 'Employee12345',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('post_login_redirect', '/devis')
            ->assertJsonPath('allow_install_prompt', true)
            ->assertJsonStructure([
                'user',
                'access_token',
                'token_type',
                'post_login_redirect',
                'allow_install_prompt',
            ]);

        $this->assertNotNull($user->fresh()->last_login_at);
    }
}
