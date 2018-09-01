<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStoreApplyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('store_apply', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name', 50)->comment('姓名');
			$table->string('province', 25)->default('台灣省');
			$table->string('city', 50)->nullable()->default('')->comment('城市');
			$table->string('address')->comment('地址');
			$table->string('phone', 25)->comment('电话');
			$table->string('email')->nullable();
			$table->string('company_name', 120)->comment('公司名称');
			$table->string('company_tax_no', 30)->comment('统一编号');
			$table->string('type_name', 50)->comment('營業類別');
			$table->boolean('status')->default(0)->comment('狀態，0申請中,1同意');
			$table->dateTime('created_at');
			$table->text('other', 65535)->comment('其他');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('store_apply');
	}

}
