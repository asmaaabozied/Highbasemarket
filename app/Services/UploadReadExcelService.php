<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class UploadReadExcelService
{
    public function findFileByName($fileName): ?string
    {
        $files = Storage::disk('public')->files('excel-files');

        foreach ($files as $file) {
            $baseName = pathinfo($file, PATHINFO_FILENAME);

            if ($baseName === $fileName) {
                return $file;
            }
        }

        return '';
    }

    public function deleteFileByName($fileName): ?string
    {

        $filePath = 'excel-files/'.$this->findFileByName($fileName);

        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        return null;
    }

    public function readExcelContentByFilePath($filePath): array
    {
        $products = [];
        $file     = str_replace(storage_path('app/public/'), '', $filePath);

        if (! empty($file) && Storage::disk('public')->exists($file)) {
            $data = Excel::toArray([], $filePath)[0];

            $keys = array_shift($data);

            $products = array_map(function ($row) use ($keys) {
                if (count($row) !== 0) {
                    return array_combine($keys, $row);
                }
            }, $data);

        }

        return $products;
    }
}
