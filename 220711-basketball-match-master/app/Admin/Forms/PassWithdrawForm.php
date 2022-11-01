<?php

namespace App\Admin\Forms;

use App\Models\Withdraw;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Contracts\LazyRenderable;
use Illuminate\Support\Facades\Log;

class PassWithdrawForm extends Form implements LazyRenderable
{
    use LazyWidget;
    protected $title = '通过';


    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        // 获取外部传递参数
        $id = $this->payload['id'] ?? null;

        // 表单参数
        $img_path = $input['img_path'] ?? '';

        if (!$img_path) {
            return $this->response()->error('凭据必须上传');
        }

        if (!$id) {
            return $this->response()->error('参数错误');
        }

        $m = Withdraw::query()->find($id);

        if (!$m) {
            return $this->response()->error('数据不存在');
        }
        Log::info(asset('/'));
        $m->update([
            'status' => 1,
            'img_path' => str_replace(asset('/'),'',$img_path),
            'pass_time' => date('Y-m-d H:i:s')
        ]);

        return $this->response()->success('操作成功')->refresh();
    }

    public function form()
    {
        $id = $this->payload['id'] ?? null;

        $this->image('img_path', '上传凭据')
            ->move('uploads/images')
            ->uniqueName()->autoUpload()
            ->required();
        $this->hidden('id')->attribute('id');
    }

    // 返回表单数据，如不需要可以删除此方法
    public function default()
    {
        $id = $this->payload['id'] ?? null;
        $m = Withdraw::query()->find($id);
        return [
            'img_path' => $m ? $m->img_path : '',
        ];
    }
}
