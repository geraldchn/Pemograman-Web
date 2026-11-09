<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function make(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/users')->assertRedirect('/login');
    }

    public function test_non_admins_get_403_on_user_management(): void
    {
        foreach ([User::ROLE_CUSTOMER, User::ROLE_TEKNISI] as $role) {
            $user = $this->make($role);
            $target = $this->make(User::ROLE_CUSTOMER);

            $this->actingAs($user)->get('/users')->assertForbidden();
            $this->actingAs($user)->get('/users/create')->assertForbidden();
            $this->actingAs($user)->get("/users/{$target->id}/edit")->assertForbidden();
            $this->actingAs($user)->post('/users', [])->assertForbidden();
            $this->actingAs($user)->put("/users/{$target->id}", [])->assertForbidden();
            $this->actingAs($user)->delete("/users/{$target->id}")->assertForbidden();
            $this->assertDatabaseHas('users', ['id' => $target->id]);
        }
    }

    public function test_everyone_can_open_their_own_profile(): void
    {
        foreach ([User::ROLE_CUSTOMER, User::ROLE_TEKNISI, User::ROLE_ADMIN] as $role) {
            $this->actingAs($this->make($role))->get('/profile')->assertOk();
        }
    }

    public function test_menu_link_only_visible_to_admin(): void
    {
        $this->actingAs($this->make(User::ROLE_ADMIN))->get('/')->assertSee('Manage Users');
        $this->actingAs($this->make(User::ROLE_CUSTOMER))->get('/')->assertDontSee('Manage Users');
    }

    public function test_admin_can_list_create_update_and_delete(): void
    {
        $admin = $this->make(User::ROLE_ADMIN);

        $this->actingAs($admin)->get('/users')->assertOk();

        $this->actingAs($admin)->post('/users', [
            'name' => 'New Tech', 'email' => 'tech@example.com', 'role' => 'teknisi',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect('/users');
        $created = User::where('email', 'tech@example.com')->firstOrFail();
        $this->assertSame('teknisi', $created->role);

        $this->actingAs($admin)->put("/users/{$created->id}", [
            'name' => 'Renamed', 'email' => 'tech@example.com', 'role' => 'customer',
        ])->assertRedirect('/users');
        $this->assertSame('Renamed', $created->fresh()->name);
        $this->assertSame('customer', $created->fresh()->role);
        $this->assertTrue(\Hash::check('password123', $created->fresh()->password), 'blank password keeps old one');

        $this->actingAs($admin)->delete("/users/{$created->id}")->assertRedirect('/users');
        $this->assertDatabaseMissing('users', ['id' => $created->id]);
    }

    public function test_admin_cannot_delete_or_demote_self(): void
    {
        $admin = $this->make(User::ROLE_ADMIN);

        $this->actingAs($admin)->delete("/users/{$admin->id}")->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        $this->actingAs($admin)->put("/users/{$admin->id}", [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'customer',
        ]);
        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_public_registration_always_creates_customer(): void
    {
        $this->post('/register', [
            'name' => 'Sneaky', 'email' => 's@example.com', 'phone' => '0812', 'role' => 'admin',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ]);
        $this->assertSame('customer', User::where('email', 's@example.com')->value('role'));
    }
}
