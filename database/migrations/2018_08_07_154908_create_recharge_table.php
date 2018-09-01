<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRechargeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('recharge', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('member_id')->nullable();
			$table->decimal('money', 10)->nullable()->comment('充值金額');
			$table->smallInteger('type')->nullable();
			$table->smallInteger('point_id')->nullable()->comment('指向類型');
			$table->integer('created_at')->nullable()->comment('充值時間');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('recharge');
	}

}
