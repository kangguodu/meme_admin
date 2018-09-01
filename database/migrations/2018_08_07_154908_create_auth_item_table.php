<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthItemTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('auth_item', function(Blueprint $table)
		{
			$table->string('name', 64);
			$table->smallInteger('type')->index('type');
			$table->text('description', 65535)->nullable();
			$table->string('rule_name', 64)->nullable()->index('rule_name');
			$table->binary('data', 65535)->nullable();
			$table->integer('created_at')->nullable();
			$table->integer('updated_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('auth_item');
	}

}
