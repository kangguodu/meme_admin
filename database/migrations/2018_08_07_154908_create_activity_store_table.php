<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActivityStoreTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activity_store', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('activity_id');
			$table->integer('store_id')->default(0)->index('store_id')->comment('状态，1已读0未读');
			$table->boolean('status');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('activity_store');
	}

}
