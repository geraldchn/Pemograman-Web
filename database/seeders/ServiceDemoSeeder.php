<?php

namespace Database\Seeders;

use App\Enums\ServiceStatus as S;
use App\Models\Laptop;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Simulation data for the service-progress module, until the real
 * Laptop + Booking modules exist. Safe to re-run: skips if laptops already exist.
 */
class ServiceDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Laptop::exists()) {
            return;
        }

        $customer = User::where('email', 'customer@techcare.test')->first()
            ?? User::where('role', User::ROLE_CUSTOMER)->first();
        $admin = User::where('role', User::ROLE_ADMIN)->first();

        if (! $customer || ! $admin) {
            return;
        }

        // 1) ASUS: an old finished service + a repair currently in progress
        $asus = $customer->laptops()->create([
            'brand' => 'ASUS', 'model' => 'ROG Strix G15', 'serial_number' => 'RS15-2291834', 'purchase_year' => 2022,
            'processor' => 'AMD Ryzen 7 6800H', 'ram_gb' => 16, 'storage' => '512 GB NVMe SSD', 'gpu' => 'NVIDIA RTX 3060',
            'screen_size' => 15.6, 'os' => 'Windows 11', 'notes' => 'Comes with original charger and a laptop sleeve.',
        ]);
        $old = $this->booking($asus, 'Keyboard keys not responding.', now()->subDays(40));
        $this->timeline($old, $admin, now()->subDays(40), [
            [S::Received, 'Laptop received. Keyboard tested: 6 keys unresponsive.', 0],
            [S::Repairing, 'Replacing keyboard module.', 60 * 26],
            [S::ReadyForPickup, 'Repair finished and tested. Ready for pickup.', 60 * 50],
            [S::Completed, 'Picked up by customer. Warranty: 30 days.', 60 * 54],
        ]);
        $active = $this->booking($asus, 'Overheating and loud fan under light load.', now()->subDays(3));
        $this->timeline($active, $admin, now()->subDays(3), [
            [S::Received, 'Laptop received at front desk. No physical damage found.', 0],
            [S::Diagnosing, 'Fan clogged with dust and thermal paste has dried out.', 60 * 5],
            [S::WaitingParts, 'Ordered a replacement fan. Estimated arrival in 2 days.', 60 * 9],
            [S::Repairing, 'Part arrived. Replacing fan and applying new thermal paste.', 60 * 50],
        ]);

        // 2) Lenovo: just booked, laptop not yet dropped off
        $lenovo = $customer->laptops()->create([
            'brand' => 'Lenovo', 'model' => 'ThinkPad E14', 'serial_number' => 'PF3K8HQ2', 'purchase_year' => 2023,
            'processor' => 'Intel Core i5-1235U', 'ram_gb' => 8, 'storage' => '256 GB SSD', 'gpu' => 'Intel Iris Xe',
            'screen_size' => 14.0, 'os' => 'Windows 11 Pro',
        ]);
        $this->booking($lenovo, 'Battery drains within an hour.', now()->subHours(3), now()->addDays(2)->setTime(10, 0));

        // 3) HP: cancelled by the customer
        $hp = $customer->laptops()->create([
            'brand' => 'HP', 'model' => 'Pavilion 14', 'serial_number' => null, 'purchase_year' => 2020,
            'processor' => 'Intel Core i3-1115G4', 'ram_gb' => 4, 'storage' => '1 TB HDD', 'screen_size' => 14.0, 'os' => 'Windows 10',
        ]);
        $cancelled = $this->booking($hp, 'Screen flickers occasionally.', now()->subDays(8));
        $this->timeline($cancelled, $admin, now()->subDays(8), [
            [S::Received, 'Laptop received.', 0],
            [S::Cancelled, 'Cancelled at customer request: they will try a different display cable first.', 60 * 20],
        ]);
    }

    private function booking(Laptop $laptop, string $issue, $createdAt, $scheduledAt = null)
    {
        $booking = $laptop->bookings()->create([
            'issue' => $issue,
            'scheduled_at' => $scheduledAt ?? $createdAt->copy()->addHours(2),
        ]);
        $booking->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

        return $booking;
    }

    /** @param array<int, array{0: S, 1: string, 2: int}> $steps  [status, note, minutes after start] */
    private function timeline($booking, User $admin, $start, array $steps): void
    {
        foreach ($steps as [$status, $note, $minutes]) {
            $at = $start->copy()->addHours(3)->addMinutes($minutes);
            $update = $booking->updates()->create(['status' => $status, 'note' => $note, 'user_id' => $admin->id]);
            $update->forceFill(['created_at' => $at, 'updated_at' => $at])->save();
        }
    }
}
