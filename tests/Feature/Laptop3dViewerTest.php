<?php

namespace Tests\Feature;

use App\Enums\ServiceStatus;
use App\Models\Laptop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Laptop3dViewerTest extends TestCase
{
    use RefreshDatabase;

    private function customerWithLaptop(array $laptop = []): array
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $laptop = $customer->laptops()->create($laptop + ['brand' => 'ASUS', 'model' => 'ROG Strix']);

        return [$customer, $laptop];
    }

    public function test_the_3d_assets_exist_and_are_small(): void
    {
        foreach (['css/techcare-laptop3d.css' => 20000, 'js/techcare-laptop3d.js' => 8000] as $file => $maxBytes) {
            $path = public_path($file);
            $this->assertFileExists($path);
            $this->assertLessThan($maxBytes, filesize($path), "$file should stay lightweight");
        }
    }

    public function test_login_and_register_show_the_3d_laptop_with_assets(): void
    {
        foreach (['/login', '/register'] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('data-l3d', false)
                ->assertSee('css/techcare-laptop3d.css', false)
                ->assertSee('js/techcare-laptop3d.js', false)
                ->assertSee('Laptop service &amp; maintenance', false);   // escaped exactly once
        }
    }

    public function test_laptop_page_screen_shows_name_status_and_booking_code(): void
    {
        [$customer, $laptop] = $this->customerWithLaptop();
        $booking = $laptop->bookings()->create(['issue' => 'Fan is loud']);
        $booking->updates()->create(['status' => ServiceStatus::Repairing, 'note' => 'Replacing fan', 'user_id' => $customer->id]);

        $this->actingAs($customer)->get(route('laptops.show', $laptop))->assertOk()
            ->assertSee('data-l3d', false)
            ->assertSee('data-color="accent"', false)
            ->assertSee('ASUS ROG Strix')
            ->assertSee($booking->code())
            ->assertSee('Repair in Progress');
    }

    public function test_screen_color_follows_the_status(): void
    {
        $cases = [
            [ServiceStatus::WaitingParts, 'warning'],
            [ServiceStatus::Completed, 'success'],
            [ServiceStatus::Cancelled, 'danger'],
        ];

        foreach ($cases as [$status, $color]) {
            [$customer, $laptop] = $this->customerWithLaptop();
            $laptop->bookings()->create(['issue' => 'x'])->updates()
                ->create(['status' => $status, 'note' => 'note', 'user_id' => $customer->id]);

            $this->actingAs($customer)->get(route('laptops.show', $laptop))
                ->assertSee('data-color="'.$color.'"', false);
        }
    }

    public function test_cancelled_service_hides_the_progress_segments_but_completed_fills_them(): void
    {
        [$customer, $laptop] = $this->customerWithLaptop();
        $b = $laptop->bookings()->create(['issue' => 'x']);
        $b->updates()->create(['status' => ServiceStatus::Cancelled, 'note' => 'n', 'user_id' => $customer->id]);

        $this->actingAs($customer)->get(route('laptops.show', $laptop))->assertDontSee('class="steps"', false);

        $b->updates()->delete();
        $b->updates()->create(['status' => ServiceStatus::Completed, 'note' => 'n', 'user_id' => $customer->id]);
        $html = $this->actingAs($customer)->get(route('laptops.show', $laptop))->getContent();
        $this->assertSame(7, substr_count($html, '<i class="on"></i>'), 'all seven segments are filled when completed');
    }

    public function test_laptop_without_service_still_renders_a_3d_laptop(): void
    {
        [$customer, $laptop] = $this->customerWithLaptop();

        $this->actingAs($customer)->get(route('laptops.show', $laptop))->assertOk()
            ->assertSee('data-l3d', false)->assertSee('No service yet');
    }

    public function test_laptop_names_are_escaped_on_the_3d_screen(): void
    {
        [$customer, $laptop] = $this->customerWithLaptop(['brand' => '<script>alert(1)</script>', 'model' => 'X']);

        $this->actingAs($customer)->get(route('laptops.show', $laptop))->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_admin_sees_the_3d_laptop_too(): void
    {
        [, $laptop] = $this->customerWithLaptop();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get(route('laptops.show', $laptop))->assertOk()->assertSee('data-l3d', false);
    }
}
