<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateHotWordTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('hot_word', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('hot_word')->default('')->comment('热搜词');
			$table->integer('number')->default(1)->comment('搜索次数');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('hot_word');
	}

}
