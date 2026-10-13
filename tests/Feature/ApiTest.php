<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Token dibuat langsung (tanpa request login) supaya tidak ada
     * cookie session yang terbawa antar-request di dalam satu test.
     * Di production frontend tidak mengirim cookie sama sekali,
     * jadi Bearer token selalu yang dipakai.
     */
    private function tokenFor(User $user): array
    {
        $token = $user->createToken('frontend')->plainTextToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_info_root_returns_api_index(): void
    {
        $this->get('/')->assertStatus(200)->assertJsonPath('mode', 'api-only');
    }

    public function test_public_can_list_questions(): void
    {
        Question::factory()->create(['q' => 'Ibukota Indonesia?', 'a' => 'Jakarta', 'b' => 'Bandung', 'ans' => 0]);

        $this->getJson('/api/questions')
            ->assertOk()
            ->assertJsonFragment(['q' => 'Ibukota Indonesia?']);
    }

    public function test_guest_cannot_create_question(): void
    {
        $this->postJson('/api/questions', ['q' => 'x', 'a' => 'a', 'b' => 'b', 'ans' => 0])
            ->assertUnauthorized();
    }

    public function test_login_issues_token(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);

        $this->postJson('/api/login', [
            'email' => $user->email, 'password' => 'rahasia123',
        ])->assertOk()->assertJsonStructure(['token', 'user']);
    }

    public function test_login_rejects_wrong_password(): void
    {
        $user = User::factory()->create(['password' => 'benar123']);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'salah'])
            ->assertUnauthorized();
    }

    public function test_me_requires_valid_token(): void
    {
        $user = User::factory()->create();

        $this->getJson('/api/me')->assertUnauthorized();
        $this->getJson('/api/me', $this->tokenFor($user))
            ->assertOk()
            ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_token_can_crud_questions(): void
    {
        $user = User::factory()->create();
        $headers = $this->tokenFor($user);

        $id = $this->postJson('/api/questions', [
            'q' => '2+2?', 'a' => '4', 'b' => '5', 'ans' => 0, 'ex' => 'Dasar.',
        ], $headers)->assertCreated()->json('id');

        $this->putJson("/api/questions/{$id}", [
            'q' => '2+2?', 'a' => '4', 'b' => '22', 'ans' => 0, 'ex' => 'Dasar.',
        ], $headers)->assertOk()->assertJsonFragment(['b' => '22']);

        $this->postJson('/api/questions', ['q' => 'x'], $headers)->assertStatus(422);

        $this->deleteJson("/api/questions/{$id}", [], $headers)->assertOk();
        $this->assertDatabaseMissing('questions', ['id' => $id]);
    }

    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->create();
        $headers = $this->tokenFor($user);

        $this->getJson('/api/me', $headers)->assertOk();
        $this->postJson('/api/logout', [], $headers)->assertOk();
        // Guard meng-cache user per proses PHP, reset agar request
        // berikutnya benar-benar memvalidasi ulang token ke database.
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/me', $headers)->assertUnauthorized();
    }
}
