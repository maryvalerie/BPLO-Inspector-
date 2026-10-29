<?php

namespace Tests\Feature\Inspector;

use App\Models\Application;
use App\Models\Permit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Support\MakesDomainData;
use Tests\TestCase;

class StandaloneInspectorPortalTest extends TestCase
{
    use MakesDomainData, RefreshDatabase;

    public function test_only_inspector_and_public_verification_routes_are_exposed(): void
    {
        $this->assertTrue(Route::has('inspector.dashboard'));
        $this->assertTrue(Route::has('inspector.permits.index'));
        $this->assertTrue(Route::has('inspector.permits.show'));
        $this->assertTrue(Route::has('verify.lookup'));
        $this->assertTrue(Route::has('verify.show'));

        $this->assertFalse(Route::has('bplo.dashboard'));
        $this->assertFalse(Route::has('bplo.applications.index'));
        $this->assertFalse(Route::has('bplo.applications.release'));
        $this->assertFalse(Route::has('treasurer.payments.index'));
        $this->assertFalse(Route::has('admin.dashboard'));
        $this->assertFalse(Route::has('applications.create'));
        $this->assertFalse(Route::has('password.request'));
        $this->assertFalse(Route::has('storage.local'));
    }

    public function test_active_inspector_can_open_monitoring_dashboard_and_scanner(): void
    {
        $inspector = $this->makeInspector();
        $application = $this->makeApplication(
            $this->makeApplicant(),
            Application::STATUS_PERMIT_RELEASED,
        );
        $permit = $this->makePermit($application);

        $this->actingAs($inspector)->get('/inspector/dashboard')
            ->assertOk()
            ->assertSee('Start camera scanner')
            ->assertSee('Upload QR image')
            ->assertSee('qr-image-upload')
            ->assertSee('QR verification');

        $this->actingAs($inspector)->get('/inspector/permits')
            ->assertOk()
            ->assertSee($permit->permit_number)
            ->assertSee($permit->business_name);

        $this->actingAs($inspector)->get("/inspector/permits/{$permit->getKey()}")
            ->assertOk()
            ->assertSee('Scan History')
            ->assertSee($permit->verification_token);
    }

    public function test_non_inspector_users_cannot_sign_in_or_open_monitoring_pages(): void
    {
        $bplo = $this->makeBplo();

        $this->post('/login', [
            'email' => $bplo->email,
            'password' => 'Password123!',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->actingAs($bplo)
            ->get('/inspector/dashboard')
            ->assertForbidden();
    }

    public function test_active_inspector_can_sign_in_to_the_standalone_portal(): void
    {
        $inspector = $this->makeInspector();

        $this->post('/login', [
            'email' => $inspector->email,
            'password' => 'Password123!',
        ])->assertRedirect('/inspector/dashboard');

        $this->assertAuthenticatedAs($inspector);
    }

    public function test_qr_verification_logs_a_scan_and_exposes_only_public_permit_fields(): void
    {
        $application = $this->makeApplication(
            $this->makeApplicant(),
            Application::STATUS_PERMIT_RELEASED,
            ['contact_number' => '09998887777'],
        );
        $permit = $this->makePermit($application);

        $this->get("/verify/{$permit->verification_token}")
            ->assertOk()
            ->assertSee('VALID PERMIT')
            ->assertSee($permit->permit_number)
            ->assertSee($permit->business_name)
            ->assertDontSee('09998887777')
            ->assertDontSee($application->email);

        $this->assertDatabaseHas('qr_verifications', [
            'permit_id' => $permit->getKey(),
            'token' => $permit->verification_token,
            'result' => 'VALID',
        ]);
    }

    public function test_login_page_is_for_inspectors_and_has_no_applicant_registration(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('INSPECTOR ACCESS')
            ->assertSee('Sign in to Inspector Portal')
            ->assertDontSee('Register as an applicant');
    }
}
