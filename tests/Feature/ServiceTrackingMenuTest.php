<?php

namespace Tests\Feature;

use App\Enums\ServiceStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTrackingMenuTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = User::ROLE_CUSTOMER): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_guest_and_technician_cannot_open_service_tracking(): void
    {
        $this->get(route('tracking.index'))->assertRedirect('/login');
        $this->actingAs($this->user(User::ROLE_TEKNISI))->get(route('tracking.index'))->assertForbidden();
    }

    public function test_tracking_lists_only_laptops_that_have_a_booking(): void
    {
        $customer = $this->user();
        $serviced = $customer->laptops()->create(['brand' => 'ASUS', 'model' => 'InService']);
        $serviced->bookings()->create(['issue' => 'Fan noise']);
        $customer->laptops()->create(['brand' => 'HP', 'model' => 'NeverServiced']);

        $this->actingAs($customer)->get(route('tracking.index'))->assertOk()
            ->assertSee('InService')->assertDontSee('NeverServiced');
    }

    public function test_tracking_has_no_add_laptop_button(): void
    {
        foreach ([User::ROLE_CUSTOMER, User::ROLE_ADMIN] as $role) {
            $this->actingAs($this->user($role))->get(route('tracking.index'))->assertOk()
                ->assertDontSee('Add laptop')->assertDontSee('Add your first laptop');
        }
    }

    public function test_customer_tracking_is_limited_to_own_laptops_and_admin_sees_all(): void
    {
        $mine = $this->user();
        $other = $this->user();
        $mine->laptops()->create(['brand' => 'ASUS', 'model' => 'Mine'])->bookings()->create(['issue' => 'x']);
        $other->laptops()->create(['brand' => 'HP', 'model' => 'Theirs'])->bookings()->create(['issue' => 'y']);

        $this->actingAs($mine)->get(route('tracking.index'))->assertSee('Mine')->assertDontSee('Theirs');
        $this->actingAs($this->user(User::ROLE_ADMIN))->get(route('tracking.index'))->assertSee('Mine')->assertSee('Theirs');
    }

    public function test_admin_can_search_tracking_by_owner(): void
    {
        $alice = User::factory()->create(['role' => 'customer', 'name' => 'Alice Wonder']);
        $bob = User::factory()->create(['role' => 'customer', 'name' => 'Bob Builder']);
        $alice->laptops()->create(['brand' => 'ASUS', 'model' => 'AliceLaptop'])->bookings()->create(['issue' => 'x']);
        $bob->laptops()->create(['brand' => 'HP', 'model' => 'BobLaptop'])->bookings()->create(['issue' => 'y']);

        $this->actingAs($this->user(User::ROLE_ADMIN))->get(route('tracking.index', ['q' => 'Alice']))
            ->assertSee('AliceLaptop')->assertDontSee('BobLaptop');
    }

    public function test_sidebar_has_separate_laptops_and_tracking_menus(): void
    {
        $this->actingAs($this->user())->get(route('dashboard'))
            ->assertSee('My Laptops')->assertSee('Service Tracking');
        $this->actingAs($this->user(User::ROLE_ADMIN))->get(route('dashboard'))
            ->assertSee('Service Tracking')->assertSee(route('laptops.index'), false);
    }

    public function test_laptops_page_is_for_managing_data_not_progress(): void
    {
        $customer = $this->user();
        $customer->laptops()->create(['brand' => 'ASUS', 'model' => 'ROG', 'processor' => 'Ryzen 7'])->bookings()->create(['issue' => 'x']);

        $this->actingAs($customer)->get(route('laptops.index'))->assertOk()
            ->assertSee('Add laptop')->assertSee('Edit')->assertSee('Details')->assertDontSee('View progress');
    }

    public function test_from_tracking_is_kept_through_progress_actions(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $laptop = $this->user()->laptops()->create(['brand' => 'ASUS', 'model' => 'ROG']);
        $booking = $laptop->bookings()->create(['issue' => 'x']);

        // Back button goes to tracking when we came from there, and to Laptops otherwise.
        $this->actingAs($admin)->get(route('laptops.show', [$laptop, 'from' => 'tracking']))
            ->assertSee('href="'.route('tracking.index').'"', false);
        $this->actingAs($admin)->get(route('laptops.show', $laptop))
            ->assertSee('href="'.route('laptops.index').'"', false);

        // Adding an update from tracking redirects back to the tracking context.
        $this->actingAs($admin)
            ->post(route('updates.store', [$booking, 'from' => 'tracking']), ['status' => ServiceStatus::Received->value, 'note' => 'Checked in'])
            ->assertRedirect(route('laptops.show', ['laptop' => $laptop->id, 'booking' => $booking->id, 'from' => 'tracking']));

        $this->actingAs($admin)
            ->post(route('updates.store', $booking), ['status' => ServiceStatus::Diagnosing->value, 'note' => 'Looking'])
            ->assertRedirect(route('laptops.show', ['laptop' => $laptop->id, 'booking' => $booking->id]));
    }
}
