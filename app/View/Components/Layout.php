<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Layout extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $metaTitle = 'Fauxvero',
        public string $metaDescription = '',
        public string $metaKeywords = '',
        public bool $metaRobots = true,
        public string $metaImage = '',
        public string $pagePath = '/',
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.layout');
    }
}
