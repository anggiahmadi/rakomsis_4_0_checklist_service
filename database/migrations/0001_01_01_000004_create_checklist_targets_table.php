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
        Schema::create('checklist_targets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tenant_id')->unsigned()->index();
            $table->string('code')->index(); // Replacement of allotment code from former version
            $table->string('name'); // Replacement of allotment name from former version
            $table->dateTime('started_at');
            $table->dateTime('ended_at');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('checklist_target_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tenant_id')->unsigned()->index();
            $table->bigInteger('checklist_target_id')->unsigned()->index();
            $table->bigInteger('checklist_id')->unsigned()->index();
            $table->bigInteger('location_id')->nullable()->unsigned()->index();
            $table->bigInteger('floor_id')->nullable()->unsigned()->index();
            $table->bigInteger('room_id')->nullable()->unsigned()->index();
            $table->bigInteger('asset_id')->nullable()->unsigned()->index();
            $table->enum('allotment', ['location', 'floor', 'room', 'asset']);
            $table->string('allotment_code')->nullable();
            $table->string('allotment_name')->nullable();
            $table->string('timezone')->default('UTC');
            $table->timestamps();
            $table->foreign('checklist_target_id')->references('id')->on('checklist_targets')->onDelete('cascade');
            $table->foreign('checklist_id')->references('id')->on('checklists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_target_details');
        Schema::dropIfExists('checklist_targets');
    }
};
