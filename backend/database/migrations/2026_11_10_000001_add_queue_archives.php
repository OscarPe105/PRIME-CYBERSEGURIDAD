<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_archives', function (Blueprint $t) {
            $t->id();
            $t->foreignId('archived_by')->constrained('users');
            $t->unsignedInteger('count');
            $t->timestamp('created_at');
        });
        Schema::table('findings', function (Blueprint $t) {
            $t->dropUnique(['reference']);
            $t->foreignId('archive_id')->nullable()->constrained('queue_archives');
            // 0 = activo; id del archivo = archivado. Permite reimportar una referencia ya archivada.
            $t->unsignedBigInteger('archive_key')->default(0);
            $t->unique(['reference', 'archive_key']);
        });
    }

    public function down(): void
    {
        Schema::table('findings', function (Blueprint $t) {
            $t->dropUnique(['reference', 'archive_key']);
            $t->dropConstrainedForeignId('archive_id');
            $t->dropColumn('archive_key');
            $t->unique('reference');
        });
        Schema::dropIfExists('queue_archives');
    }
};
