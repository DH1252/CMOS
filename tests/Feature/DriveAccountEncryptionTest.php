<?php

namespace Tests\Feature;

use App\Models\DriveAccount;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DriveAccountEncryptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_drive_account_password_is_encrypted_at_rest_and_decrypted_by_the_model(): void
    {
        $drive = DriveAccount::create([
            'name' => 'Drive Test',
            'email' => 'drive@example.test',
            'password' => 'rahasia-drive',
            'drive_url' => 'https://drive.google.com/drive/folders/test',
            'is_active' => true,
        ]);

        $storedPassword = DB::table('drive_accounts')->where('id', $drive->id)->value('password');

        $this->assertNotSame('rahasia-drive', $storedPassword);
        $this->assertSame('rahasia-drive', Crypt::decryptString($storedPassword));
        $this->assertSame('rahasia-drive', $drive->fresh()->password);
    }

    public function test_password_migration_encrypts_plaintext_without_double_encrypting_ciphertext(): void
    {
        $alreadyEncrypted = Crypt::encryptString('sudah-terenkripsi');
        $now = now();

        DB::table('drive_accounts')->insert([
            [
                'name' => 'Drive Lama',
                'email' => 'lama@example.test',
                'password' => 'plaintext-lama',
                'drive_url' => 'https://drive.google.com/drive/folders/lama',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Drive Terenkripsi',
                'email' => 'encrypted@example.test',
                'password' => $alreadyEncrypted,
                'drive_url' => 'https://drive.google.com/drive/folders/encrypted',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $migration = require database_path('migrations/2026_07_22_131358_encrypt_existing_drive_account_passwords.php');
        $migration->up();

        $plaintextValue = DB::table('drive_accounts')->where('email', 'lama@example.test')->value('password');
        $encryptedValue = DB::table('drive_accounts')->where('email', 'encrypted@example.test')->value('password');

        $this->assertNotSame('plaintext-lama', $plaintextValue);
        $this->assertSame('plaintext-lama', Crypt::decryptString($plaintextValue));
        $this->assertSame($alreadyEncrypted, $encryptedValue);
    }

    public function test_drive_password_is_limited_to_255_characters(): void
    {
        $role = Role::create(['name' => 'admin']);
        $admin = User::factory()->createOne(['role_id' => $role->id]);

        $this->actingAs($admin)->post(route('drives.store'), [
            'name' => 'Drive Test',
            'email' => 'drive@example.test',
            'password' => str_repeat('x', 256),
            'drive_url' => 'https://drive.google.com/drive/folders/test',
        ])->assertInvalid('password');
    }
}
