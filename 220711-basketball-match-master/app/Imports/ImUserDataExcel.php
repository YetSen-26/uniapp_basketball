<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ImUserDataExcel implements WithMultipleSheets
{
    private $round;

    public function __construct(int $round)
    {
        $this->round = $round;
    }

    public function sheets(): array
    {
        set_time_limit(0);
        return [
            new ImUserFirstSheetImport($this->round),
        ];
    }
}
