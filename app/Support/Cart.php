<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * A cart priced from the database. The browser only ever sends product ids and
 * quantities, so everything shown or charged is derived here.
 */
class Cart
{
    public const MAX_QUANTITY = 99;

    /**
     * @param  Collection<int, array{product: Product, quantity: int, unit_cents: int, line_cents: int, available: bool}>  $lines
     * @param  array<int, int>  $missingIds  ids that no longer exist or are inactive
     */
    public function __construct(
        public readonly Collection $lines,
        public readonly array $missingIds,
    ) {}

    /**
     * @param  array<int, array{id: int|string, qty: int|string}>  $items
     */
    public static function fromItems(array $items): self
    {
        $quantities = [];

        foreach ($items as $item) {
            $id = (int) $item['id'];
            $quantities[$id] = min(self::MAX_QUANTITY, ($quantities[$id] ?? 0) + (int) $item['qty']);
        }

        $products = Product::active()
            ->with('featuredAsset.media')
            ->whereIn('id', array_keys($quantities))
            ->get()
            ->keyBy('id');

        $lines = collect($quantities)
            ->filter(fn (int $quantity, int $id) => $products->has($id))
            ->map(function (int $quantity, int $id) use ($products) {
                $product = $products[$id];
                $unitCents = self::toCents($product->price);

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_cents' => $unitCents,
                    'line_cents' => $unitCents * $quantity,
                    'available' => $product->in_stock && $product->price !== null,
                ];
            })
            ->values();

        $missingIds = array_values(array_diff(array_keys($quantities), $products->keys()->all()));

        return new self($lines, $missingIds);
    }

    public function subtotalCents(): int
    {
        return $this->lines->sum('line_cents');
    }

    public function isEmpty(): bool
    {
        return $this->lines->isEmpty();
    }

    /**
     * Every requested product still exists and can be bought.
     */
    public function isPurchasable(): bool
    {
        return ! $this->isEmpty()
            && $this->missingIds === []
            && $this->lines->every(fn (array $line) => $line['available']);
    }

    public function toArray(): array
    {
        return [
            'lines' => $this->lines->map(fn (array $line) => [
                'id' => $line['product']->id,
                'name' => $line['product']->name,
                'type' => $line['product']->type,
                'url' => route('products.show', $line['product']->slug),
                'image' => $line['product']->featuredAsset?->url('thumb') ?: null,
                'qty' => $line['quantity'],
                'unit_price' => self::format($line['unit_cents']),
                'line_total' => self::format($line['line_cents']),
                'available' => $line['available'],
            ])->all(),
            'subtotal' => self::format($this->subtotalCents()),
            'missing_ids' => $this->missingIds,
            'purchasable' => $this->isPurchasable(),
        ];
    }

    public static function toCents(string|float|null $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    /**
     * A plain decimal string ("12.50"), matching how prices are stored and displayed.
     */
    public static function format(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
