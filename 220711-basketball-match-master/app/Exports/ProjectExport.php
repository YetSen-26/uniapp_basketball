<?php

namespace App\Exports;

use Dcat\Admin\Grid\Exporters\AbstractExporter;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProjectExport extends AbstractExporter implements WithHeadings, WithColumnFormatting, WithColumnWidths, WithMapping, FromCollection
{
    use Exportable;

    protected $fileName = '报名信息导出';
    protected $titles = [];

    public function __construct()
    {
        $this->fileName = $this->fileName . '_' . date('YMDHI') . '.xlsx';//拼接下载文件名称
        $this->titles = [
            'product_name' => '节目名称',
            'category_name' => '参演项目',
            'level' => '参赛组别',
            'person_cnt' => '参赛人数',
            'teacher_name' => '指导老师',
            'tf_creator' => '是否原创',
            'user_nickname' => '参赛人昵称',
            'user_mobile' => '手机号',
            'avg_grade' => '平均分',
            'created_at' => '报名时间',
        ];
        parent::__construct();
    }

    public function export()
    {
        $this->download($this->fileName)->prepare(request())->send();
        exit;
    }

    public function collection()
    {
        return collect($this->buildData());
    }

    public function headings(): array
    {
        return $this->titles();
    }

    public function map($row): array
    {
        return [
            $row['product_name'],
            $row['category_name'],
            $row['level'],
            $row['person_cnt'],
            $row['teacher_name'],
            $row['tf_creator'] == 0 ? '是' : '否',
            $row['user_nickname'],
            $row['user_mobile'],
            $row['avg_grade'],
            $row['created_at'],
        ];
    }


    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 30,
            'C' => 30,
            'D' => 30,
            'E' => 30,
            'F' => 30,
            'G' => 30,
            'H' => 30,
            'I' => 30,
            'J' => 30,
            'K' => 30,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_TEXT,
            'K' => NumberFormat::FORMAT_TEXT,
            'L' => NumberFormat::FORMAT_TEXT,
            'M' => NumberFormat::FORMAT_TEXT,

            'N' => NumberFormat::FORMAT_TEXT,
            'O' => NumberFormat::FORMAT_TEXT,
            'P' => NumberFormat::FORMAT_TEXT,
            'Q' => NumberFormat::FORMAT_TEXT,
            'R' => NumberFormat::FORMAT_TEXT,
            'S' => NumberFormat::FORMAT_TEXT,
            'T' => NumberFormat::FORMAT_TEXT,
            'U' => NumberFormat::FORMAT_TEXT,
            'V' => NumberFormat::FORMAT_TEXT,
            'W' => NumberFormat::FORMAT_TEXT,
            'X' => NumberFormat::FORMAT_TEXT,
            'Y' => NumberFormat::FORMAT_TEXT,
            'Z' => NumberFormat::FORMAT_TEXT,
            'AA' => NumberFormat::FORMAT_TEXT,
            'AB' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
