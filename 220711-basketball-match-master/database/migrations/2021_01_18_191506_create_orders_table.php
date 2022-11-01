<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->default('0')->comment('下单人id');
            $table->integer('post_id')->default('0')->comment('需求id');
            $table->decimal('amount')->default('0')->comment('总价');
            $table->integer('person_num')->default('0')->comment('人数');
            $table->string('look_num')->default('')->comment('浏览数量');
            $table->string('look_amount')->default('')->comment('浏览总额度');
            $table->string('look_residue_amount')->default('')->comment('浏览剩余额度');
            $table->string('look_residue_num')->default('')->comment('浏览剩余次数');
            $table->string('share_num')->default('')->comment('分享宗次数');
            $table->string('share_amount')->default('')->comment('分享总额度');
            $table->string('share_residue_amount')->default('')->comment('分享剩余钱数');
            $table->string('share_residue_num')->default('')->comment('分享剩余次数');
            $table->string('share_rate')->default('')->comment('分享费率');
            $table->string('plat_rate')->default('')->comment('平台费率');
            $table->string('plat_amount')->default('')->comment('平台总额度');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
