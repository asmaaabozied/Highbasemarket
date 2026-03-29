<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Collection;

class OrderStatsService
{
    private array $accounts = [];

    private array $sellers = [];

    private array $branches = [];

    private array $products = [];

    private array $categories = [];

    private array $brands = [];

    private array $variants = [];

    private $query;

    private $active_query;

    public function __construct()
    {
        //        $this->query = Order::query();
    }

    public function setAccounts(Account|array $accounts): self
    {
        if (is_array($accounts)) {
            $this->accounts = $accounts;
        } else {
            $this->accounts[] = $accounts;
        }

        return $this;
    }

    public function setSellers(Branch|array $sellers): self
    {
        if (is_array($sellers)) {
            $this->sellers = [...$this->sellers, ...$sellers];
        } else {
            $this->sellers[] = $sellers;
        }

        return $this;
    }

    public function setBranches(Branch|array $branches): self
    {
        if (is_array($branches)) {
            $this->branches = [...$this->branches, ...$branches];
        } else {
            $this->branches[] = $branches;
        }

        return $this;
    }

    public function setProducts(Product|array $products): self
    {
        if (is_array($products)) {
            $this->products = [...$this->products, ...$products];
        } else {
            $this->products[] = $products;
        }

        return $this;
    }

    public function setCategories(Category|array $categories): self
    {
        if (is_array($categories)) {
            $this->categories = [...$this->categories, ...$categories];
        } else {
            $this->categories[] = $categories;
        }

        return $this;
    }

    public function setBrands(Brand|array $brands): self
    {
        if (is_array($brands)) {
            $this->brands = [...$this->brands, ...$brands];
        } else {
            $this->brands[] = $brands;
        }

        return $this;
    }

    public function setVariants(Variant|array $variants): self
    {
        if (is_array($variants)) {
            $this->variants = [...$this->variants, ...$variants];
        } else {
            $this->variants[] = $variants;
        }

        return $this;
    }

    private function getProductsIds(): array
    {
        return $this->getIds($this->products);
    }

    private function getAccountsIds(): array
    {
        return $this->getIds($this->accounts);
    }

    private function getSellersIds(): array
    {
        return $this->getIds($this->sellers);
    }

    private function getBranchesIds(): array
    {
        return $this->getIds($this->branches);
    }

    private function getCategoriesIds(): array
    {
        return $this->getIds($this->categories);
    }

    private function getBrandsIds(): array
    {
        return $this->getIds($this->brands);
    }

    private function getVariantsIds(): array
    {
        return $this->getIds($this->variants);
    }

    private function getIds(Collection|array $items): array
    {
        return collect($items)->map(function ($item) {
            if (is_object($item)) {
                return $item->id;
            }

            if (is_array($item)) {
                return $item['id'] ?? null;
            }

            return $item;
        })->toArray();
    }

    public function getOrdersCount(): int
    {
        if (empty($this->query)) {
            $this->query();
        }

        return $this->query->count();
    }

    public function totalAmount(): int
    {
        if ($this->getSellersIds() !== []) {
            return $this->lineQuery()->sum('total');
        }

        return $this->query->sum('total');

    }

    public function mostPurchasedVariants(?int $take = null)
    {
        return $this->variantQuery()
            ->selectRaw('variants.id, variants.name, variants.uuid, COUNT(order_lines.id) as total_order_lines,
                SUM(order_lines.quantity) as total_quantity, SUM(order_lines.total) as total_amount')
            ->when($take, function ($query) use ($take): void {
                $query->take($take);
            })
            ?->groupByRaw('variants.id, variants.name')
            ->orderByDesc('total_order_lines')
            ->get();
    }

    public function mostPurchasedProducts(?int $take = null)
    {
        return $this->productQuery()
            ->selectRaw('products.id, products.name, products.slug, COUNT(order_lines.id) as total_order_lines,
                SUM(order_lines.quantity) as total_quantity, SUM(order_lines.total) as total_amount')
            ->when($take, function ($query) use ($take): void {
                $query->take($take);
            })
            ?->groupByRaw('products.id, products.name')
            ->orderByDesc('total_order_lines')
            ->get();

    }

    public function SalesChart(?int $take = null, $group_type = 'day')
    {
        $query = $this->lineQuery()
            ->selectRaw('DATE(order_lines.created_at) as date, SUM(order_lines.total) as total_amount')
            ->when($take, function ($query) use ($take): void {
                $query->take($take);
            })
            ->when($group_type === 'day', function ($query): void {
                $query->groupByRaw('DATE(order_lines.created_at)');
            })
            ->when($group_type === 'month', function ($query): void {
                $query->groupByRaw('MONTH(order_lines.created_at), YEAR(order_lines.created_at)');
            })
            ->when($group_type === 'year', function ($query): void {
                $query->groupByRaw('YEAR(order_lines.created_at)');
            })
            ->orderBy('date');

        return $query->get();
    }

    public function purchasesChart(?int $take = null, $group_type = 'day')
    {
        return $this->lineQuery()
            ->selectRaw('DATE(order_lines.created_at) as date, SUM(order_lines.total) as total_amount')
            ->when($take, function ($query) use ($take): void {
                $query->take($take);
            })
            ->when($group_type === 'day', function ($query): void {
                $query->groupByRaw('DATE(order_lines.created_at)');
            })
            ->when($group_type === 'month', function ($query): void {
                $query->groupByRaw('MONTH(order_lines.created_at), YEAR(order_lines.created_at)');
            })
            ->when($group_type === 'year', function ($query): void {
                $query->groupByRaw('YEAR(order_lines.created_at)');
            })
            ->orderBy('date')
            ->get();
    }

    public function mostPurchasedBrands(?int $take = null, ?callable $callback = null)
    {
        return $this->brandQuery()
            ->selectRaw('brands.id, brands.name, brands.slug, COUNT(order_lines.id) as total_order_lines')
            ->when($take, function ($query) use ($take): void {
                $query->take($take);
            })
            ->when($callback, $callback)
            ?->groupByRaw('brands.id, brands.name')
            ->orderByDesc('total_order_lines')
            ->get();
    }

    public function mostPurchasedCategories(?int $take = null, ?callable $callback = null)
    {
        return $this->categoryQuery()
            ->selectRaw('categories.id, categories.name, categories.slug, COUNT(order_lines.id) as total_order_lines,
                SUM(order_lines.quantity) as total_quantity, SUM(order_lines.total) as total_amount')
            ->when($take, function ($query) use ($take): void {
                $query->take($take);
            })
            ->when($callback, $callback)
            ?->groupByRaw('categories.id, categories.name')
            ->orderByDesc('total_order_lines')
            ->get();

    }

    private function query(): void
    {
        $this->query = Order::query()
            ->when($this->getAccountsIds() !== [], function ($query): void {
                $query->whereHas('branch.account', function ($query): void {
                    $query->whereIn('id', $this->getAccountsIds());
                });
            })
            ->when($this->getSellersIds() !== [], function ($query): void {
                $query->whereHas('lines.product', function ($query): void {
                    $query->whereIn('branch_id', $this->getSellersIds());
                });
            })
            ->when($this->getBranchesIds() !== [], function ($query): void {
                $query->whereIn('branch_id', $this->getBranchesIds());
            })
            ->when($this->getProductsIds() !== [], function ($query): void {
                $query->whereHas('lines.product', function ($query): void {
                    $query->whereIn('product_id', $this->getProductsIds());
                });
            })
            ->when($this->getCategoriesIds() !== [], function ($query): void {
                $query->whereHas('lines.product', function ($query): void {
                    $query->whereHas('product', function ($query): void {
                        $query->whereIn('category_id', $this->getCategoriesIds());
                    });
                });
            })
            ->when($this->getBrandsIds() !== [], function ($query): void {
                $query->whereHas('lines.product', function ($query): void {
                    $query->whereHas('product', function ($query): void {
                        $query->whereIn('brand_id', $this->getBrandsIds());
                    });
                });
            })
            ->when($this->getVariantsIds() !== [], function ($query): void {
                $query->whereIn('p', $this->getVariantsIds());
            });
    }

    public function lineQuery()
    {
        return OrderLine::whereNotIn('status', ['cancelled'])
            ->when($this->branches !== [], function ($query): void {
                $query->whereHas('order', function ($query): void {
                    $query->whereIn('branch_id', $this->getBranchesIds());
                });
            })
            ->whereHas('product', function ($query): void {
                $query->whereHas('branch', function ($query): void {
                    $query->when($this->getSellersIds() !== [], function ($query): void {
                        $query->whereIn('id', $this->getSellersIds());
                    });

                })
                    ->when($this->getCategoriesIds() !== [], function ($query): void {
                        $query->whereHas('product.category', function ($query): void {
                            $query->whereIn('id', $this->getCategoriesIds());
                        });
                    })
                    ->when($this->getBrandsIds() !== [], function ($query): void {
                        $query->whereHas('product.brand', function ($query): void {
                            $query->whereIn('id', $this->getBrandsIds());
                        });
                    })
                    ->when($this->getProductsIds() !== [], function ($query): void {
                        $query->whereHas('product', function ($query): void {
                            $query->whereIn('id', $this->getProductsIds());
                        });
                    })
                    ->when($this->getVariantsIds() !== [], function ($query): void {
                        $query->whereHas('variants', function ($query): void {
                            $query->whereIn('id', $this->getVariantsIds());
                        });
                    });
            });
    }

    public function categoryQuery()
    {
        return Category::query()
            ->when($this->getAccountsIds() !== [], function ($query): void {
                $query->whereHas('products', function ($query): void {
                    $query->whereHas('branch.account', function ($query): void {
                        $query->whereIn('id', $this->getAccountsIds());
                    });
                });
            })
            ->whereHas('products', function ($query): void {
                $query->when($this->getProductsIds() !== [], function ($query): void {
                    $query->whereIn('id', $this->getProductsIds());
                })
                    ->whereHas('variants', function ($query): void {
                        $query
                            ->when($this->getVariantsIds() !== [], function ($query): void {
                                $query->whereIn('id', $this->getVariantsIds());
                            })
                            ->whereHas('stocks', function ($query): void {
                                $query
                                    ->when($this->getSellersIds() !== [], function ($query): void {
                                        $query->whereIn('branch_id', $this->getSellersIds());
                                    })
                                    ->whereHas('lines', function ($query): void {
                                        $query->whereNotIn('status', ['cancelled', 'rejected'])
                                            ->when($this->getBranchesIds() !== [], function ($query): void {
                                                $query->whereHas('order', function ($query): void {
                                                    $query->whereIn('branch_id', $this->getBranchesIds());
                                                });
                                            });
                                    });
                            });
                    });
            })
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('variants', 'products.id', '=', 'variants.product_id')
            ->join('stocks', 'variants.id', '=', 'stocks.variant_id')
            ->join('order_lines', 'stocks.id', '=', 'order_lines.product_id');

    }

    public function BrandQuery()
    {
        return Brand::query()
            ->when($this->getAccountsIds() !== [], function ($query): void {
                $query->whereHas('owner', function ($query): void {
                    $query->whereIn('account_id', $this->getAccountsIds());
                });
            })
            ->whereHas('products', function ($query): void {
                $query->when($this->getProductsIds() !== [], function ($query): void {
                    $query->whereIn('id', $this->getProductsIds());
                })
                    ->whereHas('variants', function ($query): void {
                        $query->when($this->getVariantsIds() !== [], function ($query): void {
                            $query->whereIn('id', $this->getVariantsIds());
                        })
                            ->whereHas('stocks', function ($query): void {
                                $query
                                    ->when($this->getSellersIds() !== [], function ($query): void {
                                        $query->whereIn('branch_id', $this->getSellersIds());
                                    })
                                    ->whereHas('lines', function ($query): void {
                                        $query->whereNotIn('status', ['cancelled', 'rejected'])
                                            ->when($this->getBranchesIds() !== [], function ($query): void {
                                                $query->whereHas('order', function ($query): void {
                                                    $query->whereIn('branch_id', $this->getBranchesIds());
                                                });
                                            });
                                    });
                            });
                    });
            })
            ->join('products', 'brands.id', '=', 'products.brand_id')
            ->join('variants', 'products.id', '=', 'variants.product_id')
            ->join('stocks', 'variants.id', '=', 'stocks.variant_id')
            ->join('order_lines', 'stocks.id', '=', 'order_lines.product_id');
    }

    public function productQuery()
    {
        return Product::query()
            ->when($this->getBrandsIds() !== [], function ($query): void {
                $query->whereIn('brand_id', $this->getBrandsIds());
            })->when($this->getCategoriesIds() !== [], function ($query): void {
                $query->whereIn('category_id', $this->getCategoriesIds());
            })
            ->whereHas('variants.stocks', function ($query): void {
                $query->when($this->getSellersIds() !== [], function ($query): void {
                    $query->whereIn('branch_id', $this->getSellersIds());
                })
                    ->whereHas('lines', function ($query): void {
                        $query->whereNotIn('status', ['cancelled', 'rejected'])
                            ->when($this->getBranchesIds() !== [], function ($query): void {
                                $query->whereHas('order', function ($query): void {
                                    $query->whereIn('branch_id', $this->getBranchesIds());
                                });
                            });
                    });
            })
            ->join('variants', 'products.id', '=', 'variants.product_id')
            ->join('stocks', 'variants.id', '=', 'stocks.variant_id')
            ->join('order_lines', 'stocks.id', '=', 'order_lines.product_id');
    }

    public function variantQuery()
    {
        return Variant::query()
            ->when($this->getAccountsIds() !== [], function ($query): void {
                $query->whereHas('product.branch.account', function ($query): void {
                    $query->whereIn('id', $this->getAccountsIds());
                });
            })
            ->when($this->getProductsIds() !== [], function ($query): void {
                $query->whereIn('variants.product_id', $this->getProductsIds());
            })
            ->when($this->getBrandsIds() !== [], function ($query): void {
                $query->whereIn('brand_id', $this->getBrandsIds());
            })->when($this->getCategoriesIds() !== [], function ($query): void {
                $query->whereIn('category_id', $this->getCategoriesIds());
            })
            ->whereHas('stocks', function ($query): void {
                $query->when($this->getSellersIds() !== [], function ($query): void {
                    $query->whereIn('branch_id', $this->getSellersIds());
                })
                    ->whereHas('lines', function ($query): void {
                        $query->whereNotIn('status', ['cancelled', 'rejected'])
                            ->when($this->getBranchesIds() !== [], function ($query): void {
                                $query->whereHas('order', function ($query): void {
                                    $query->whereIn('branch_id', $this->getBranchesIds());
                                });
                            });
                    });
            })
            ->join('stocks', 'variants.id', '=', 'stocks.variant_id')
            ->join('order_lines', 'stocks.id', '=', 'order_lines.product_id');
    }

    public function get(?callable $callback = null): Collection
    {
        if ($callback) {
            return $callback($this->query);
        }

        return $this->active_query->get();
    }
}
