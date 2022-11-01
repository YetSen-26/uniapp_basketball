<?php

namespace App\Admin\Actions\Grid;

use App\Admin\Forms\ProjectGradeEditForm;
use App\Admin\Forms\ProjectGradeEditFormByUncomment;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Widgets\Modal;


class ProjectGradeEditByUncomment extends RowAction
{
    /**
     * @return string
     */
	protected $title = '评审编辑';

    public function render()
    {
        // 实例化表单类并传递自定义参数
        $form = ProjectGradeEditFormByUncomment::make()->payload(['id' => $this->getKey()]);

        return Modal::make()
            ->lg()
            ->title($this->title)
            ->body($form)
            ->button($this->title);
    }

}
