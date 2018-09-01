<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBillsCategoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bills_category', function(Blueprint $table)
		{
			$table->integer('category_id', true);
			$table->string('category_name', 191)->nullable();
			$table->integer('parent_id')->default(0)->comment('父级分类编号');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('bills_category');
	}

}
