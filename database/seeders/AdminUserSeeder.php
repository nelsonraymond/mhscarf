<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = trim((string) config('admin.seed.name'));
        $email = strtolower(trim((string) config('admin.seed.email')));
        $password = (string) config('admin.seed.password');

        if ($email === '' || $password === '') {
            throw new RuntimeException(
                'Atur ADMIN_EMAIL dan ADMIN_PASSWORD di .env terlebih dahulu.'
            );
        }

        $user = User::query()->firstOrNew([
            'email' => $email,
        ]);

        $user->name = $name !== '' ? $name : 'Admin Mutya Store';

        // Seeder ini menetapkan role melalui forceFill karena role
        // tidak harus tersedia di $fillable.
        $user->forceFill([
            'role' => 'admin',
            'password' => Hash::make($password),
        ]);

        $user->save();

        $this->command?->info(
            "Akun admin {$email} berhasil dibuat atau diperbarui."
        );
    }
}
