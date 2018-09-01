<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMemberCreditsLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('member_credits_log', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->boolean('type')->comment('類型 1:收入 2;:支出 3:');
			$table->string('trade_type', 20)->nullable();
			$table->date('date')->comment('日期');
			$table->dateTime('log_date')->comment('時間');
			$table->string('log_no', 100)->nullable();
			$table->decimal('amount', 10)->default(0.00)->comment('金額');
			$table->decimal('balance', 10)->default(0.00)->comment('異動前餘額');
			$table->boolean('status')->default(0)->comment('狀態');
			$table->string('remark', 191)->nullable();
			$table->integer('member_id')->default(0)->index('member_id');
			$table->integer('order_id')->nullable();
			$table->string('order_sn', 50)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('member_credits_log');
	}

}
