<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMemberBankAccountTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('member_bank_account', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('member_id')->index('member_id');
			$table->string('bank_name')->nullable();
			$table->string('bank_account')->nullable();
			$table->string('id_card')->nullable();
			$table->string('bank_branch')->nullable();
			$table->string('bank_phone')->nullable();
			$table->string('bank_account_name')->nullable();
			$table->timestamps();
			$table->primary(['id','member_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('member_bank_account');
	}

}
