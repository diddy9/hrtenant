<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancialYearsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financial_years', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('start_date'); // Format: MM-DD
            $table->string('end_date');   // Format: MM-DD
            $table->decimal('carryover_percentage', 5, 2)->default(10); // default 10%
            $table->softDeletes(); // Soft delete
            $table->timestamps();

            // Foreign Key Constraint
            $table->foreign('tenant_id')->references('id')->on('hostnames')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('financial_years');
    }
}
