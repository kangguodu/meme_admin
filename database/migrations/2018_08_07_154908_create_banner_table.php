<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBannerTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('banner', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->boolean('type')->nullable()->comment('活動類型 1:活動 2:店鋪');
			$table->integer('store_id')->nullable()->comment('type爲2，需要填入店鋪編號');
			$table->integer('region_id')->nullable();
			$table->string('image_url')->nullable()->comment('图片');
			$table->boolean('use_type')->nullable()->default(1)->comment('首頁大banner為1,小banner為2');
			$table->string('url')->nullable()->comment('鏈接');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('banner');
	}

}
