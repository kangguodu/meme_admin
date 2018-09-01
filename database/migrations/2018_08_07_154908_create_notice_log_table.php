<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNoticeLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notice_log', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->boolean('platform_type')->comment('2：店铺，3：网红');
			$table->string('title');
			$table->string('description')->nullable();
			$table->boolean('type')->comment('推送类型：1系统，2活动');
			$table->string('content')->nullable();
			$table->string('url')->nullable();
			$table->integer('point_id')->nullable();
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('notice_log');
	}

}
