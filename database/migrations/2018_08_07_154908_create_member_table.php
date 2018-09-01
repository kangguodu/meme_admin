<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMemberTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('member', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('phone', 50)->nullable()->comment('手機號碼');
			$table->string('zone')->nullable()->default('')->comment('區號');
			$table->string('password', 191)->nullable();
			$table->string('username')->nullable()->default('')->comment('姓名');
			$table->string('nickname', 50)->nullable();
			$table->string('email')->nullable()->default('')->comment('信箱');
			$table->boolean('gender')->nullable()->default(1)->comment('性别,1男2女');
			$table->string('avatar', 191)->nullable();
			$table->date('birthday')->nullable()->comment('生日');
			$table->string('id_card')->nullable()->default('')->comment('ID');
			$table->boolean('status')->nullable()->default(1)->comment('用户状态');
			$table->smallInteger('groupid')->nullable()->default(0)->comment('会员组groupid');
			$table->boolean('user_type')->default(1)->comment('认证类型 0  未认证 1 个人 2 網紅');
			$table->boolean('secure_status')->nullable()->default(0)->comment('安全碼開關');
			$table->string('secure_password', 191)->nullable()->comment('安全碼');
			$table->string('invite_code', 30)->nullable()->comment('推廣碼');
			$table->string('promo_code', 30)->nullable()->comment('綁定推廣碼');
			$table->integer('invite_count')->nullable()->default(0)->comment('邀請數量');
			$table->timestamps();
			$table->integer('number')->nullable()->default(0)->comment('是否新用户，0是其他不是');
			$table->string('token', 300)->nullable()->default('');
			$table->boolean('code_type')->default(1)->comment('綁定邀請碼類型，1網紅會員2店家');
			$table->boolean('honor')->default(0)->comment('网红头衔，0无1普通网红2校园大使');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('member');
	}

}
