<?php

namespace App\Filament\Components;

use Filament\Schemas\Components\Component;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;

class ContentBlocks extends Component
{
    protected string $view = 'filament.components.content-blocks';

    public static function make(String $name)
    {
        return Builder::make($name)
            ->blocks([
                Block::make('heading')
                    ->schema([
                        TextInput::make('content')
                            ->label('Heading')
                            ->required(),
                        Select::make('level')
                            ->options([
                                'h2' => 'Heading 2',
                                'h3' => 'Heading 3',
                                'h4' => 'Heading 4',
                                'h5' => 'Heading 5',
                                'h6' => 'Heading 6',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
                Block::make('paragraph')
                    ->schema([
                        RichEditor::make('content')
                            ->toolbarButtons([
                                ['bold', 'italic', 'link'],
                                ['orderedList', 'bulletList'],
                            ])
                            ->label('Paragraph')
                            ->required(),
                    ]),
                Block::make('image')
                    ->schema([
                        Images::asset()->required(),
                    ]),
                Block::make('gallery')
                    ->schema([
                        Repeater::make('images')
                            ->label('Gallery Images')
                            ->simple(Images::asset()->required())
                            ->reorderable(),
                    ]),
                Block::make('list')
                    ->schema([
                        Select::make('type')
                            ->label('List Type')
                            ->options([
                                'ul' => 'Bullet List',
                                'ol' => 'Numbered List',
                            ])
                            ->required()
                            ->default('ul'),
                        Repeater::make('content')
                            ->label('List Items')
                            ->schema([
                                TextInput::make('content')
                                    ->label('List Item')
                                    ->required(),
                            ])
                            ->minItems(1)
                            ->columns(1),
                    ]),
            ]);
    }
}