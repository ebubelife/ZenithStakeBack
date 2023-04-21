<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Members extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::create('members', function (Blueprint $table) {
            $table->id()->unique();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('email')->unique();
            $table->string('email_code');
            $table->boolean('email_verified')->default(false);
            $table->string('password');
            $table->string('phone')->unique();
            $table->string('phone_code')->nullable();
            $table->boolean('phone_verified')->default(false);
            $table->boolean('is_vendor')->default(false);
            $table->string('affiliate_id');
            $table->string('bank_account_name')->nullable();
            $table->string('bank')->nullable();
            $table->string('bank_account_number')->nullable();
            
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
        //
    }
}
