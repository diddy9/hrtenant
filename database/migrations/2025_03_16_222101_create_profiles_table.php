<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('emp_id', 191)->nullable();
            $table->string('avatar', 191)->nullable();
            $table->string('gender', 191)->nullable();
            $table->date('start_date')->nullable();
            $table->string('nhf_no', 191)->nullable();
            $table->unsignedBigInteger('pfa_id')->nullable();
            $table->string('rsa_pin_no', 191)->nullable();
            $table->string('grade', 191)->nullable();
            $table->string('r_address', 191)->nullable();
            $table->string('p_address', 191)->nullable();
            $table->string('title', 191)->nullable();
            $table->string('phone', 191)->nullable();
            $table->date('d_o_b')->nullable();
            $table->string('p_o_b', 191)->nullable();
            $table->string('nationality', 191)->nullable();
            $table->string('state_of_origin', 191)->nullable();
            $table->string('home_town', 191)->nullable();
            $table->string('local_govt', 191)->nullable();
            $table->string('marital_status', 191)->nullable();
            $table->string('religion', 191)->nullable();
            $table->string('name_of_spouse', 191)->nullable();
            $table->string('maiden_name', 191)->nullable();
            $table->string('spouse_phone', 191)->nullable();
            $table->string('address_of_spouse', 191)->nullable();
            $table->string('next_of_kin_ben', 191)->nullable();
            $table->string('relationship_ben', 191)->nullable();
            $table->string('address_ben', 191)->nullable();
            $table->string('tel_ben', 191)->nullable();
            $table->string('next_of_kin_em', 191)->nullable();
            $table->string('relationship_em', 191)->nullable();
            $table->string('address_em', 191)->nullable();
            $table->string('tel_em', 191)->nullable();
            $table->string('disability', 191)->nullable();
            $table->string('height', 191)->nullable();
            $table->string('weight', 191)->nullable();
            $table->string('blood_group', 191)->nullable();
            $table->string('genotype', 191)->nullable();
            $table->string('hobbies', 191)->nullable();
            $table->string('languages', 191)->nullable();
            $table->boolean('indebted')->default(0);
            $table->string('debt_details', 191)->nullable();
            $table->string('intention', 191)->nullable();
            $table->boolean('convict')->default(0);
            $table->string('crime_details', 191)->nullable();
            $table->string('bank_name', 191)->nullable();
            $table->string('account_no', 191)->nullable();
            $table->string('sort_code', 191)->nullable();
            $table->string('salary_basis', 191)->nullable();
            $table->decimal('salary', 15, 2)->nullable();
            $table->string('payment_type', 191)->nullable();
            $table->string('cv', 191)->nullable();
            $table->string('contract_letter', 191)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profiles');
    }
}
