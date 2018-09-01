<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMemberHotWordTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('member_hot_word', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('member_id')->comment('會員id');
			$table->integer('hot_word_id')->comment('熱搜詞id');
			$table->integer('num')->nullable()->default(0)->comment('我的搜索次數');
			$table->integer('created_at')->nullable()->comment('搜索时间');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('member_hot_word');
	}

}
