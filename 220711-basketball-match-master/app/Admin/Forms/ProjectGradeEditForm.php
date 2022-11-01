<?php

namespace App\Admin\Forms;

use App\Models\ProjectGrade;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;

class ProjectGradeEditForm extends Form implements LazyRenderable
{
    use LazyWidget;

    // 使用异步加载功能

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
        $grade = $input['grade'] ?? null;

        if (!$id) {
            return $this->response()->error('参数错误');
        }

        $m = ProjectGrade::query()->find($id);

        if (!$m) {
            return $this->response()->error('未查找到数据');
        }

        $m->update([
            'grade' => $grade,
            'note' => $input['note'],
        ]);

        return $this->response()->success('操作成功')->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->decimal('grade', '成绩');
        $this->textarea('note', '意见');
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        $id = $this->payload['id'] ?? null;
        $m = ProjectGrade::query()->find($id);

        return [
            'grade' => $m->grade,
            'note' => $m->note,
        ];
    }
}
