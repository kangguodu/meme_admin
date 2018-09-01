<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActivityTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activity', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('title')->nullable();
			$table->text('content', 65535)->nullable()->comment('内容，1文本2鏈接');
			$table->string('description')->nullable()->comment('描述');
			$table->boolean('type')->nullable()->comment('活动类型 1:文章 2 活动');
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('活动創建时间');
			$table->string('created_by', 30)->nullable();
			$table->dateTime('start_at')->nullable()->comment('活動開始時間');
			$table->dateTime('expire_at')->nullable()->comment('结束时间');
			$table->boolean('checked')->nullable()->default(1)->comment('發佈');
			$table->boolean('platform_type')->nullable()->index('platform_type')->comment('平台类型 1 普通 2 商家 3 网红');
			$table->string('posters_pictures')->nullable()->comment('图片');
			$table->boolean('discount')->nullable()->comment('活动折数/回赠');
			$table->string('url')->nullable()->default('')->comment('鏈接');
			$table->dateTime('show_time')->comment('推出時間');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('activity');
	}

}
