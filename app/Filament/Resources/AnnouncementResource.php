<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Announcements';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Announcement Details')
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

                    Forms\Components\Select::make('category')
                        ->required()
                        ->options(Announcement::CATEGORIES)
                        ->default(Announcement::CATEGORY_GENERAL)
                        ->validationMessages([
                            'required' => 'Please select a category.',
                        ]),
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
                        ->helperText('Announcement will be automatically unpublished after this date.')
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
                        ->directory('announcements/thumbnails')
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

            Forms\Components\Section::make('Publishing')
                ->schema([
                    Forms\Components\Toggle::make('is_published')
                        ->label('Published')
                        ->default(false),

                    Forms\Components\Toggle::make('is_pinned')
                        ->label('Pinned')
                        ->default(false),

                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('Scheduled Publish Time')
                        ->helperText('Leave empty to publish immediately.')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_pinned')
                    ->label('Pinned')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary' => Announcement::CATEGORY_GENERAL,
                        'success' => Announcement::CATEGORY_CHILDREN,
                        'warning' => Announcement::CATEGORY_OFFERING,
                    ]),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
            ])
            ->defaultSort('is_pinned', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(Announcement::CATEGORIES),

                Tables\Filters\TernaryFilter::make('is_pinned')
                    ->label('Pinned'),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),

                Tables\Filters\Filter::make('expired')
                    ->label('Expired (past due date)')
                    ->query(fn (Builder $query) => $query->whereNotNull('due_date')
                        ->whereDate('due_date', '<', now()->toDateString())),
            ])
            ->actions([
                Tables\Actions\Action::make('togglePin')
                    ->label(fn (Announcement $record) => $record->is_pinned ? 'Unpin' : 'Pin')
                    ->icon(fn (Announcement $record) => $record->is_pinned ? 'heroicon-o-map-pin' : 'heroicon-o-map-pin')
                    ->action(fn (Announcement $record) => $record->update(['is_pinned' => !$record->is_pinned])),

                Tables\Actions\Action::make('togglePublish')
                    ->label(fn (Announcement $record) => $record->is_published ? 'Unpublish' : 'Publish')
                    ->icon(fn (Announcement $record) => $record->is_published ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->action(fn (Announcement $record) => $record->update(['is_published' => !$record->is_published])),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index'  => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit'   => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
