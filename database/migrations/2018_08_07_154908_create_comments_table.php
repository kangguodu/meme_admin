<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCommentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('comments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('store_id')->nullable()->index('store_id')->comment('店鋪id');
			$table->integer('order_id')->comment('訂單id');
			$table->integer('member_id')->index('member_id')->comment('用家id');
			$table->string('content')->comment('評價內容');
			$table->boolean('level')->nullable()->default(1)->comment('評價等級，1滿意2普通3不滿意');
			$table->boolean('is_reply')->nullable()->default(0)->comment('是否回復，1是0否');
			$table->string('reply_content')->nullable()->default('')->comment('回復內容');
			$table->integer('parent_id')->default(0)->comment('父回复id');
			$table->string('nickname')->nullable()->default('')->comment('會員暱稱');
			$table->string('image', 500)->nullable()->default('')->comment('圖片');
			$table->timestamps();
			$table->integer('updated_by')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('comments');
	}

}
