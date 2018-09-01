<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCooperationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cooperation', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('username', 50)->default('')->comment('姓名');
			$table->string('phone', 25)->default('')->comment('電話');
			$table->string('company_name', 120)->default('')->comment('公司名稱');
			$table->string('company_tax_no', 30)->default('')->comment('統一編號');
			$table->string('type_name', 50)->default('')->comment('合作類別');
			$table->string('direction')->default('')->comment('合作方向');
			$table->boolean('status')->nullable()->default(0)->comment('狀態，0申請中1已處理');
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
		Schema::drop('cooperation');
	}

}
