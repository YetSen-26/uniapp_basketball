<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->default('tg_rate')->comment('1推广费率  2分享费率  3浏览费率   4信息发布规范 5使用说明 6合作伙伴  7关于我们');
            $table->string('desc')->default('推广费率')->comment('描述');
            $table->text('value');
            $table->string('value_type')->default('1')->comment('1字符串 2富文本');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
