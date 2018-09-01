<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRegionsListTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('regions_list', function(Blueprint $table)
		{
			$table->integer('region_id', true)->comment('區域編號');
			$table->string('region_name', 50)->comment('區域名稱');
			$table->boolean('region_type')->default(0)->comment('区域类型');
			$table->integer('parent_id')->default(0)->comment('父区域');
			$table->boolean('is_free')->default(0)->comment('1包郵0不包郵');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('regions_list');
	}

}
