<?php

namespace App\Services;

use App\Models\ExceptionAttribute;
use App\Models\Plan;
use App\Models\PlanException;

class PlanExceptionService
{
    public function create(array $data): array
    {
        $exceptionable_ids = [];
        $attribute_ids     = [];

        foreach ($data['exceptions'] as $exception) {
            $exceptionables = collect($exception['exceptionables'])
                ->map(fn (array $data) => PlanException::query()->updateOrCreate(['id' => $data['id'] ?? null], [...$data, 'name' => $exception['exception_name']]));

            $attribute = ExceptionAttribute::query()->updateOrCreate(['id' => $exception['attribute_id'] ?? null], [
                'attributes' => $exception['attributes'],
            ]);

            $attribute->exceptionables()->syncWithoutDetaching($exceptionables->pluck('id'));

            $exceptionable_ids[] = $exceptionables->pluck('id')->toArray();

            $attribute_ids[] = $attribute->id;
        }

        $ids = collect($exceptionable_ids)->flatten()->toArray();

        return ['ids' => $ids, 'exceptions' => $this->getExceptionsByPlanId(0, 0, $attribute_ids)];
    }

    public function update(array $data): void
    {

        foreach ($data['exceptions'] as $exception) {

            $exceptionables = collect($exception['exceptionables'])
                ->map(fn ($data) => PlanException::query()
                    ->updateOrCreate(['id' => $data['id'] ?? null], [...$data, 'name' => $exception['exception_name']]));

            $attribute = ExceptionAttribute::query()->updateOrCreate(['id' => $exception['attribute_id'] ?? null], [
                'attributes' => $exception['attributes'],
            ]);

            $attribute->exceptionables()->sync($exceptionables->pluck('id'));
        }
    }

    public function getExceptionsByPlanId($planId, $planableType = Plan::class, $attributeId = null)
    {
        $attributes = ExceptionAttribute::with('exceptionables')
            ->whereHas('exceptionables')
            ->when($attributeId, function ($query) use ($attributeId): void {
                $query->orWhereIn('id', $attributeId);
            })
            ->get();

        return $attributes->map(function ($attribute): array {
            $exceptionables = $attribute->exceptionables->map(fn ($item): array => [
                'exception_name'     => $item->name,
                'id'                 => $item->id,
                'exceptionable_type' => $item->exceptionable_type,
                'exceptionable_id'   => $item->exceptionable_id,
                'name'               => $item->exceptionable?->name,
            ])->toArray();

            return [
                'exceptionables' => $exceptionables,
                'attributes'     => $attribute->attributes,
                'attribute_id'   => $attribute->id,
                'exception_name' => $exceptionables[0]['exception_name'],
            ];
        });
    }

    public function delete($exception_attribute): void
    {
        $exception_attribute->exceptionables()->delete();
        $exception_attribute->exceptionables()->detach();
        $exception_attribute->delete();
    }
}
