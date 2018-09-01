<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePermissionRoleUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('permission_role_user', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('role_id')->default(0)->comment('角色id');
			$table->integer('rules_id')->nullable()->default(0)->comment('規則id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('permission_role_user');
	}

}
