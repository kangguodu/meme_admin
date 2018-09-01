<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStoreBannerTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('store_banner', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('store_id')->nullable()->index('store_id')->comment('店鋪id');
			$table->string('image')->nullable()->default('')->comment('图片');
			$table->integer('rank')->nullable()->default(0)->comment('权重');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('store_banner');
	}

}
