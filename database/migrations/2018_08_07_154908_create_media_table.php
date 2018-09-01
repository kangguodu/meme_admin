<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMediaTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('media', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('company_name')->nullable()->default('')->comment('媒體單位');
			$table->string('username', 50)->nullable()->default('')->comment('聯絡人');
			$table->string('phone', 25)->nullable()->default('')->comment('聯絡手機');
			$table->string('report_content')->nullable()->default('')->comment('報道內容');
			$table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('media');
	}

}
