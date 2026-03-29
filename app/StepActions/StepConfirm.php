<?php

namespace App\StepActions;

use App\Models\Action;
use App\Models\Step;
use App\Services\ConfirmService;
use App\Services\PercentagePaymentService;

class StepConfirm implements ActionStrategyInterface
{
    /**
     * @throws \Exception
     */
    public function update($step, $data): void
    {
        $confirmService = new ConfirmService;
        $confirm        = $confirmService->create($data);

        if ($confirmService->check($confirm->id, $step->confirmation)) {
            $step->update([
                'status' => Step::COMPLETE,
            ]);

            $newStep = $step->progress->steps()->where('status', Step::INACTIVE)->first();
            $quote   = $step->progress?->quoteDetail;

            (new PercentagePaymentService)->payment($quote, $newStep);

            $newStep?->update([
                'status' => Step::PENDING,
            ]);

            Action::query()->findOrFail($data['actionId'])->update(['status' => Action::APPROVED]);
        }
    }
}
