<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Alter enum to add 'pending' status
        DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('todo', 'in_progress', 'pending', 'done') DEFAULT 'todo'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Revert back to original enum
        DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('todo', 'in_progress', 'done') DEFAULT 'todo'");
    }
};
