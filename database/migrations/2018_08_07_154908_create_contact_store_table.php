<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateContactStoreTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('contact_store', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('member_id')->index('member_id')->comment('用家id');
			$table->integer('store_id')->index('store_id')->comment('店鋪id');
			$table->dateTime('created_at')->comment('最近时间');
			$table->integer('number')->default(0)->comment('笔数');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('contact_store');
	}

}
