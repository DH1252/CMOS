<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        Schema::table('information_boards', function (Blueprint $table): void {
            $table->fullText(
                ['title', 'excerpt', 'content', 'meta_title', 'meta_description'],
                'information_boards_search_fulltext',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        Schema::table('information_boards', function (Blueprint $table): void {
            $table->dropFullText('information_boards_search_fulltext');
        });
    }
};
