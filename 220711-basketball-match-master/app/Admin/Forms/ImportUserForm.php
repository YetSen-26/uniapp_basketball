<?php


namespace App\Admin\Forms;

use App\Models\ExcelImportLog;
use Dcat\Admin\Widgets\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Imports\ImUserDataExcel;
use Maatwebsite\Excel\Validators\ValidationException;
use Throwable;

class ImportUserForm extends Form
{
    public function handle()
    {
        try {
            $file = request('file');
            $e = new ExcelImportLog();
            $e->file_name = $file;
            $e->created_at = date("Y-m-d H:i:s");
            $e->updated_at = date("Y-m-d H:i:s");
            $e->save();
            //获取所有数据
            return $this->response()
                ->success('导入成功')
                ->refresh();
        } catch (ValidationException $validationException) {
            Log::info($validationException);
            return Response::withException($validationException);
        } catch (Throwable $throwable) {
            Log::error($throwable);
            return $this->response()->error('上传失败')->refresh();
        }
    }

    public function form()
    {
        $this->file('file', '上传数据（Excel）')
            ->rules('required', ['required' => '文件不能为空'])
            ->disk('local')
            ->uniqueName()
            ->maxSize(307200);
    }

}
