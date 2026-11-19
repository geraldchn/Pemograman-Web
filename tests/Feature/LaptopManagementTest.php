<?php

namespace Tests\Feature;

use App\Models\Laptop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LaptopManagementTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = User::ROLE_CUSTOMER): User
    {
        return User::factory()->create(['role' => $role]);
    }

    /** A real 1x1 PNG, so the tests do not need the GD extension. */
    private function png(string $name): UploadedFile
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

        return UploadedFile::fake()->createWithContent($name, $bytes);
    }

    private function payload(array $override = []): array
    {
        return $override + [
            'brand' => 'ASUS', 'model' => 'ROG Strix G15', 'serial_number' => 'SN123', 'purchase_year' => 2022,
            'processor' => 'Ryzen 7 6800H', 'ram_gb' => 16, 'storage' => '512 GB SSD', 'gpu' => 'RTX 3060',
            'screen_size' => 15.6, 'os' => 'Windows 11', 'notes' => 'Has a dent on the lid.',
        ];
    }

    // ---------- create ----------

    public function test_customer_can_add_a_laptop_with_specs(): void
    {
        $customer = $this->user();

        $this->actingAs($customer)->get(route('laptops.create'))->assertOk()->assertSee('Specifications');
        $res = $this->actingAs($customer)->post(route('laptops.store'), $this->payload());

        $laptop = Laptop::firstOrFail();
        $res->assertRedirect(route('laptops.show', $laptop));
        $this->assertSame($customer->id, $laptop->user_id);
        $this->assertSame(16, $laptop->ram_gb);
        $this->assertSame('Ryzen 7 6800H', $laptop->processor);

        $this->actingAs($customer)->get(route('laptops.show', $laptop))
            ->assertOk()->assertSee('Ryzen 7 6800H')->assertSee('16 GB')->assertSee('15.6"')->assertSee('Has a dent on the lid.');
    }

    public function test_only_brand_and_model_are_required(): void
    {
        $this->actingAs($this->user())->post(route('laptops.store'), ['brand' => 'HP', 'model' => 'Pavilion'])
            ->assertSessionHasNoErrors();
        $this->assertSame(1, Laptop::count());
    }

    public function test_validation_rules(): void
    {
        $post = fn (array $d) => $this->actingAs($this->user())->post(route('laptops.store'), $this->payload($d));

        $post(['brand' => ''])->assertSessionHasErrors('brand');
        $post(['model' => ''])->assertSessionHasErrors('model');
        $post(['ram_gb' => 0])->assertSessionHasErrors('ram_gb');
        $post(['ram_gb' => 'lots'])->assertSessionHasErrors('ram_gb');
        $post(['purchase_year' => 1800])->assertSessionHasErrors('purchase_year');
        $post(['purchase_year' => now()->year + 1])->assertSessionHasErrors('purchase_year');
        $post(['screen_size' => 99])->assertSessionHasErrors('screen_size');
        $post(['photo' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')])->assertSessionHasErrors('photo');
        $this->assertSame(0, Laptop::count());
    }

    public function test_customer_cannot_assign_a_laptop_to_someone_else(): void
    {
        $me = $this->user();
        $other = $this->user();

        $this->actingAs($me)->post(route('laptops.store'), $this->payload(['user_id' => $other->id]))
            ->assertSessionHasErrors('user_id');
        $this->assertSame(0, Laptop::count());

        $mine = $me->laptops()->create(['brand' => 'HP', 'model' => 'X']);
        $this->actingAs($me)->put(route('laptops.update', $mine), $this->payload(['user_id' => $other->id]))
            ->assertSessionHasErrors('user_id');
        $this->assertSame($me->id, $mine->fresh()->user_id);
    }

    public function test_photo_upload_replace_and_remove(): void
    {
        Storage::fake('public');
        $customer = $this->user();

        $this->actingAs($customer)->post(route('laptops.store'), $this->payload([
            'photo' => $this->png('a.png'),
        ]));
        $laptop = Laptop::firstOrFail();
        $first = $laptop->photo;
        Storage::disk('public')->assertExists($first);

        $this->actingAs($customer)->put(route('laptops.update', $laptop), $this->payload([
            'photo' => $this->png('b.png'),
        ]));
        $second = $laptop->fresh()->photo;
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        $this->actingAs($customer)->put(route('laptops.update', $laptop), $this->payload(['remove_photo' => 1]));
        $this->assertNull($laptop->fresh()->photo);
        Storage::disk('public')->assertMissing($second);
    }

    // ---------- edit / delete ----------

    public function test_customer_can_edit_own_laptop_and_keeps_photo_when_not_changed(): void
    {
        Storage::fake('public');
        $customer = $this->user();
        $laptop = $customer->laptops()->create(['brand' => 'HP', 'model' => 'Old', 'photo' => 'laptops/x.jpg']);

        $this->actingAs($customer)->get(route('laptops.edit', $laptop))->assertOk()->assertSee('Old');
        $this->actingAs($customer)->put(route('laptops.update', $laptop), $this->payload(['model' => 'New']))
            ->assertRedirect(route('laptops.show', $laptop));

        $this->assertSame('New', $laptop->fresh()->model);
        $this->assertSame('laptops/x.jpg', $laptop->fresh()->photo);
    }

    public function test_customer_cannot_touch_someone_elses_laptop(): void
    {
        $owner = $this->user();
        $laptop = $owner->laptops()->create(['brand' => 'HP', 'model' => 'Mine']);
        $intruder = $this->user();

        $this->actingAs($intruder)->get(route('laptops.edit', $laptop))->assertNotFound();
        $this->actingAs($intruder)->put(route('laptops.update', $laptop), $this->payload())->assertNotFound();
        $this->actingAs($intruder)->delete(route('laptops.destroy', $laptop))->assertNotFound();

        $this->assertSame('Mine', $laptop->fresh()->model);
        $this->assertDatabaseHas('laptops', ['id' => $laptop->id]);
    }

    public function test_laptop_without_service_history_can_be_deleted(): void
    {
        Storage::fake('public');
        $customer = $this->user();
        $laptop = $customer->laptops()->create(['brand' => 'HP', 'model' => 'X']);

        $this->actingAs($customer)->delete(route('laptops.destroy', $laptop))->assertRedirect(route('laptops.index'));
        $this->assertDatabaseMissing('laptops', ['id' => $laptop->id]);
    }

    public function test_laptop_with_service_history_cannot_be_deleted(): void
    {
        $customer = $this->user();
        $laptop = $customer->laptops()->create(['brand' => 'HP', 'model' => 'X']);
        $laptop->bookings()->create(['issue' => 'Broken screen']);

        $this->actingAs($customer)->delete(route('laptops.destroy', $laptop))->assertSessionHas('error');
        $this->assertDatabaseHas('laptops', ['id' => $laptop->id]);
        $this->assertSame(1, $laptop->bookings()->count());
    }

    // ---------- roles ----------

    public function test_technician_cannot_register_laptops(): void
    {
        $tech = $this->user(User::ROLE_TEKNISI);
        $this->actingAs($tech)->get(route('laptops.create'))->assertForbidden();
        $this->actingAs($tech)->post(route('laptops.store'), $this->payload())->assertForbidden();
        $this->assertSame(0, Laptop::count());
    }

    // ---------- admin = super user (acts as a customer too) ----------

    public function test_admin_sees_add_laptop_button_and_owner_picker(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $customer = $this->user();

        $this->actingAs($admin)->get(route('laptops.index'))->assertSee('Add laptop');
        $this->actingAs($admin)->get(route('laptops.create'))->assertOk()
            ->assertSee('Owner')->assertSee($customer->email)->assertSee(' - you');
    }

    public function test_admin_laptop_belongs_to_the_admin_by_default(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);

        $this->actingAs($admin)->post(route('laptops.store'), $this->payload())->assertSessionHasNoErrors();
        $this->assertSame($admin->id, Laptop::firstOrFail()->user_id);
    }

    public function test_admin_can_register_a_laptop_on_behalf_of_a_customer(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $customer = $this->user();

        $this->actingAs($admin)->post(route('laptops.store'), $this->payload(['user_id' => $customer->id]));
        $laptop = Laptop::firstOrFail();

        $this->assertSame($customer->id, $laptop->user_id);
        $this->actingAs($customer)->get(route('laptops.show', $laptop))->assertOk()->assertSee('ROG Strix G15');
    }

    public function test_admin_cannot_assign_a_laptop_to_a_technician_or_unknown_user(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $tech = $this->user(User::ROLE_TEKNISI);

        $this->actingAs($admin)->post(route('laptops.store'), $this->payload(['user_id' => $tech->id]))->assertSessionHasErrors('user_id');
        $this->actingAs($admin)->post(route('laptops.store'), $this->payload(['user_id' => 99999]))->assertSessionHasErrors('user_id');
        $this->assertSame(0, Laptop::count());
    }

    public function test_admin_can_edit_reassign_and_delete_any_laptop(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $owner = $this->user();
        $newOwner = $this->user();
        $laptop = $owner->laptops()->create(['brand' => 'ASUS', 'model' => 'ROG', 'processor' => 'Ryzen 9']);

        $this->actingAs($admin)->get(route('laptops.show', $laptop))->assertOk()->assertSee('Ryzen 9')
            ->assertSee('Edit laptop')->assertSee('Delete laptop');
        $this->actingAs($admin)->get(route('laptops.edit', $laptop))->assertOk();

        $this->actingAs($admin)->put(route('laptops.update', $laptop), $this->payload(['model' => 'Renamed', 'user_id' => $newOwner->id]))
            ->assertRedirect(route('laptops.show', $laptop));
        $this->assertSame('Renamed', $laptop->fresh()->model);
        $this->assertSame($newOwner->id, $laptop->fresh()->user_id);

        $this->actingAs($admin)->delete(route('laptops.destroy', $laptop))->assertRedirect(route('laptops.index'));
        $this->assertDatabaseMissing('laptops', ['id' => $laptop->id]);
    }

    public function test_admin_keeps_the_owner_when_editing_without_choosing_one(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $owner = $this->user();
        $laptop = $owner->laptops()->create(['brand' => 'HP', 'model' => 'X']);

        $this->actingAs($admin)->put(route('laptops.update', $laptop), $this->payload());
        $this->assertSame($owner->id, $laptop->fresh()->user_id);
    }

    public function test_even_admin_cannot_delete_a_laptop_with_service_history(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $laptop = $this->user()->laptops()->create(['brand' => 'HP', 'model' => 'X']);
        $laptop->bookings()->create(['issue' => 'Broken screen']);

        $this->actingAs($admin)->delete(route('laptops.destroy', $laptop))->assertSessionHas('error');
        $this->assertDatabaseHas('laptops', ['id' => $laptop->id]);
    }

    public function test_customer_list_shows_add_button_and_spec_summary(): void
    {
        $customer = $this->user();
        $this->actingAs($customer)->get(route('laptops.index'))->assertSee('Add your first laptop');

        $customer->laptops()->create(['brand' => 'ASUS', 'model' => 'ROG', 'processor' => 'Ryzen 7', 'ram_gb' => 16]);
        $this->actingAs($customer)->get(route('laptops.index'))->assertSee('Add laptop')->assertSee('Ryzen 7 · 16 GB');
    }
}
