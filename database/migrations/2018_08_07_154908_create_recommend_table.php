<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRecommendTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('recommend', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('store_id')->index('store_id')->comment('店鋪id');
			$table->boolean('cat')->nullable()->default(1)->comment('分類，1蜜蜜推薦2鮮貨報馬仔3在地小吃...');
			$table->integer('created_at');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('recommend');
	}

}
