<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImageSignApplyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('image_sign_apply', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('store_id')->default(0)->comment('店铺id');
			$table->string('other_remark', 500)->nullable();
			$table->timestamps();
			$table->boolean('status')->nullable()->default(1)->comment('处理状态 1:待处理 2: 处理中 3 处理完成 4 取消');
			$table->string('cancel_reason')->nullable()->comment('取消原因');
			$table->string('address')->nullable()->comment('寄送地址');
			$table->decimal('imagesign_carriage', 16)->nullable()->comment('运费');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('image_sign_apply');
	}

}
