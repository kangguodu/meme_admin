<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMemberOrdersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('member_orders', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('uid')->default(0)->index('uid')->comment('用戶編號');
			$table->smallInteger('ugroupid')->default(1)->comment('用户组');
			$table->string('order_no', 80)->comment('返利單號');
			$table->decimal('cycle_point', 10)->comment('週期返利總積分');
			$table->decimal('cycle_percent', 10)->default(0.00)->comment('週期回贈率');
			$table->boolean('cycle_month');
			$table->integer('cycle_days')->default(0)->comment('週期返回天數');
			$table->dateTime('cycle_start')->nullable()->comment('週期回贈開始時間');
			$table->dateTime('cycle_end')->nullable()->comment('週期回贈結束時間');
			$table->boolean('cycle_status')->default(0)->comment('回贈狀態 0 待返利 1 返利中 2 回贈完畢 3 取消返利 4 返利異常');
			$table->boolean('status')->default(1)->comment('狀態:1有效 0 無效');
			$table->boolean('deleted')->nullable()->default(0)->comment('已删除');
			$table->dateTime('created_at')->default('0000-00-00 00:00:00')->comment('創建時間');
			$table->decimal('interest_remain', 16)->nullable()->default(0.00);
			$table->integer('cycle_days_remain')->nullable()->default(0);
			$table->decimal('interest_ever', 10)->nullable()->default(0.00);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('member_orders');
	}

}
