<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('event_registration_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('reason');
            // Bentrok Jadwal, Darurat, dll

            $table->text('description');

            $table->string('bank_name');
            $table->string('account_number');
            $table->string('account_holder');

            $table->decimal('amount', 12, 2);

            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            $table->text('admin_note')->nullable();

            $table->timestamp('processed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
