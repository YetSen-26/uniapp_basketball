<?php

namespace App\Admin\Actions\Grid;

use App\Models\Company;
use App\User;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\BatchAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ActiveCompany extends BatchAction
{
    /**
     * @return string
     */
    protected $title = '批量激活';

    protected $status = 0;
    protected $role_type = 0;

    public function __construct($title = null, $status = null, $roleType = 1)
    {
        $this->title = $title;
        $this->status = $status;
        $this->role_type = $roleType;
        parent::__construct($title);
    }

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        $keys = $this->getKey();

        // 获取请求参数
        $status = $request->get('status');
        $role_type = $request->get('role_type');
        User::query()->whereIn('id', $keys)->where(['role_type' => $role_type])->update(['status' => $status]);
        return $this->response()->success($status ? '激活成功' : '冻结成功')->refresh();
    }

    /**
     * @return string|array|void
     */
    public function confirm()
    {

        return $this->status ? "请确认激活！" : "请确认冻结";
    }

    /**
     * @param Model|Authenticatable|HasPermissions|null $user
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        return true;
    }

    /**
     * @return array
     */
    protected function parameters()
    {
        return [
            'status' => $this->status,
            'role_type' => $this->role_type,
        ];
    }
}
