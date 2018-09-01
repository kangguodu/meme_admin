<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMemberGroupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('member_group', function(Blueprint $table)
		{
			$table->boolean('id')->primary();
			$table->string('name', 50)->comment('名稱');
			$table->string('description', 50)->nullable()->comment('描述');
			$table->dateTime('created_at');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('member_group');
	}

}
