<?php

namespace App\Filament\Resources\Packagings\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PackagingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('نام بسته‌بندی')
                    ->required()
                    ->maxLength(255),

                TextInput::make('brand_name')
                    ->label('نام برند')
                    ->nullable()
                    ->maxLength(255),

                Repeater::make('packagingItems')
                    ->relationship()
                    ->label('محصولات داخل بسته‌بندی')
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->afterStateHydrated(function (Repeater $component): void {
                        $state = $component->getState();
                        $state = is_array($state) ? $state : [];
                        /** @var array<string, array<string, mixed>> $state */
                        $state = array_filter(
                            $state,
                            fn (array $item): bool => filled($item['product_id'] ?? null),
                        );
                        $presentProductIds = [];

                        foreach ($state as $item) {
                            if (isset($item['product_id'])) {
                                $presentProductIds[] = (int) $item['product_id'];
                            }
                        }

                        foreach (Product::all()->sortBy('id') as $product) {
                            if (in_array($product->id, $presentProductIds, true)) {
                                continue;
                            }

                            $state[(string) Str::uuid()] = [
                                'product_id' => $product->id,
                                'quantity' => 0,
                            ];
                        }

                        $component->state($state);
                    })
                    ->schema([
                        Select::make('product_id')
                            ->label('محصول')
                            ->relationship('product', 'name')
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('quantity')
                            ->label('تعداد در بسته')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->required()
                            ->default(0),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
