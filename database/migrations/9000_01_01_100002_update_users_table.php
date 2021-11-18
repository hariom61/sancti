<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function (Blueprint $table) {
			if (!Schema::hasColumn('users', 'email_verified_at')) {
				$table->timestamp('email_verified_at')->nullable(true);
			}
			if (!Schema::hasColumn('users', 'code')) {
				$table->string('code', 128)->unique()->nullable(true);
			}
			if (!Schema::hasColumn('users', 'ip')) {
				$table->string('ip')->nullable(true);
			}
			if (!Schema::hasColumn('users', 'deleted_at')) {
				$table->softDeletes();
			}
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn(['code', 'ip']);
		});
	}
}
