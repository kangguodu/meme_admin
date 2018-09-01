<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRegionsStoreTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('regions_store', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('region_id')->comment('商圈編號');
			$table->integer('store_id')->nullable()->comment('店家編號');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('regions_store');
	}

}
