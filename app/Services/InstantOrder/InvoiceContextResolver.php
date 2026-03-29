<?php

namespace App\Services\InstantOrder;

use App\Models\AnonymousCustomerBranch;
use App\Models\Branch;
use App\Models\EmployeeVisit;

class InvoiceContextResolver
{
    public function resolve(array $input): array
    {
        $context = [
            'source'          => 'manual',
            'visit'           => null,
            'customerContext' => null,
        ];

        // From Visit
        if (! empty($input['visit'])) {
            $visit = EmployeeVisit::with([
                'visitable',
                'lines.orderLine.product.product.category',
                'lines.orderLine.product.product.brand',
                'lines.orderLine.product.variant',
            ])->findOrFail($input['visit']);

            $context['source'] = 'visit';
            $context['visit']  = [
                'id'    => $visit->id,
                'lines' => $visit->lines->map(fn ($line): array => [
                    'id'         => $line->orderLine->product->id,
                    'uuid'       => $line->orderLine->product->id,
                    'product_id' => $line->orderLine->product->id,
                    'quantity'   => $line->quantity,
                    'packaging'  => $line->orderLine->packaging ?? null,
                    'product'    => $line->orderLine->product->product,
                    'variant'    => $line->orderLine->product->variant,
                ]),
            ];

            if ($visit->visitable_type === Branch::class) {
                $context['customerContext'] = [
                    'type'        => 'existing',
                    'customer_id' => $visit->visitable->id,
                ];
            }

            if ($visit->visitable_type === AnonymousCustomerBranch::class) {
                $context['customerContext'] = [
                    'type'              => 'new',
                    'anonymous_payload' => [
                        'name'       => $visit->visitable->name,
                        'phone'      => $visit->visitable->phone,
                        'email'      => $visit->visitable->email,
                        'cr_number'  => $visit->visitable->customer->cr_number ?? null,
                        'vat_number' => $visit->visitable->customer->vat_number ?? null,
                    ],
                ];
            }
        }

        if (! empty($input['customer'])) {
            $customer = Branch::findOrFail($input['customer']);

            $context['source']          = 'customer';
            $context['customerContext'] = [
                'type'        => 'existing',
                'customer_id' => $customer->id,
            ];
        }

        return $context;
    }
}
