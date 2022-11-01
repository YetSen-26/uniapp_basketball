<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWithdrawTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdraw', function (Blueprint $table) {
            $table->increments('id');
            $table->string('img_path')->default('')->comment('凭证');
            $table->integer('status')->comment('-1拒绝 0申请中  1通过');
            $table->integer('user_id')->comment('申请人id');
            $table->string('reason')->default('')->comment('原因');
            $table->dateTime('reject_time')->comment('拒绝原因');
            $table->dateTime('pass_time')->comment('通过原因');
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
        Schema::dropIfExists('withdraw');
    }
}
