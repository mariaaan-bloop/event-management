<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();

            // akun login organisasi
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // relasi category (IMPORTANT CHANGE)
            $table->foreignId('organization_category_id')
                ->constrained('organization_categories')
                ->onDelete('restrict');

            // identitas organisasi
            $table->string('org_name');

            $table->text('description')->nullable();

            // kontak
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // opsional alamat
            $table->text('address')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
