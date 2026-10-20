<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_login_page_renders_accessible_form_without_exposing_password(): void
    {
        $response = $this->get('/login');

        $response->assertOk()->assertSee('Welcome back.')->assertSee('name="username"', false)
            ->assertSee('type="password"', false)->assertSee('Show password')->assertDontSee('admin123');
    }

    #[TestWith(['/overview'])]
    #[TestWith(['/monitoring'])]
    #[TestWith(['/historical-baseline'])]
    #[TestWith(['/areas-of-interest'])]
    #[TestWith(['/alerts'])]
    #[TestWith(['/reports'])]
    #[TestWith(['/reports/print'])]
    #[TestWith(['/observations/export'])]
    #[TestWith(['/administration'])]
    #[TestWith(['/poc-evaluation'])]
    public function test_guest_cannot_access_protected_screen_or_export(string $path): void
    {
        $response = $this->get($path);

        $response->assertRedirectToRoute('login')->assertSessionHas('url.intended', url($path));
    }

    public function test_guest_json_request_returns_401(): void
    {
        $this->getJson('/observations/export')->assertUnauthorized();
    }

    #[TestWith(['/administration/settings'])]
    #[TestWith(['/alerts/ANM-026/review'])]
    public function test_guest_cannot_change_demo_state(string $path): void
    {
        $response = $this->post($path, ['status' => 'Closed', 'note' => 'Attempted without signing in.']);

        $response->assertRedirectToRoute('login')->assertSessionMissing('iems.reviews')->assertSessionMissing('iems.settings');
    }

    public function test_admin_credentials_create_session_and_rotate_session_id(): void
    {
        $this->withSession([]);
        $previousId = session()->getId();

        $response = $this->post('/login', ['username' => 'admin', 'password' => 'admin123']);

        $response->assertRedirectToRoute('iems.overview')->assertSessionHas('iems.authenticated', true);
        $this->assertNotSame($previousId, session()->getId());
    }

    public function test_login_returns_to_requested_screen_with_filters(): void
    {
        $this->withSession(['url.intended' => url('/monitoring?parameter=water&aoi=catchment')]);

        $response = $this->post('/login', ['username' => 'admin', 'password' => 'admin123']);

        $response->assertRedirect('/monitoring?parameter=water&aoi=catchment');
        $response->assertSessionHas('iems.authenticated', true);
    }

    #[TestWith(['admin', 'wrong'])]
    #[TestWith(['wrong', 'admin123'])]
    #[TestWith(['Admin', 'admin123'])]
    public function test_incorrect_credentials_show_generic_error_without_creating_session(string $username, string $password): void
    {
        $response = $this->from('/login')->post('/login', compact('username', 'password'));

        $response->assertRedirect('/login')->assertSessionHasErrors(['username' => 'The username or password is incorrect.']);
        $response->assertSessionMissing('iems.authenticated');
        $response->assertSessionMissing('_old_input.password');
    }

    public function test_required_credentials_are_validated(): void
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['username', 'password'])->assertSessionMissing('iems.authenticated');
    }

    public function test_sixth_login_attempt_in_one_minute_is_throttled(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', ['username' => 'admin', 'password' => 'wrong']);
        }

        $response = $this->post('/login', ['username' => 'admin', 'password' => 'wrong']);

        $response->assertTooManyRequests()->assertSessionMissing('iems.authenticated');
    }

    public function test_authenticated_admin_visiting_login_goes_to_dashboard(): void
    {
        $response = $this->withSession(['iems.authenticated' => true])->get('/login');

        $response->assertRedirectToRoute('iems.overview');
    }

    public function test_authenticated_dashboard_has_sign_out_action_and_disables_caching(): void
    {
        $response = $this->withSession(['iems.authenticated' => true])->get('/');

        $response->assertOk()->assertSee('Administrator')->assertSee('action="'.route('logout').'"', false);
        $response->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_logout_invalidates_demo_session_and_rotates_csrf_token(): void
    {
        $this->withSession(['iems.authenticated' => true, 'iems.reviews.ANM-026.status' => 'Reviewed', '_token' => 'previous-token']);

        $response = $this->post('/logout');

        $response->assertRedirectToRoute('login')->assertSessionMissing('iems')->assertSessionHas('success', 'You have been signed out.');
        $this->assertNotSame('previous-token', session()->token());
    }

    public function test_truthy_session_value_does_not_bypass_login(): void
    {
        $response = $this->withSession(['iems.authenticated' => 'true'])->get('/');

        $response->assertRedirectToRoute('login');
    }
}
