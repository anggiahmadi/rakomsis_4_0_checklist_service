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
        Schema::create('checklists', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tenant_id')->unsigned()->index();
            $table->bigInteger('form_template_id')->unsigned()->index();
            $table->string('code');
            $table->string('name');
            $table->integer('quantity')->default(1);
            $table->enum('period', ['hour', 'day', 'week', 'two weeks', 'month', 'two months', 'quarter', 'half year', 'year'])->default('day');
            $table->time('started_time_at')->nullable(); // Only show when period is hour
            $table->time('ended_time_at')->nullable(); // Only show when period is hour
            $table->boolean('mon')->default(true); // Only show when period is hour or day with condition generate checklist if value is true on specific day
            $table->boolean('tue')->default(true); // Only show when period is hour or day with condition generate checklist if value is true on specific day
            $table->boolean('wed')->default(true); // Only show when period is hour or day with condition generate checklist if value is true on specific day
            $table->boolean('thu')->default(true); // Only show when period is hour or day with condition generate checklist if value is true on specific day
            $table->boolean('fri')->default(true); // Only show when period is hour or day with condition generate checklist if value is true on specific day
            $table->boolean('sat')->default(true); // Only show when period is hour or day with condition generate checklist if value is true on specific day
            $table->boolean('sun')->default(true); // Only show when period is hour or day with condition generate checklist if value is true on specific day
            $table->boolean('need_approval')->default(false);
            $table->boolean('has_save_to_draft')->default(false);
            $table->boolean('has_unfinish_notification')->default(false);
            $table->time('notification_started_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('checklist_division', function (Blueprint $table) {
            $table->bigInteger('checklist_id')->unsigned()->index();
            $table->bigInteger('division_id')->unsigned()->index();
            $table->timestamps();
            $table->primary(['checklist_id', 'division_id']);
            $table->foreign('checklist_id')->references('id')->on('checklists');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_division');
        Schema::dropIfExists('checklists');
    }
};
