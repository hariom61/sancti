<?php

namespace Tests\Sancti;

use App\Models\User;
use Tests\Sancti\DataCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Event;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;

/*
	php artisan test --testsuite=Sancti --stop-on-failure
*/
class SanctiTest extends DataCase
{
	// use RefreshDatabase;

	/* Test OK response */

	function test_register_user()
	{
		$this->deleteUser();

		Event::fake([MessageSent::class]);

		$response = $this->postJson('api/register', $this->data);
		$response->assertStatus(201)->assertJson(['created' => true]);
		$this->assertDatabaseHas('users', ['email' => $this->data['email']]);

		Event::assertDispatched(MessageSent::class, function ($e) {
			$html = $e->message->getBody();
			$this->assertStringContainsString("api/activate", $html);
			$this->assertMatchesRegularExpression('/api\/activate\/[0-9]+\/[a-z0-9]+"/i', $html);
			return true;
		});
	}

	function test_activate_user()
	{
		$this->assertDatabaseHas('users', ['email' => $this->data['email']]);
		$user = User::where('email', $this->data['email'])->get()->first();
		$response = $this->get('api/activate/'.$user->id.'/'.$user->code);
		$response->assertStatus(200)->assertJson(['message' => 'Email has been confirmed.']);
		$this->assertDatabaseHas('users', ['email' => $this->data['email']]);
		$user = User::where('email', $this->data['email'])->get()->first();
		$this->assertNotNull($user->email_verified_at);
	}

	function test_user_login_token()
	{
		$response = $this->postJson('/api/login', $this->data);
		$response->assertStatus(200);
		$this->assertNotNull($response['token']);

		$this->setToken($response['token']);
	}

	function test_user_data()
	{
		$c = 'Bearer ' . $this->getToken();

		$response = $this->withHeaders(['Authorization' => $c])->get('api/user');
		$response->assertStatus(200);
		$this->assertNotNull($response['user']['email']);
	}

	function test_user_change_password()
	{
		$c = 'Bearer ' . $this->getToken();

		$data = [
			'password_current' => 'password123',
			'password' => 'password1234',
			'password_confirmation' => 'password1234'
		];

		$response = $this->withHeaders(['Authorization' => $c])->postJson('/api/change-password', $data);
		$response->assertStatus(200)->assertJson(['message' => 'A password has been updated.']);
	}

	function test_user_login_token_after_pass_change()
	{
		$this->data['password'] = 'password1234';
		$this->data['password_confirmation'] = 'password1234';

		$response = $this->postJson('/api/login', $this->data);
		$response->assertStatus(200);
		$this->assertNotNull($response['token']);

		$this->setToken($response['token']);
	}

	function test_user_reset_password()
	{
		Event::fake([MessageSent::class]);

		$response = $this->postJson('/api/reset', ['email' => $this->data['email']]);
		$response->assertStatus(200)->assertJson(['message' => 'A new password has been sent to the e-mail address provided.']);

		Event::assertDispatched(MessageSent::class, function ($e) {
			$html = $e->message->getBody();
			$this->assertMatchesRegularExpression('/word>[a-zA-Z0-9]+<\/pass/', $html);

			// password
			$pass = $this->getPassword($html);
			$this->setPass($pass);

			return true;
		});
	}

	function test_user_login_token_after_pass_reset()
	{
		$this->data['password'] = $this->getPass();
		$response = $this->postJson('/api/login', $this->data);
		$response->assertStatus(200);
		$this->assertNotNull($response['token']);
		$this->setToken($response['token']);
	}

	function test_user_logout()
	{
		$c = 'Bearer ' . $this->getToken();
		$response = $this->withHeaders(['Authorization' => $c])->getJson('/api/logout');
		$response->assertStatus(200)->assertJson(['message' => 'Logged out.']);
	}

	function test_user_login_token_after_logout()
	{
		$this->data['password'] = $this->getPass();
		$response = $this->postJson('/api/login', $this->data);
		$response->assertStatus(200);
		$this->assertNotNull($response['token']);
		$this->setToken($response['token']);
	}

	function test_user_tokens_delete()
	{
		$c = 'Bearer ' . $this->getToken();
		$response = $this->withHeaders(['Authorization' => $c])->getJson('/api/delete');
		$response->assertStatus(200)->assertJson(['message' => 'Token has been removed.']);
	}

	public function test_user_details_can_be_retrieved()
	{
		Sanctum::actingAs(
			User::factory()->create(),
			['view-user']
		);
		$response = $this->get('/api/user');
		$response->assertOk();
	}

	/* Test ERROR response */

	function test_register_error_duplicate_email()
	{
		$response = $this->postJson('/api/register', $this->data);
		$response->assertStatus(422)->assertJsonMissing(['created'])->assertJson(['message' => 'The email has already been taken.']);
	}

	function test_register_error_name()
	{
		$this->deleteUser();

		unset($this->data['name']);
		$response = $this->postJson('/api/register', $this->data);
		$response->assertStatus(422)->assertJsonMissing(['created'])->assertJson(['message' => 'The name field is required.']);
	}

	function test_register_error_email()
	{
		unset($this->data['email']);
		$response = $this->postJson('/api/register', $this->data);
		$response->assertStatus(422)->assertJsonMissing(['created'])->assertJson(['message' => 'The email field is required.']);
	}

	function test_register_error_password()
	{
		unset($this->data['password']);
		$response = $this->postJson('/api/register', $this->data);
		$response->assertStatus(422)->assertJsonMissing(['created'])->assertJson(['message' => 'The password field is required.']);
	}

	function test_register_error_password_confirmation()
	{
		unset($this->data['password_confirmation']);
		$response = $this->postJson('/api/register', $this->data);
		$response->assertStatus(422)->assertJsonMissing(['created'])->assertJson(['message' => 'The password confirmation does not match.']);
	}
}