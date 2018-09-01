<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGoodsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('goods', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('store_id')->comment('店铺id');
			$table->string('goods_name')->nullable()->comment('食品名称');
			$table->string('image')->nullable()->comment('图片');
			$table->integer('price')->nullable()->default(0)->comment('原价');
			$table->integer('prom_price')->nullable()->default(0)->comment('优惠价');
			$table->integer('created_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('goods');
	}

}
