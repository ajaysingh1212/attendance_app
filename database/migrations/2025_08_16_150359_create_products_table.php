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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->unsignedBigInteger('company_id')->nullable(); // from add_companies
            $table->unsignedBigInteger('category_id')->nullable(); // from product_categories
            
            // Basic product info
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique(); // stock keeping unit
            $table->text('description')->nullable();
            
            // Pricing & stock
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount', 8, 2)->nullable();
            $table->integer('quantity')->default(0);
            
            // Extra info
            $table->string('item_code')->nullable();
            $table->string('hsn_code')->nullable();
            $table->boolean('status')->default(true);
            
            $table->timestamps();

            // Foreign keys (optional)
            $table->foreign('company_id')->references('id')->on('add_companies')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
