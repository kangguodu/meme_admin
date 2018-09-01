<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNoticeMemberTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notice_member', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('notice_id');
			$table->integer('member_id');
			$table->boolean('status')->nullable()->default(1)->comment('狀態，1已讀2未讀');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('notice_member');
	}

}
