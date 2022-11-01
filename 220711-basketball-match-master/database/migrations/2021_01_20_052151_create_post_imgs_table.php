<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostImgsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_imgs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('post_id')->default('')->comment('发帖人ID');
            $table->string('img_path')->default('')->comment('图片地址');
            $table->string('sort')->default('0')->comment('排序ID');
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
        Schema::dropIfExists('post_imgs');
    }
}
