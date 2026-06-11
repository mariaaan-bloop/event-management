<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organization_categories', function (Blueprint $table) {
            $table->id();

            $table->string('name');     // contoh: Education, Health
            $table->string('emoji');    // contoh: 🎓 🏥 🌱 💻

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_categories');
    }
};
