<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*Schema::create('leave__applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sid')->nullable()->constrained('users')->onDelete('set null'); // Supervisor
            $table->foreignId('reliver_id')->nullable()->constrained('users')->onDelete('set null'); // Reliever
            $table->foreignId('category_id')->constrained('leave_categories')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days');
            $table->string('status')->default('PENDING');
            $table->year('leave_year'); // Year when leave is applied
            $table->softDeletes(); // Soft delete
            $table->timestamps();

            // Foreign Key Constraint
            $table->foreign('tenant_id')->references('id')->on('hostnames')->onDelete('cascade');
        });*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave__applications');
    }
}
