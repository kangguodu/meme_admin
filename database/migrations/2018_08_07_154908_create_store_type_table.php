<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStoreTypeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('store_type', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name', 50)->comment('名稱');
			$table->boolean('published')->nullable()->default(1)->comment('发布');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('store_type');
	}

}
