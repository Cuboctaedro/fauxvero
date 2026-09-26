<?php

namespace App\Filament\Components;

use Filament\Schemas\Components\Component;
use Filament\Forms\Components\RichEditor;


class Editor extends Component
{
    protected string $view = 'filament.components.editor';

    public static function make(String $name)
    {
        return RichEditor::make($name)
            ->toolbarButtons([
                ['bold', 'italic', 'link'],
                ['h2', 'h3', 'h4'],
                ['orderedList', 'bulletList'],
            ]);

    }

}