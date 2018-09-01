<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCouponsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('coupons', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('member_id')->comment('用家id');
			$table->integer('store_id')->nullable()->default(0);
			$table->decimal('money', 6)->comment('抵用金额/积分');
			$table->boolean('discount')->nullable()->comment('折数，94为94折');
			$table->integer('activity_id')->nullable()->comment('活动id');
			$table->string('acvitity_name')->nullable()->default('')->comment('活动名称');
			$table->boolean('type')->nullable()->default(2)->comment('类型，1通用2店鋪');
			$table->integer('start_at')->nullable();
			$table->integer('expire_time')->nullable()->comment('过期日期');
			$table->boolean('status')->nullable()->default(-1)->comment('状态，1可使用0已删除-1未領取');
			$table->integer('conditions')->default(0)->comment('消費金額滿足條件');
			$table->boolean('use_type')->nullable()->default(2)->comment('1立即2下單用3禮物');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('coupons');
	}

}
