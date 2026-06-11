<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            // organisasi pembuat event
            $table->foreignId('organization_id')
                ->constrained()
                ->onDelete('cascade');

            // category event (NEW RELATION)
            $table->foreignId('event_category_id')
                ->constrained('event_categories')
                ->onDelete('restrict');

            // type event (online / onsite / hybrid)
            $table->foreignId('event_type_id')
                ->constrained('event_types')
                ->onDelete('restrict');

            // core event data
            $table->string('title');
            $table->text('description')->nullable();

            $table->string('location')->nullable();

            $table->integer('quota');

            $table->dateTime('start_date');
            $table->dateTime('end_date');

            // free / paid
            $table->enum('type', ['free', 'paid'])->default('free');

            $table->decimal('price', 12, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
