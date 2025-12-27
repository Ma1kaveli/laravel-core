<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fake_soft_models', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();

            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fake_soft_models');
    }
};
