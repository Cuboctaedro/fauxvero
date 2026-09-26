<?php

namespace App\View\Components;

use App\Models\Asset;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * A library image with a srcset of its resized conversions. `sizes` describes
 * how wide the image is displayed at each breakpoint, so the browser can pick
 * the smallest file that fits.
 */
class AssetImage extends Component
{
    public function __construct(
        public ?Asset $asset,
        public string $sizes = '100vw',
        public ?string $alt = null,
        public string $loading = 'lazy',
    ) {}

    public function shouldRender(): bool
    {
        return $this->asset !== null && $this->asset->url() !== '';
    }

    public function render(): View|Closure|string
    {
        return view('components.asset-image');
    }
}
