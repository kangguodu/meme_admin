<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStoreAccountTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('store_account', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('store_id')->index('store_id_2');
			$table->decimal('business_income', 16)->default(0.00)->comment('营业收入');
			$table->decimal('credits_income', 16)->default(0.00)->comment('积分收入');
			$table->decimal('return_credits', 16)->default(0.00)->comment('可回贈的積分額度');
			$table->boolean('probability')->default(10)->comment('单笔消费回赠点数');
			$table->boolean('fixed_probability')->default(0)->comment('单笔固定回赠点数');
			$table->boolean('feature_probability')->default(0)->comment('修改回贈的回贈點數');
			$table->integer('feature_probability_time')->default(0)->comment('修改下次生效時間');
			$table->boolean('feature_fixed_probability')->default(0);
			$table->integer('feature_fixed_probability_time')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('store_account');
	}

}
