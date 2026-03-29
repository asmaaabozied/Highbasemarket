<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Model;

class UploadService
{
    public static function create(Account $account, string $uploadType, string $uploadPath, ?Model $linkable = null, string $status = 'pending'): Upload
    {
        return Upload::create([
            'upload_type'   => $uploadType,
            'upload_path'   => $uploadPath,
            'account_id'    => $account->id,
            'linkable_id'   => $linkable?->id ?? null,
            'linkable_type' => $linkable instanceof \Illuminate\Database\Eloquent\Model ? $linkable::class : null,
            'status'        => $status,
        ]);
    }
}
