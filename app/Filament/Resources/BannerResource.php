<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Banners';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Select::make('category')
                    ->label('Category')
                    ->options([
                        'main_slide'  => 'Main Slide',
                        'main_banner' => 'Main Banner',
                        'sub_banner'  => 'Sub Banner',
                    ])
                    ->required()
                    ->validationMessages([
                        'required' => 'Please select a category.',
                    ])
                    ->live(),

                Forms\Components\TextInput::make('position')
                    ->label('Position')
                    ->placeholder('e.g. banner1_main_slide1')
                    ->required()
                    ->maxLength(255)
                    ->validationMessages([
                        'required' => 'Please enter a position.',
                        'max'      => 'Position may not be greater than 255 characters.',
                    ]),

                Forms\Components\TextInput::make('order')
                    ->label('Order')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->minValue(0)
                    ->validationMessages([
                        'required' => 'Please enter a display order.',
                        'numeric'  => 'Order must be a number.',
                        'min'      => 'Order must be at least 0.',
                    ]),

                Forms\Components\FileUpload::make('image_url')
                    ->label('Banner Image')
                    ->image()
                    ->directory('banners')
                    ->visibility('public')
                    ->imagePreviewHeight('200')
                    ->required()
                    ->validationMessages([
                        'required' => 'Please upload a banner image.',
                    ]),

                Forms\Components\TextInput::make('url')
                    ->label('Link URL')
                    ->url()
                    ->placeholder('https://example.com')
                    ->nullable()
                    ->validationMessages([
                        'url' => 'Please enter a valid URL.',
                    ]),

                Forms\Components\DatePicker::make('due_date')
                    ->label('Due Date')
                    ->nullable()
                    ->helperText('Banner will be automatically hidden after this date.'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->height(60)
                    ->width(120),

                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('position')
                    ->label('Position')
                    ->searchable(),

                Tables\Columns\TextColumn::make('order')
                    ->label('Order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(30)
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->due_date?->isPast() ? 'danger' : 'success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'main_slide'  => 'Main Slide',
                        'main_banner' => 'Main Banner',
                        'sub_banner'  => 'Sub Banner',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit'   => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
