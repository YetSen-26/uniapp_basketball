<?php

namespace App\Admin\Actions\Grid;

use App\Admin\Forms\PassWithdrawForm;
use App\Models\Withdraw;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Widgets\Modal;

class PassWithdraw extends RowAction
{
    protected $title = '通过';

    public function render()
    {
        $form = PassWithdrawForm::make()->payload(['id' => $this->getKey()]);

        return Modal::make()
            ->lg()
            ->title($this->title)
            ->body($form)
            ->button($this->title);
    }

}
