<?php


namespace App\Imports;

use App\Models\Company as DataModel;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithUpserts;

class ImUserFirstSheetImport implements WithBatchInserts, WithChunkReading, ToModel, WithUpserts
{
    private $round;

    public function __construct(int $round)
    {
        $this->round = $round;
    }

    public function uniqueBy()
    {
        return 'uniform_social_credit_code';
    }

    /**
     * @param array $row
     *
     * @return Model|Model[]|null
     */
    public function model(array $row)
    {
        if ($row[0] == '公司名称') {
            return null;
        }
        if ($row[11] == '') {
            return null;
        }
//        $d = DataModel::query()
////            ->where('registration_code', '=', $row[15])
//            ->where('uniform_social_credit_code', '=', $row[11])
////            ->orWhere('taxpayer_registration_number', '=', $row[14])
//            ->get();
//        if (count($d)) {
//            foreach ($d as $item) {
//                $item->delete();
//            }
//        }

        $row = array_map(function ($v) {
            if ($v == '-') {
                return '';
            }
            return $v;
        }, $row);


        $importData = [
            'company_name' => $row[0],
            'registration_status' => $row[1],
            'legal_person_name' => $row[2],
//            'registration_money' => str_replace('万', '', $row[3]),
//            'real_registration_money' => str_replace('万', '', $row[4]),
            'registration_money' => $row[3],
            'real_registration_money' => $row[4],
            'create_date' => $row[5],
            'check_date' => $row[6],
            'work_date_limit' => $row[7],
            'province_name' => $row[8],
            'city_name' => $row[9],
            'area_name' => $row[10],
            'uniform_social_credit_code' => $row[11],
            'taxpayer_registration_number' => $row[12],
            'registration_code' => $row[13],
            'organizing_code' => $row[14],
            'CBZZ' => empty($row[15]) ? 0 : $row[15],
            'company_type' => $row[16],
            'industry_involved' => $row[17],
            'used_name' => $row[18],
            'en_name' => '',
            'address' => $row[19],
            'year_address' => $row[20],
            'web_uri' => $row[21],
            'tel' => $row[22],
            'danger_tel' => $row[23],
            'more_tel' => $row[24],
            'email' => $row[25],
            'more_email' => $row[26],
            'business_scope' => $row[27],
        ];
        return new DataModel($importData);
    }

    //批量导入1000条
    public function batchSize(): int
    {
        return 100;
    }

    //以1000条数据基准切割数据
    public function chunkSize(): int
    {
        return 100;
    }
}
