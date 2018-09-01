<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStoreTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('store', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('store_id')->comment('店鋪編號');
			$table->integer('super_uid')->default(0)->comment('超級用戶id');
			$table->string('name', 150)->comment('名称');
			$table->string('branchname', 50)->nullable()->default('')->comment('分店名稱');
			$table->string('city', 50)->nullable()->default('')->comment('城市');
			$table->string('district', 50)->nullable()->default('')->comment('區');
			$table->string('address')->comment('地址');
			$table->string('phone', 50)->comment('电话');
			$table->string('email', 50)->nullable()->default('')->comment('邮箱');
			$table->string('company_name', 120)->comment('公司名称');
			$table->string('company_tax_no', 20)->comment('统一编号');
			$table->string('code')->default('')->comment('用于扫码下单的代码');
			$table->integer('type')->default(0)->comment('店家業態');
			$table->string('type_name', 50)->comment('業態名稱');
			$table->string('image')->nullable()->default('')->comment('廣告首圖');
			$table->boolean('service_status')->default(1)->comment('營業狀態，1營業0休息');
			$table->string('remark')->nullable()->default('')->comment('备注');
			$table->boolean('avg_cost_status')->nullable()->default(0)->comment('平均消费是否启用，1启用0否');
			$table->integer('avg_cost_low')->nullable()->default(0)->comment('平均消费');
			$table->integer('avg_cost_high')->default(0);
			$table->string('facebook', 50)->nullable()->default('')->comment('facebook连结');
			$table->string('instagram')->nullable()->default('')->comment('instagram连结');
			$table->string('google_keyword', 150)->nullable()->default('')->comment('google搜索关键字');
			$table->string('coordinate', 100)->nullable()->default('')->comment('坐标');
			$table->string('lat', 50)->nullable()->default('')->comment('緯度');
			$table->string('lng', 50)->nullable()->default('')->comment('經度');
			$table->dateTime('created_at');
			$table->enum('email_valid', array('unverified','verified'))->nullable();
			$table->integer('routine_holiday')->nullable()->default(0)->comment('例行休息日,0無非0有');
			$table->date('special_holiday')->nullable()->comment('特休日空無');
			$table->date('special_business_day')->nullable()->comment('特別營業日');
			$table->boolean('is_return')->nullable()->default(1)->comment('是否回贈，1是0否');
			$table->string('search_keyword')->nullable()->default('')->comment('搜索關鍵詞，用於搜索');
			$table->integer('recommend_rank')->nullable()->default(999999999)->comment('推薦排名,1第一2第二3第三，默認999999999不推薦');
			$table->string('description')->comment('關於店家介紹');
			$table->text('service', 65535)->comment('提供的服务');
			$table->index(['code','is_return'], 'code');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('store');
	}

}
