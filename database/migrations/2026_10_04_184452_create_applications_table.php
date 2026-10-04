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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_record_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('approval_status')->default(0)->comment('承認待ち:0、承認済み:1');
            $table->date('new_date');
            $table->timestamp('new_clock_in')->nullable();
            $table->timestamp('new_clock_out')->nullable();
            $table->text('comment');
            $table->timestamp('application_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
