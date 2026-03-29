<?php

namespace App\Services;

use App\Models\EmployeeVisit;
use App\Models\EmployeeVisitComment;

class EmployeeVisitCommentService
{
    public function addComment(EmployeeVisit $visit, string $comment, $attachment = null): EmployeeVisitComment
    {
        $comment = $visit->comments()->create([
            'comment'     => $comment,
            'employee_id' => auth()->user()->userable_id,
            'branch_id'   => currentBranch()->id,
        ]);

        if ($attachment) {
            foreach ($attachment as $file) {
                $comment
                    ->addMedia($file)
                    ->toMediaCollection('comment_attachments');
            }
        }

        return $comment;

    }
}
