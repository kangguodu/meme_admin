<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCollectionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('collection', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('store_id')->nullable()->comment('店鋪id');
			$table->string('store_name')->nullable()->comment('店鋪名');
			$table->integer('goods_id')->nullable()->comment('商品id');
			$table->integer('member_id')->index('member_id')->comment('會員id');
			$table->integer('created_at');
			$table->boolean('type')->default(0)->comment('类型，商品1，店铺0');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('collection');
	}

}
