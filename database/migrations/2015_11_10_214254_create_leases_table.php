<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leases', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid')->comment('Shown publicly as the leaseId');
            $table->integer('user_id')->unsigned();
            $table->integer('resource_id')->unsigned();
            $table->string('duration')->comment = "Lease duration in minutes. If created_at + this is less than expires_at, lease was terminated early";
            $table->dateTime('expires_at')->comment = "Lease expiration date. If user terminates the lease early, it's gets updated to NOW()";
            $table->timestamps();

            $table->foreign('user_id')->on('users')
                ->references('id')->onDelete('cascade');

            $table->foreign('resource_id')->on('resources')
                ->references('id')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('leases');
    }
}
