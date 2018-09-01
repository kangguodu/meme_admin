<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOpenHourRangeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('open_hour_range', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('store_id')->index('store_id')->comment('店铺id');
			$table->boolean('day_of_week');
			$table->time('open_time');
			$table->time('close_time');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('open_hour_range');
	}

}
