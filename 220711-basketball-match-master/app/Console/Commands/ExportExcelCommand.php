<?php

namespace App\Console\Commands;

use App\Exports\ProjectExport;
use App\Imports\ImUserDataExcel;
use App\Models\ExcelExportLog;
use App\Models\ExcelImportLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ExportExcelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cus:export-excel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $logs = ExcelExportLog::query()
            ->where(['status' => 0])
            ->where('retry_cnt', '<', 1)
            ->get();
        $currentLog = '';
        try {
            foreach ($logs as $log) {
                Log::info('开始导出 id:' . $log->id);
                $currentLog = $log;
                (new ProjectExport($log->query_content))->store($log->file_name, 'export');
                Log::info($log);
                $currentLog->status = 1;
                $currentLog = '';
                Log::info('导出结束');
            }
        } catch (\Exception $exception) {
            $currentLog->retry_cnt += 1;
            if ($currentLog->retry_cnt > 4) {
                $currentLog->status = -1;
                $currentLog->msg = '导出失败';
            }
            $currentLog->save();
            Log::error($exception);
            Log::info('导出失败');
        }
//        if ($log) {
//            Log::info('开始导入 id:' . $log->id);
//            $log->status = 1;
//            $log->save();
//            try {
//                Excel::import(new ImUserDataExcel(time()), storage_path('app/' . $log->file_name));
//                $log->status = 2;
//                $log->msg = 'success';
//                $log->save();
//            } catch (\Exception $e) {
//                $log->status = -1;
//                $log->msg = $e->getMessage();
//                $log->save();
//                Log::error($e);
//            }
//            Log::info('导入结束');
//        }
        return 0;
    }
}
