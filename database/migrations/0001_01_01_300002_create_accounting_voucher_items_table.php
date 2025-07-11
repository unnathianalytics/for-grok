<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_voucher_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accounting_voucher_id')->constrained('accounting_vouchers')->onDelete('cascade');

            $table->string('avr_item_type', 1)->comment('C for Credit, D for Debit');
            $table->unsignedBigInteger('cr_account_id');
            $table->decimal('cr_amount', 15, 2);
            $table->unsignedBigInteger('dr_account_id');
            $table->decimal('dr_amount', 15, 2);
            $table->string('description')->nullable();
            $table->unsignedBigInteger('user')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_voucher_items');
    }
};
