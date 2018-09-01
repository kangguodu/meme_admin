<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCompanyBankTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('company_bank', function(Blueprint $table)
		{
			$table->smallInteger('id')->index('id_2');
			$table->string('bank_account_name')->comment('匯款賬戶');
			$table->string('bank_name')->comment('開戶銀行');
			$table->string('bank_branch')->comment('開戶支行');
			$table->string('bank_number')->comment('銀行代號');
			$table->string('bank_account')->comment('銀行賬號');
			$table->boolean('is_default')->index('is_default')->comment('默認，1是0否');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('company_bank');
	}

}
