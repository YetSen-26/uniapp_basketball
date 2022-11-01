<?php

namespace App\Admin\Actions\Grid;

use App\Admin\Forms\RejectWithdrawForm;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Widgets\Modal;

class RejectWithdraw extends RowAction
{
    protected $title = '拒绝';

    public function render()
    {
        $form = RejectWithdrawForm::make()->payload(['id' => $this->getKey()]);

        return Modal::make()
            ->lg()
            ->title($this->title)
            ->body($form)
            ->button($this->title);
    }

}
