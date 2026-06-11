<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_registration_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('payment_method_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('payment_code')->unique();
            // PAY-XXXX

            $table->decimal('amount', 12, 2);

            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            $table->string('proof_image')->nullable();
            // bukti transfer / screenshot

            $table->timestamp('paid_at')->nullable();

            $table->text('admin_note')->nullable();
            // catatan admin saat reject/approve

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
