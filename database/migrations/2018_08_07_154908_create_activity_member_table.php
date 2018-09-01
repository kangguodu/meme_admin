<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActivityMemberTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activity_member', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('activity_id');
			$table->integer('member_id')->default(0)->index('member_id')->comment('状态，1已读0未读');
			$table->boolean('status')->default(0)->comment('1已参加，0未参加');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('activity_member');
	}

}
