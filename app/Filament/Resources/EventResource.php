<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Events';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Event Details')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->validationMessages([
                            'required' => 'Please enter a title.',
                            'max'      => 'Title may not be greater than 255 characters.',
                        ]),

                    Forms\Components\RichEditor::make('description')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Date & Time')
                ->schema([
                    Forms\Components\DatePicker::make('starts_at')
                        ->required()
                        ->label('Start Date')
                        ->validationMessages([
                            'required' => 'Please select a start date.',
                        ]),

                    Forms\Components\DatePicker::make('ends_at')
                        ->label('End Date')
                        ->after('starts_at')
                        ->validationMessages([
                            'after' => 'End date must be after the start date.',
                        ]),

                    Forms\Components\TimePicker::make('start_time')
                        ->label('Start Time')
                        ->seconds(false),

                    Forms\Components\TimePicker::make('end_time')
                        ->label('End Time')
                        ->seconds(false)
                        ->after('start_time')
                        ->validationMessages([
                            'after' => 'End time must be after the start time.',
                        ]),

                    Forms\Components\DatePicker::make('due_date')
                        ->label('Due Date')
                        ->helperText('Event will be automatically unpublished after this date.')
                        ->after('starts_at'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Location')
                ->schema([
                    Forms\Components\TextInput::make('location')
                        ->label('Venue Name')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('location_address')
                        ->label('Address')
                        ->maxLength(255),
                ])
                ->columns(2),

            Forms\Components\Section::make('Thumbnail')
                ->schema([
                    Forms\Components\Radio::make('thumbnail_type')
                        ->label('Image Type')
                        ->options([
                            'upload' => 'File Upload',
                            'url'    => 'External URL',
                        ])
                        ->default('upload')
                        ->live()
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('thumbnail_path')
                        ->label('Upload Image')
                        ->image()
                        ->disk('public')
                        ->directory('events/thumbnails')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(5120)
                        ->visible(fn (Forms\Get $get) => $get('thumbnail_type') === 'upload')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('thumbnail_url')
                        ->label('Image URL')
                        ->url()
                        ->maxLength(255)
                        ->visible(fn (Forms\Get $get) => $get('thumbnail_type') === 'url')
                        ->columnSpanFull()
                        ->validationMessages([
                            'url' => 'Please enter a valid URL.',
                        ]),
                ])
                ->columns(2),

            Forms\Components\Section::make('Registration')
                ->schema([
                    Forms\Components\TextInput::make('capacity')
                        ->label('Max Capacity')
                        ->numeric()
                        ->minValue(1)
                        ->helperText('Leave empty for unlimited.')
                        ->validationMessages([
                            'numeric' => 'Capacity must be a number.',
                            'min'     => 'Capacity must be at least 1.',
                        ]),

                    Forms\Components\TextInput::make('remaining_spots')
                        ->label('Remaining Spots')
                        ->numeric()
                        ->disabled()
                        ->helperText('Auto-calculated from registrations.'),

                    Forms\Components\TextInput::make('external_link')
                        ->label('External Link')
                        ->url()
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->validationMessages([
                            'url' => 'Please enter a valid URL.',
                            'max' => 'Link may not be greater than 255 characters.',
                        ]),
                ])
                ->columns(2),

            Forms\Components\Section::make('Publishing')
                ->schema([
                    Forms\Components\Toggle::make('is_published')
                        ->label('Published')
                        ->default(false),

                    Forms\Components\Toggle::make('is_banner')
                        ->label('Banner')
                        ->default(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_spots')
                    ->label('Spots Left')
                    ->default('Unlimited'),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                Tables\Columns\IconConlumn::make('is_banner')
                    ->label('Banner')
                    ->boolean(),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),

                Tables\Filters\Filter::make('upcoming')
                    ->label('Upcoming')
                    ->query(fn (Builder $query) => $query->where('starts_at', '>=', now()->toDateString())),

                Tables\Filters\Filter::make('expired')
                    ->label('Expired (past due date)')
                    ->query(fn (Builder $query) => $query->whereNotNull('due_date')
                        ->whereDate('due_date', '<', now()->toDateString())),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('togglePublish')
                    ->label(fn (Event $record) => $record->is_published ? 'Unpublish' : 'Publish')
                    ->icon(fn (Event $record) => $record->is_published ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->action(fn (Event $record) => $record->update(['is_published' => !$record->is_published])),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit'   => Pages\EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
