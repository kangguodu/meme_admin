<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVerificationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('verification', function(Blueprint $table)
		{
			$table->integer('id', true)->comment('id');
			$table->string('verification_account', 15)->nullable()->index('verification_account');
			$table->boolean('verification_type')->nullable()->default(1)->comment('验证类型(1:phone 2:email)');
			$table->string('verification_code', 10)->nullable()->index('verification_code');
			$table->integer('send_at')->nullable()->comment('发送时间');
			$table->string('zone', 20)->nullable()->default('')->comment('區號');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('verification');
	}

}
