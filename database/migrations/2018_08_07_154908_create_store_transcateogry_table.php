<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStoreTranscateogryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('store_transcateogry', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name', 50)->comment('分類名稱');
			$table->string('description')->comment('描述');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('store_transcateogry');
	}

}
