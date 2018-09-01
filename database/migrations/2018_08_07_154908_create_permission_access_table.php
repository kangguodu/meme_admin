<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePermissionAccessTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('permission_access', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('role_id')->default(0)->comment('角色編號');
			$table->integer('user_id')->default(0)->comment('用戶編號');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('permission_access');
	}

}
