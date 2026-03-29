<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\UploadedFile;

readonly class VariantService
{
    public function __construct(private Product $product, private array $data) {}

    public function execute(): void
    {
        $list = [];

        foreach ($this->data as $variant) {
            $list[] = $this->add($variant);
        }

        $this->product->variants()->whereNotIn('id', collect($list)->pluck('id'))->delete();
    }

    private function add(array $data): Variant
    {
        if ($images = $data['images'] ?? null) {
            $data['images'] = collect($images)->map(function (UploadedFile|string $image) use ($data) {
                if (is_string($image)) {
                    return $image;
                }

                $name = $data['name'].uniqid().'.'.$image->extension();

                return $image->storeAs('products', $name);
            });
        }

        return Variant::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'product_id'     => $this->product->id,
                'name'           => $data['name'] ?? $this->product->name,
                'country'        => $data['country'] ?? null,
                'barcode'        => $data['barcode'] ?? null,
                'attributes'     => $data['attributes'] ?? [],
                'main'           => $data['main'] ?? false,
                'image'          => $data['image'] ?? null,
                'images'         => $data['images'] ?? null,
                'description'    => $data['description'] ?? null,
                'packages'       => $data['packages'] ?? null,
                'cargo_packages' => $data['cargo_packages'] ?? null,
            ]
        );
    }

    public static function make(Product $product, array $data): self
    {
        return new self($product, $data);
    }
}
