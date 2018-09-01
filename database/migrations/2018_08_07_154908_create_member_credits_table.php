<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMemberCreditsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('member_credits', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('member_id')->index('member_id')->comment('用戶id');
			$table->decimal('total_credits', 10)->default(0.00)->comment('總積分');
			$table->decimal('grand_total_credits', 10)->default(0.00)->comment('累計返利');
			$table->decimal('wait_total_credits', 10)->default(0.00)->comment('待返利');
			$table->decimal('freeze_credits', 10)->default(0.00)->comment('冻结金额');
			$table->decimal('promo_credits', 10)->nullable()->default(0.00)->comment('推廣積分');
			$table->decimal('promo_credits_total', 10)->nullable()->default(0.00)->comment('獲得的所有積分');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('member_credits');
	}

}
