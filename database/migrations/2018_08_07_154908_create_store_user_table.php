<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStoreUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('store_user', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('store_id')->index('store_id')->comment('店铺id');
			$table->string('nickname', 50)->nullable()->default('')->comment('名字');
			$table->string('email', 50)->nullable()->comment('信箱');
			$table->string('mobile', 20)->comment('手機號碼');
			$table->string('zone', 10)->nullable()->comment('区号');
			$table->string('password', 150)->nullable()->default('')->comment('密码');
			$table->enum('permission', array('ALL','ONLYSEE','NONE'))->nullable();
			$table->char('email_status', 10)->nullable()->comment('信箱驗證狀態');
			$table->string('token', 300)->nullable();
			$table->string('gender', 10);
			$table->boolean('super_account')->default(0);
			$table->string('position', 30)->comment('職位');
			$table->text('menus', 65535)->comment('菜單');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('store_user');
	}

}
