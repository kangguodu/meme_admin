<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStoreDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('store_data', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('store_id');
			$table->decimal('level', 2, 1)->nullable()->default(0.0)->comment('評分');
			$table->integer('number')->nullable()->default(0)->comment('搜索人氣次數');
			$table->integer('collect_number')->nullable()->default(0)->comment('收藏數');
			$table->integer('comment_number')->nullable()->default(0)->comment('評論數');
			$table->integer('click_number')->nullable()->default(0)->comment('點擊數');
			$table->integer('order_number')->nullable()->default(0)->comment('人氣次數');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('store_data');
	}

}
