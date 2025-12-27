<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fake_repository_models', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('value')->nullable();
            $table->boolean('is_superadministrator')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fake_repository_models');
    }
};
