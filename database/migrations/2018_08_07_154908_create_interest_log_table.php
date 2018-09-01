<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInterestLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('interest_log', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('uid')->default(0)->index('uid');
			$table->decimal('amount', 10)->default(0.00);
			$table->string('interest_date', 8);
			$table->date('created_at');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('interest_log');
	}

}
