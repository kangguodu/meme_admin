<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImageSignTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('image_sign', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('title', 150)->nullable()->default('')->comment('立牌標題');
			$table->string('description')->nullable()->comment('立牌描述');
			$table->string('cover')->nullable()->comment('立牌封面圖片');
			$table->string('image_config', 1000)->nullable()->comment('立牌打印配置');
			$table->date('start_at')->nullable()->comment('開始日期');
			$table->date('end_at')->nullable()->comment('結束日期');
			$table->dateTime('created_at')->nullable()->comment('創建時間');
			$table->decimal('price', 10)->nullable()->default(0.00)->comment('价格');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('image_sign');
	}

}
