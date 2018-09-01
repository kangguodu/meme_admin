<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTagTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tag', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name')->default('')->comment('名稱');
			$table->boolean('type')->default(1)->comment('類型，1你怎麼了2想找什麼');
			$table->boolean('status')->nullable()->default(1)->comment('是否使用,0否1是');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tag');
	}

}
