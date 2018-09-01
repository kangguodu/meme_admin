<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStoreBankAccountTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('store_bank_account', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('store_id')->nullable()->default(0)->index('store_id')->comment('店家編號');
			$table->string('bank_name')->nullable()->default('')->comment('銀行名稱');
			$table->string('receiver_name')->nullable()->default('')->comment('收款人');
			$table->string('bank_account')->nullable()->default('')->comment('賬號');
			$table->string('bank_phone', 50)->nullable()->default('')->comment('手機號');
			$table->string('branch_name')->nullable();
			$table->string('region')->nullable();
			$table->boolean('status')->nullable()->default(1)->comment('是否使用1是0否');
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('store_bank_account');
	}

}
