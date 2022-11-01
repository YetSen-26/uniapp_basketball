<?php

namespace App\Admin\Forms;

use App\Models\Withdraw;
use App\User;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Contracts\LazyRenderable;

class RejectWithdrawForm extends Form implements LazyRenderable
{
    use LazyWidget;

    protected $title = '拒绝';


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
        $reason = $input['reason'] ?? '';

        if (!$reason) {
            return $this->response()->error('拒绝原因必填');
        }

        if (!$id) {
            return $this->response()->error('参数错误');
        }

        $m = Withdraw::query()->find($id);

        if (!$m) {
            return $this->response()->error('数据不存在');
        }

        if ($m->status != 0) {
            return $this->response()->error('数据状态错误');
        }

        $m->update([
            'status' => -1,
            'reason' => $reason,
            'reject_time' => date('Y-m-d H:i:s')
        ]);

        $user = User::query()->find($m->user_id);
        $user->money += $m->money;
        $user->save();

        return $this->response()->success('操作成功')->refresh();
    }

    public function form()
    {
        $this->text('reason', '拒绝原因')
            ->required();
        $this->hidden('id')->attribute('id');
    }

    // 返回表单数据，如不需要可以删除此方法
    public function default()
    {
        $id = $this->payload['id'] ?? null;
        $m = Withdraw::query()->find($id);
        return [
            'reason' => $m ? $m->reason : '',
        ];
    }
}
