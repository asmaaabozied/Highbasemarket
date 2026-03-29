<?php

namespace App\Services;

use App\Http\Filters\OrdersFilter;
use App\Http\Filters\StocksFilter;
use App\Models\Branch;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\QuoteProduct;
use App\Models\Stock;
use Illuminate\Contracts\Pagination\Paginator;

class ProductService
{
    public function __construct(public ?Product $product = null, public ?Branch $branch = null) {}

    public function getProducts(): \Illuminate\Database\Eloquent\Collection
    {
        return Product::query()
            ->whereHas('brand', function ($join): void {
                $join->whereHas('owner', function ($query): void {
                    $query
                        ->where('account_id', auth()->user()->getAccount()->id)
                        ->orWhere('id', auth()->user()->getAccount()->id);
                });
            })
            ->with(['variants:id,product_id,attributes,packages'])
            ->select('products.id', 'products.id as productId', 'products.name as productName', 'products.image')
            ->get();
    }

    public function getVariants(): Paginator
    {
        return $this->product->variants()->paginate(100);
    }

    public function getStock(): Paginator
    {
        if ($this->branch instanceof \App\Models\Branch) {
            return $this->accountStocks();
        }

        return $this->highbaseAdminStocks();
    }

    private function accountStocks(): Paginator
    {
        $filter = new StocksFilter;

        $data = $filter->execute(function ($query): void {
            $query
                ->where('product_id', $this->product->id)
                ->where('branch_id', $this->branch->id)
                ->with([
                    'product.category:id,slug,name',
                    'product.brand:id,slug,name',
                    'variant',
                    'branch:id,slug,name',
                ]);
        })->paginate();

        $data->transform(function ($item) {
            $item->name = $item->variant?->name ?? $item->product?->name;
            $item->image ??= $item->variant?->image ?? $item->product?->image;

            return $item;
        });

        return $data;
    }

    private function highbaseAdminStocks(): Paginator
    {
        $filter = new StocksFilter;

        $data = $filter->execute(function ($query): void {
            $query
                ->where('product_id', $this->product->id)
                ->with([
                    'product.category:id,slug,name',
                    'product.brand:id,slug,name',
                    'variant',
                    'branch:id,slug,name',
                ]);
        })->paginate();

        $data->transform(function ($item) {
            $item->name = $item->variant?->name ?? $item->product?->name;
            $item->image ??= $item->variant?->image ?? $item->product?->image;

            return $item;
        });

        return $data;
    }

    public function ordersCount(): int
    {
        return OrderLine::whereHas('product', function ($query): void {
            $query->when($this->branch, function ($query): void {
                $query->where('branch_id', $this->branch->id);
            })->where('product_id', $this->product->id);
        })
            ->whereNotIn('status', ['rejected', 'cancelled', 'pending'])
            ->count();
    }

    public function totalSales(): float
    {
        return OrderLine::whereHas('product', function ($query): void {
            $query->when($this->branch, function ($query): void {
                $query->where('branch_id', $this->branch->id);
            })->where('product_id', $this->product->id);
        })
            ->whereNotIn('status', ['rejected', 'cancelled', 'pending'])
            ->sum('total');
    }

    public function stocksCount(): float
    {
        return Stock::where('product_id', $this->product->id)
            ->when($this->branch, function ($query): void {
                $query->where('branch_id', $this->branch->id);
            })
            ->sum('quantity');
    }

    public function quotesCount(): int
    {
        return QuoteProduct::where(['quotable_id' => $this->product->id, 'quotable_type' => Product::class])
            ->when($this->branch, function ($query): void {
                $query->whereHas('quoteDetail.quote', function ($query): void {
                    $query->where('vendor', $this->branch->id);
                });
            })
            ->count();
    }

    public function salesChart()
    {
        $service = (new OrderStatsService)->setProducts($this->product);

        if ($this->branch instanceof \App\Models\Branch) {
            $service->setSellers($this->branch);
        }

        return $service->SalesChart();
    }

    public function topSales()
    {
        $service = (new OrderStatsService)->setProducts($this->product);

        if ($this->branch instanceof \App\Models\Branch) {
            $service->setSellers($this->branch);
        }

        return $service->mostPurchasedVariants();
    }

    public function sales()
    {
        $filter = (new OrdersFilter)
            ->execute(function ($query): void {
                $query->withWhereHas('lines.product', function ($query): void {
                    $query->where('product_id', $this->product->id)
                        ->with(['product:id,name,image,slug', 'variant:id,name,image']);
                })->with(['branch']);
            })
            ->withBuyer(false);

        if ($this->branch instanceof \App\Models\Branch) {
            $filter->forSeller($this->branch)->withAssignees($this->branch);
        }

        $data = $filter->paginate();

        $data->transform(function ($item) {
            $item->line = $item->lines->first();

            return $item;
        });

        return $data;
    }
}
