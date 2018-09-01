<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStoreTransTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('store_trans', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('store_id')->index('store_id')->comment('店鋪ID');
			$table->integer('trans_type')->index('trans_type')->comment('交易類型:1 收入 2:支出');
			$table->integer('trans_category')->default(1)->comment('交易分類');
			$table->string('trans_category_name', 50)->nullable()->comment('交易分類名稱');
			$table->string('trans_description')->comment('交易描述');
			$table->date('trans_date')->comment('交易日期');
			$table->dateTime('trans_datetime')->comment('交易時間');
			$table->decimal('amount', 16)->default(0.00)->comment('交易金額');
			$table->decimal('balance', 16)->default(0.00)->comment('異動前金額');
			$table->dateTime('created_at')->comment('創建時間');
			$table->integer('created_by')->comment('創建人');
			$table->string('created_name', 50)->comment('創建名稱');
			$table->string('custom_field1', 50)->comment('自定義字段1');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('store_trans');
	}

}
