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
        Schema::create('cids_customer', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->integer('user_id')->nullable();
            $table->text('cid_value')->nullable();
            $table->integer('status')->default(1);
            $table->text('log')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cids_customer');
    }
};
