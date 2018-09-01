<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBillsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bills', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('bills_no', 100)->nullable();
			$table->decimal('amount', 10)->nullable()->comment('金额');
			$table->string('bills_desc', 191)->nullable();
			$table->integer('category_id')->comment('分类编号');
			$table->boolean('bills_type')->default(0)->comment('流水类型');
			$table->integer('uid')->comment('当前用户');
			$table->integer('merchant_id')->nullable()->comment('商家id');
			$table->string('merchant_no', 100)->nullable();
			$table->integer('status')->default(0)->comment('交易状态');
			$table->string('payment_type', 20)->nullable();
			$table->integer('trade_uid')->nullable()->comment('交易用户id');
			$table->string('trade_nickname')->nullable();
			$table->integer('order_id')->nullable();
			$table->string('order_sn', 50)->nullable();
			$table->string('trade_avatar')->nullable();
			$table->dateTime('created_at')->comment('创建时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('bills');
	}

}
