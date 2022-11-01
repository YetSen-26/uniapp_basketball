<?php

namespace App\Admin\Actions\Grid;

use App\Admin\Forms\ImportUserForm;
use App\Models\Company;
use App\User;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Admin;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AllActive extends AbstractTool
{
    /**
     * @return string
     */
    protected $title = '全体激活';

    protected $status = 0;

    protected $style = 'btn btn-outline-info btn-outline';
    protected $role_type = 0;

    public function __construct($title = null, $status = null,$roleType=null)
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
        User::query()->where('status', $status==1 ? 0 : 1)
            ->where(['role_type'=>$role_type])
            ->update(['status' => $status]);
        return $this->response()->success($status ? '全体激活成功' : '全体冻结成功')->refresh();
    }

    /**
     * @return string|void
     */
    protected function href()
    {
        // return admin_url('auth/users');
    }

    /**
     * @return string|array|void
     */
    public function confirm()
    {
        return $this->status ? "请确认全体激活！" : "请确认全体冻结";
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
