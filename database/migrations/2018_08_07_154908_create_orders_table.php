<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrdersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orders', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('order_no')->comment('序号');
			$table->string('order_sn', 20)->nullable()->comment('订单序号');
			$table->string('month', 50)->nullable()->default('')->comment('月份2018-07');
			$table->date('date')->comment('下單日期');
			$table->integer('store_id')->index('store_id')->comment('店鋪id');
			$table->string('store_name')->comment('店铺名称');
			$table->integer('member_id')->index('member_id')->comment('用家id');
			$table->integer('amount')->default(0)->comment('消费总金額');
			$table->decimal('credits', 10)->default(0.00)->comment('积分折扣');
			$table->integer('coupons_id')->nullable()->default(0)->comment('优惠券id');
			$table->decimal('coupons_money')->nullable()->default(0.00)->comment('優惠券金額');
			$table->decimal('prate')->nullable()->default(0.00)->comment('平臺回贈');
			$table->decimal('mfixedrate')->nullable()->default(0.00)->comment('會員固定回贈');
			$table->decimal('mrate')->default(0.00)->comment('會員回贈');
			$table->decimal('promoreate')->nullable()->default(0.00)->comment('推廣回贈');
			$table->boolean('status')->default(0)->comment('狀態,--1已取消，0待處理,1已完成，2退货');
			$table->dateTime('checkout_at')->nullable()->comment('处理时间');
			$table->integer('checkout_user_id')->nullable()->comment('结账人员');
			$table->dateTime('refund_at')->nullable()->comment('退貨時間');
			$table->integer('refund_user_id')->nullable()->comment('退货人员');
			$table->timestamps();
			$table->integer('updated_by')->default(0)->index('updated_by');
			$table->integer('number')->nullable()->comment('今日第幾單');
			$table->boolean('is_evaluate')->default(0)->comment('是否已評價1是0否');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('orders');
	}

}
