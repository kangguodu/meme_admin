<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImageSignApplyDetailTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('image_sign_apply_detail', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('image_sign_id');
			$table->integer('quantity')->default(0)->comment('数量');
			$table->integer('apply_id');
			$table->decimal('amount', 16)->nullable()->default(0.00)->comment('价格');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('image_sign_apply_detail');
	}

}
