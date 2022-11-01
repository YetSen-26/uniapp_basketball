<?php

namespace App\Admin\Forms;

use App\Models\System\SystemDict;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Arr;

class AdminSetting extends Form implements LazyRenderable
{
    use LazyWidget;

    /**
     * 处理表单请求.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
//        Log::info('1',[$input]);
//        Log::info('2',[Arr::dot($input)]);
        foreach (Arr::dot($input) as $k => $v) {
            $this->update($k, $v);
        }

        return $this->response()->success('设置成功');
    }

    /**
     * 构建表单.
     */
    public function form()
    {
        $this->number('can_read_grade', '用户查看成绩')
            ->options([
                'no' => '否'
                , 'yes' => '是'
            ])
            ->default('yes');
        $this->radio('can_read_grade', '用户查看成绩')
            ->options([
                'no' => '否'
                , 'yes' => '是'
            ])
            ->default('yes');
    }

    /**
     * 设置接口保存成功后的回调JS代码.
     *
     * 1.2秒后刷新整个页面.
     *
     * @return string|void
     */
    public function savedScript()
    {
        return <<<'JS'
    if (data.status) {
        setTimeout(function () {
          location.reload()
        }, 1200);
    }
JS;
    }

    /**
     * 返回表单数据.
     *
     * @return array
     */
    public function default()
    {
        $s = SystemDict::query()->select()->get();
        $arr = [];
        foreach ($s as $i) {
            $arr[$i->key] = $i->value;
        }
        return $arr;
//        return user_admin_config();
    }

    /**
     * 更新配置.
     *
     * @param string $key
     * @param string $value
     */
    protected function update($key, $value)
    {
        $s = SystemDict::query()->where(['key' => $key])->firstOrNew();
        $s->value = $value;
        $s->save();
//        user_admin_config([$key => $value]);
    }
}
