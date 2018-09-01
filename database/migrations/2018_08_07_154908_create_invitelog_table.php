<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInvitelogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('invitelog', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('promo_uid')->index('promo_uid')->comment('推广用户id');
			$table->integer('invite_uid')->index('invite_uid')->comment('邀请用户id');
			$table->dateTime('invite_date')->comment('邀请日期');
			$table->boolean('invite_type')->default(1)->comment('邀请类型 1: 会员 2 店铺');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('invitelog');
	}

}
