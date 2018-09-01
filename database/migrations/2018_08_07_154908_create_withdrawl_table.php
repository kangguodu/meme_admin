<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWithdrawlTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('withdrawl', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('store_id')->nullable()->default(0)->comment('店鋪編號');
			$table->integer('uid')->default(0)->comment('店家id或者網紅id');
			$table->boolean('type')->default(1)->comment('類型，1店家提現2網紅提現');
			$table->decimal('amount', 16)->default(0.00)->comment('提現金額');
			$table->decimal('service_charge', 16)->default(0.00)->comment('手续费');
			$table->boolean('status')->default(0)->comment('提現狀態 0:提现中，1完成，2失败');
			$table->string('remark', 500)->nullable()->comment('提現備註');
			$table->string('bank_name')->comment('銀行名稱');
			$table->string('receiver_name', 50)->nullable()->comment('收款人');
			$table->string('bank_account', 50)->nullable()->comment('收款银行账户');
			$table->string('bank_phone', 50)->nullable()->comment('收款人电话');
			$table->string('handle_note', 500)->nullable()->default('')->comment('處理備註');
			$table->dateTime('handle_date')->nullable()->comment('处理時間');
			$table->dateTime('created_at')->comment('申請時間');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('withdrawl');
	}

}
