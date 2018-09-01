<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePostsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('posts', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('title', 191)->nullable();
			$table->integer('rank')->default(0)->comment('排序');
			$table->text('content', 65535)->nullable();
			$table->integer('category_id')->default(0)->comment('分类编号');
			$table->timestamps();
			$table->integer('checked')->default(1)->comment('发布');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('posts');
	}

}
