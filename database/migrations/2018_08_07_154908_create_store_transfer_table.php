<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStoreTransferTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('store_transfer', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('store_id')->comment('店铺id');
			$table->date('transfer_date')->comment('转账日期');
			$table->string('accounts_no', 30)->comment('银行卡末五位');
			$table->decimal('amount', 16)->nullable()->comment('汇款金额');
			$table->string('attachment')->nullable()->comment('附件');
			$table->enum('status', array('completed','refunded','failed','cancelled','processing','pending'))->default('pending')->comment('处理状态');
			$table->integer('created_by')->nullable()->comment('申请人');
			$table->timestamps();
			$table->integer('updated_by')->nullable()->comment('后台审批用户id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('store_transfer');
	}

}
