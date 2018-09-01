<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBillsMonthTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bills_month', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('uid')->default(0)->index('uid');
			$table->string('bill_date', 10)->nullable()->index('bill_date');
			$table->integer('bill_year')->default(0)->comment('月账单年份');
			$table->integer('bill_month')->default(0)->comment('月账单月份');
			$table->decimal('income', 10)->comment('收入');
			$table->decimal('expenditure', 10)->comment('支出');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('bills_month');
	}

}
