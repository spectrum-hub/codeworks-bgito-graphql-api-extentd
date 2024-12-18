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
        Schema::create('cids_cart_shifts', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->integer('user_id')->nullable();
            $table->text('cid_value')->nullable();
            $table->integer('cart_id')->nullable();
            $table->integer('cid_id')->nullable();
            $table->integer('status')->default(1);
            $table->text('log')->nullable();

            $table->timestamps();
        });
    }

    /** 
     * password_resets
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cids_cart_shifts');
    }
};
