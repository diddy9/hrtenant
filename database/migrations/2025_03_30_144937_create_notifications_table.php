<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('mid');
            $table->string('sender');
            $table->string('receiver'); 
            $table->string('type'); 
            $table->text('message');  
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
        Schema::dropIfExists('notifications');
    }
}
