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
        Schema::create('checklist_results', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tenant_id')->unsigned()->index();
            $table->bigInteger('checklist_id')->unsigned()->index();
            $table->bigInteger('custom_form_id')->unsigned()->index();
            $table->bigInteger('employee_id')->nullable()->unsigned()->index();
            $table->bigInteger('asset_id')->nullable()->unsigned()->index();
            $table->bigInteger('room_id')->nullable()->unsigned()->index();
            $table->bigInteger('floor_id')->nullable()->unsigned()->index();
            $table->bigInteger('location_id')->nullable()->unsigned()->index();
            $table->string('checklist_name')->nullable();
            $table->string('checklist_desc', 1000)->nullable();
            $table->string('customer_name')->nullable();
            $table->string('employee_name')->nullable();
            $table->string('allotment_code')->default('no code');
            $table->string('allotment_name')->nullable();
            $table->string('asset_name')->nullable();
            $table->string('room_name')->nullable();
            $table->string('floor_name')->nullable();
            $table->string('location_code')->nullable();
            $table->string('location_name')->nullable();
            $table->enum('allotment', ['location', 'floor', 'room', 'asset']);
            $table->boolean('is_checked')->default(false);
            $table->enum('period', ['hour', 'day', 'week', 'two weeks', 'month', 'quarter', 'half year', 'year'])->default('month');
            $table->dateTime('started_at');
            $table->dateTime('ended_at');
            $table->dateTime('filled_at')->nullable();
            $table->boolean('has_geolocation')->default(false);
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->boolean('has_abnormal_answer')->default(false);
            $table->boolean('is_overdue')->default(false);
            $table->boolean('need_approval')->default(false);
            $table->boolean('has_save_to_draft')->default(false);
            $table->integer('total_reject')->default(0);
            $table->string('reject_reason')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('timezone')->default('UTC');
            $table->boolean('is_finished')->default(false);
            $table->enum('status', ['draft', 'posted'])->default('draft');
            $table->string('back_to_draft_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('checklist_id')->references('id')->on('checklists');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_results');
    }
};
