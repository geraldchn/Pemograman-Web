<?php

namespace Tests\Feature;

use App\Enums\ServiceStatus;
use App\Models\Laptop;
use App\Models\ServiceBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceProgressTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;
    private Laptop $laptop;
    private ServiceBooking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $this->laptop = $this->customer->laptops()->create(['brand' => 'ASUS', 'model' => 'ROG']);
        $this->booking = $this->laptop->bookings()->create(['issue' => 'Overheating']);
    }

    private function addUpdate(ServiceStatus $status, string $note = 'A note'): void
    {
        $this->actingAs($this->admin)->post(route('updates.store', $this->booking), [
            'status' => $status->value, 'note' => $note,
        ])->assertSessionHasNoErrors();
    }

    private function currentStatus(): ServiceStatus
    {
        return $this->booking->fresh()->currentStatus();
    }

    // ---------- access ----------

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('laptops.index'))->assertRedirect('/login');
        $this->get(route('laptops.show', $this->laptop))->assertRedirect('/login');
    }

    public function test_customer_sees_only_own_laptops_and_timeline(): void
    {
        $this->addUpdate(ServiceStatus::Received, 'Laptop checked in at desk');

        $this->actingAs($this->customer)->get(route('laptops.index'))->assertOk()->assertSee('ROG');
        $this->actingAs($this->customer)->get(route('laptops.show', $this->laptop))
            ->assertOk()->assertSee('Laptop checked in at desk')->assertSee('Laptop Received');
    }

    public function test_customer_cannot_open_someone_elses_laptop(): void
    {
        $other = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $this->actingAs($other)->get(route('laptops.show', $this->laptop))->assertNotFound();
        $this->actingAs($other)->get(route('laptops.index'))->assertDontSee('ROG');
    }

    public function test_technician_has_no_access_yet(): void
    {
        $tech = User::factory()->create(['role' => User::ROLE_TEKNISI]);
        $this->actingAs($tech)->get(route('laptops.index'))->assertForbidden();
        $this->actingAs($tech)->get(route('laptops.show', $this->laptop))->assertForbidden();
    }

    public function test_customer_cannot_add_edit_or_delete_updates(): void
    {
        $this->addUpdate(ServiceStatus::Received);
        $update = $this->booking->latestUpdate()->first();

        $this->actingAs($this->customer)->post(route('updates.store', $this->booking), ['status' => 'repairing', 'note' => 'hack'])->assertForbidden();
        $this->actingAs($this->customer)->put(route('updates.update', [$this->booking, $update]), ['note' => 'hack'])->assertForbidden();
        $this->actingAs($this->customer)->delete(route('updates.destroy', [$this->booking, $update]))->assertForbidden();
        $this->assertSame(1, $this->booking->updates()->count());
    }

    public function test_admin_sees_all_laptops_with_owner(): void
    {
        $this->actingAs($this->admin)->get(route('laptops.index'))->assertOk()->assertSee('ROG')->assertSee($this->customer->name);
        $this->actingAs($this->admin)->get(route('laptops.show', $this->laptop))->assertOk()->assertSee('Add progress update');
    }

    public function test_customer_does_not_see_admin_controls(): void
    {
        $this->addUpdate(ServiceStatus::Received);
        $this->actingAs($this->customer)->get(route('laptops.show', $this->laptop))
            ->assertDontSee('Add progress update')->assertDontSee('Edit note')->assertDontSee('Delete');
    }

    // ---------- status logic ----------

    public function test_new_booking_defaults_to_booked(): void
    {
        $this->assertSame(ServiceStatus::Booked, $this->currentStatus());
    }

    public function test_admin_adds_updates_and_current_status_follows_the_latest(): void
    {
        $this->addUpdate(ServiceStatus::Received);
        $this->addUpdate(ServiceStatus::Diagnosing);
        $this->assertSame(ServiceStatus::Diagnosing, $this->currentStatus());
        $this->assertSame($this->admin->id, $this->booking->latestUpdate()->first()->user_id);
    }

    public function test_update_validation(): void
    {
        $post = fn (array $d) => $this->actingAs($this->admin)->post(route('updates.store', $this->booking), $d);

        $post(['status' => 'received', 'note' => ''])->assertSessionHasErrors('note');
        $post(['status' => 'nonsense', 'note' => 'abc'])->assertSessionHasErrors('status');
        $post(['status' => 'booked', 'note' => 'abc'])->assertSessionHasErrors('status');
        $this->assertSame(0, $this->booking->updates()->count());
    }

    public function test_cannot_add_update_after_completed_or_cancelled(): void
    {
        foreach ([ServiceStatus::Completed, ServiceStatus::Cancelled] as $final) {
            $booking = $this->laptop->bookings()->create(['issue' => 'x']);
            $booking->updates()->create(['status' => $final, 'note' => 'done', 'user_id' => $this->admin->id]);

            $this->actingAs($this->admin)->post(route('updates.store', $booking), ['status' => 'repairing', 'note' => 'again'])
                ->assertSessionHas('error');
            $this->assertSame(1, $booking->updates()->count());
        }
    }

    // ---------- edit / delete LAST only ----------

    public function test_admin_can_edit_note_of_last_update_only(): void
    {
        $this->addUpdate(ServiceStatus::Received, 'first');
        $this->addUpdate(ServiceStatus::Diagnosing, 'second');
        [$first, $second] = $this->booking->updates()->get()->all();

        $this->actingAs($this->admin)->put(route('updates.update', [$this->booking, $second]), ['note' => 'second fixed'])
            ->assertSessionHas('success');
        $this->assertSame('second fixed', $second->fresh()->note);
        $this->assertSame(ServiceStatus::Diagnosing, $second->fresh()->status, 'status must not change');

        $this->actingAs($this->admin)->put(route('updates.update', [$this->booking, $first]), ['note' => 'hacked'])
            ->assertSessionHas('error');
        $this->assertSame('first', $first->fresh()->note);
    }

    public function test_edit_note_validation(): void
    {
        $this->addUpdate(ServiceStatus::Received, 'first');
        $update = $this->booking->latestUpdate()->first();

        $this->actingAs($this->admin)->put(route('updates.update', [$this->booking, $update]), ['note' => ''])
            ->assertSessionHasErrors('note', null, 'editNote');
        $this->assertSame('first', $update->fresh()->note);
    }

    public function test_admin_can_delete_last_update_only_and_status_reverts(): void
    {
        $this->addUpdate(ServiceStatus::Received);
        $this->addUpdate(ServiceStatus::Repairing);
        [$first, $second] = $this->booking->updates()->get()->all();

        $this->actingAs($this->admin)->delete(route('updates.destroy', [$this->booking, $first]))->assertSessionHas('error');
        $this->assertSame(2, $this->booking->updates()->count());

        $this->actingAs($this->admin)->delete(route('updates.destroy', [$this->booking, $second]))->assertSessionHas('success');
        $this->assertSame(ServiceStatus::Received, $this->currentStatus(), 'reverts to previous update');

        $this->actingAs($this->admin)->delete(route('updates.destroy', [$this->booking, $first]));
        $this->assertSame(ServiceStatus::Booked, $this->currentStatus(), 'back to Booked when no updates remain');
    }

    public function test_deleting_a_completed_update_reopens_the_service(): void
    {
        $this->addUpdate(ServiceStatus::Completed);
        $this->actingAs($this->admin)->delete(route('updates.destroy', [$this->booking, $this->booking->latestUpdate()->first()]));

        $this->addUpdate(ServiceStatus::Repairing, 'Actually still broken');
        $this->assertSame(ServiceStatus::Repairing, $this->currentStatus());
    }

    public function test_cannot_touch_an_update_through_another_bookings_url(): void
    {
        $this->addUpdate(ServiceStatus::Received);
        $update = $this->booking->latestUpdate()->first();
        $otherBooking = $this->laptop->bookings()->create(['issue' => 'other']);

        $this->actingAs($this->admin)->delete(route('updates.destroy', [$otherBooking, $update]))->assertNotFound();
        $this->assertSame(1, $this->booking->updates()->count());
    }

    public function test_selecting_a_specific_booking_in_history(): void
    {
        $second = $this->laptop->bookings()->create(['issue' => 'Second problem']);
        $this->actingAs($this->customer)->get(route('laptops.show', [$this->laptop, 'booking' => $second->id]))
            ->assertOk()->assertSee('Second problem');
        $this->actingAs($this->customer)->get(route('laptops.show', [$this->laptop, 'booking' => 9999]))->assertNotFound();
    }
}
