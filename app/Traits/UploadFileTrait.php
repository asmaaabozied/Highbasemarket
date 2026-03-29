<?php

namespace App\Traits;

trait UploadFileTrait
{
    public function upload($file)
    {
        $customFileName = 'excel-'.auth()->user()->id.'.'.$file->getClientOriginalExtension();

        return $file->storeAs('public/excel-files', $customFileName);
    }

    public function read(string $fileName): string
    {
        return storage_path('app/public/'.$fileName);
    }
}
