<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNoticeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notice', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('title')->default('')->comment('主題，標題');
			$table->string('description')->nullable()->default('')->comment('簡短描述');
			$table->boolean('type_id')->index('type_id')->comment('類型，1主題活動2錢包更新3系統更新');
			$table->text('content', 65535)->nullable()->comment('內容');
			$table->string('icon')->nullable()->default('')->comment('圖片');
			$table->string('url')->nullable()->default('')->comment('鏈接');
			$table->integer('point_id')->nullable()->comment('指向活動或訂單獲取其他id');
			$table->integer('member_id')->nullable()->comment('會員id');
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
		Schema::drop('notice');
	}

}
