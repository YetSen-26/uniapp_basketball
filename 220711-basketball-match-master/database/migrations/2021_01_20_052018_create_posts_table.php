<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->default('')->comment('标题');
            $table->text('content')->comment('内容');
            $table->integer('type')->default('1')->comment('1普通 2推广');
            $table->integer('status')->comment('0待帮助  1帮助中 2已结束');
            $table->integer('category_id')->comment('分类ID');
            $table->integer('city_id')->comment('城市ID');
            $table->integer('province_id')->comment('省份ID');
            $table->integer('user_id')->comment('发帖人ID');
            $table->integer('help_user_id')->comment('帮助者ID');
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
        Schema::dropIfExists('posts');
    }
}
