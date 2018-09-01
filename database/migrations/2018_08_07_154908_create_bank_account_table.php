<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBankAccountTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bank_account', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('member_id')->nullable()->default(0)->comment('會員編號');
			$table->string('bank_name')->nullable()->default('')->comment('銀行名稱');
			$table->string('receiver_name')->nullable()->default('')->comment('收款人');
			$table->string('bank_account')->nullable()->default('')->comment('賬號');
			$table->string('bank_phone', 50)->nullable()->default('')->comment('手機號');
			$table->boolean('status')->nullable()->default(1)->comment('是否使用1是0否');
			$table->dateTime('created_at');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('bank_account');
	}

}
