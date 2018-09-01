<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePayStoreTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pay_store', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('store_id')->comment('付費店家id');
			$table->decimal('amount', 10)->comment('付費金額');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('pay_store');
	}

}
