<?php

namespace App\Services;

use App\Dto\QuoteDto;
use App\Dto\QuotesDto;
use App\Events\QuoteNotify;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Progress;
use App\Models\Quote;
use App\Models\QuoteDetail;
use App\Models\QuoteProduct;
use App\Models\Step;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    public function getMyQuotes(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $quotesIds = Quote::query()->where('creator', currentBranch()->id)
            ->where('status', 'active')->pluck('id');

        return QuoteDetail::query()
            ->with(['quote.creator_branch', 'quote.vendor_branch', 'quote.confirm'])
            ->whereIn('quote_id', $quotesIds)->paginate();
    }

    public function getQuotesAssignToMe(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $quotesIds = Quote::query()
            ->select('id', 'vendor', 'status')
            ->where('vendor', currentBranch()->id)
            ->where('status', 'active')->pluck('id');

        return QuoteDetail::query()
            ->with(['quote.creator_branch:id,slug,name,address', 'quote.vendor_branch:id,slug,name,address', 'quote.confirm'])
            ->whereIn('quote_id', $quotesIds)->paginate();
    }

    public function getQuotesOrdered(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {

        return QuoteDetail::query()
            ->with(['quote.creator_branch:id,slug,name', 'quote.vendor_branch:id,slug,name', 'quote.confirm'])
            ->whereHas('quote', function ($quote): void {
                $quote->where('status', 'inactive')
                    ->where(function ($quote): void {
                        $quote->where('vendor', currentBranch()->id)
                            ->orWhere('creator', currentBranch()->id);
                    });
            })
            ->paginate();
    }

    public function createQuote(array $data): void
    {

        DB::transaction(function () use ($data): void {
            $progressService = new ProgressService;
            $termService     = new TermsService;

            $quote = Quote::query()->create([
                'creator' => $data['creator'],
                'vendor'  => $data['vendor'],
                'status'  => $data['status'] ?? 'active',
            ]);

            foreach ($data['details'] as $detail) {
                $progress               = $progressService->replicateProgress($detail['progress_id']);
                $term                   = $termService->replicateTerms($detail['term_id']);
                $details                = Arr::except($detail, ['products', 'collapse', 'steps']);
                $details['progress_id'] = $progress->id;
                $details['term_id']     = $term->id;

                $detailModel = $quote->quoteDetails()->create($details);
                foreach ($detail['products'] as $product) {
                    $productModel = $detailModel->quoteProducts()->create([
                        ...Arr::except($product, ['quotable', 'name', 'image', 'specification']),
                        'quotable_type' => Product::class,
                        'quotable_id'   => $product['quotable'],
                        'product'       => json_encode([
                            'name'  => $product['name'],
                            'image' => $product['image'],
                        ]),
                    ]);

                    if (isset($product['specification'])) {
                        $productModel->addMedia($product['specification'])->toMediaCollection('specifications');
                    }
                }
            }
        });

        QuoteNotify::dispatch(
            new QuoteDto(
                creator: $data['creator'],
                vendor: $data['vendor'],
            ));
    }

    public function getQuoteDetailById($id)
    {
        return QuoteDetail::query()->with(['quote.creator_branch', 'quote.vendor_branch', 'progress.steps.actions'])->find($id);
    }

    public function getQuoteProductsByQuoteId($id): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return QuoteProduct::query()->with(['quotable.variants'])->where('quote_detail_id', $id)->paginate();
    }

    public function getQuoteCreateVariables($data): QuotesDto
    {
        $product = null;
        $brand   = null;

        if ($data->get('type') === 'brand') {
            $brand = Brand::query()->find($data->get('quotable'))->load('products.variants:id,product_id,attributes,packages');
        } elseif ($data->get('type') === 'product') {
            $product = Product::query()
                ->with(['variants:id,product_id,attributes,packages', 'brand.products'])
                ->findOrFail($data->get('quotable'));
            $brand = $product?->brand;
        }

        $vendor = Branch::query()->where('slug', $data->get('branch'))->first();

        return new QuotesDto(
            vendor: $vendor,
            product: $product,
            brand: $brand,
            quotable_id: $data->get('type') === 'proposal' ? $data->get('quotable') : null
        );

    }

    public function getQuoteById($id)
    {
        return Quote::query()
            ->with(['quoteDetails.term', 'quoteDetails.quoteProducts.quotable.variants:id,product_id,attributes,packages', 'quoteDetails.progress.steps'])
            ->where('id', $id)->first();
    }

    public function getBranchesId()
    {
        return auth()->user()->getAccount()->branches()->pluck('id');
    }

    public function updateQuote($id, $data): void
    {
        DB::transaction(function () use ($data, $id): void {
            $progressService = new ProgressService;
            $termService     = new TermsService;

            $quote = Quote::query()->findOrFail($id);
            $quote->update([
                'creator' => $data['creator'],
                'vendor'  => $data['vendor'],
            ]);

            foreach ($data['details'] as $detail) {
                $detil                  = $quote->quoteDetails()->where('id', $detail['id'])->first();
                $progress               = $progressService->replicateProgress($detail['progress_id'], $detail['id']);
                $term                   = $termService->replicateTerms($detail['term_id'], $detail['id']);
                $details                = Arr::only($detail, ['name', 'quote_type', 'price', 'terms']);
                $details['progress_id'] = $progress->id;
                $details['term_id']     = $term->id;
                $newQuote               = $quote->quoteDetails()
                    ->updateOrCreate(
                        ['id' => $detail['id'] ?? null],
                        $details
                    );

                if (! $detil) {
                    $detil = $newQuote;
                }

                foreach ($detail['products'] as $product) {
                    $productModel = $detil->quoteProducts()
                        ->updateOrCreate(['id' => $product['id'] ?? null], [
                            ...Arr::except($product, ['quotable', 'name', 'image', 'specification', 'media']),
                            'quotable_type' => Product::class,
                            'quotable_id'   => $product['quotable'],
                            'product'       => json_encode([
                                'name'  => $product['name'],
                                'image' => $product['image'],
                            ]),
                        ]);

                    if (isset($product['specification'])) {
                        $productModel->clearMediaCollection('specifications');
                        $productModel->addMedia($product['specification'])->toMediaCollection('specifications');
                    }
                }
            }
        });
    }

    public function quoteProgress($id, array $data): void
    {
        $quote    = QuoteDetail::query()->findOrFail($id);
        $progress = $quote->progress;
        $model    = $quote->progress()->updateOrCreate(
            ['id' => $progress?->id],
            [
                'name'   => $quote->name,
                'status' => Progress::ACTIVE,
            ]
        );

        foreach ($data['steps'] as $step) {
            $model->steps()->updateOrCreate(
                ['id' => $step['id'] ?? null], [...$step, 'status' => Step::INACTIVE]
            );
        }
    }

    public function quoteUpdatePayment($quote_order): void
    {
        currentBranch()->customers()->updateExistingPivot($quote_order?->quote->vendor, [
            'config->paid' => true,
        ]);
    }
}
