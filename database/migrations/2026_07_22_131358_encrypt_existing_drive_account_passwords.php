<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drive_accounts', function (Blueprint $table) {
            $table->text('password')->change();
        });

        DB::table('drive_accounts')
            ->orderBy('id')
            ->chunkById(100, function ($accounts): void {
                foreach ($accounts as $account) {
                    if ($this->isEncrypted($account->password)) {
                        continue;
                    }

                    DB::table('drive_accounts')
                        ->where('id', $account->id)
                        ->update(['password' => Crypt::encryptString($account->password)]);
                }
            });
    }

    public function down(): void
    {
        DB::table('drive_accounts')
            ->orderBy('id')
            ->chunkById(100, function ($accounts): void {
                foreach ($accounts as $account) {
                    if (! $this->isEncrypted($account->password)) {
                        continue;
                    }

                    DB::table('drive_accounts')
                        ->where('id', $account->id)
                        ->update(['password' => Crypt::decryptString($account->password)]);
                }
            });

        Schema::table('drive_accounts', function (Blueprint $table) {
            $table->string('password')->change();
        });
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);

            return true;
        } catch (DecryptException) {
            return false;
        }
    }
};
