<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChurchBulletinResource\Pages;
use App\Models\ChurchBulletin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Policy:
 * - Only PDF files are accepted (acceptedFileTypes) — no other file types.
 * - Uses the 'public' disk to match the project's current temporary file
 *   storage setup (see "File Storage Decision" in the architecture doc);
 *   switch the `disk()` call here when the R2 migration happens.
 */
class ChurchBulletinResource extends Resource
{
    protected static ?string $model = ChurchBulletin::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Church Bulletin';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->label('Title')
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('content')
                ->label('Content')
                ->rows(5)
                ->required(),
            Forms\Components\FileUpload::make('pdf_path')
                ->label('PDF file')
                ->disk('public')
                ->directory('bulletins')
                ->acceptedFileTypes(['application/pdf'])
                ->downloadable()
                ->openable()
                ->required(),
            Forms\Components\FileUpload::make('thumbnail_path')
                ->label('Thumbnail image')
                ->disk('public')
                ->directory('bulletins/thumbnails')
                ->image()
                ->openable(),
            Forms\Components\TextInput::make('thumbnail_url')
                ->label('Thumbnail image url (optional)')
                ->url()
                ->helperText('Url to an image that will be used as a thumbnail for the bulletin.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_url')->label('Thumbnail')->circular(),
                Tables\Columns\TextColumn::make('title')->label('Title')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Uploaded at')->dateTime('Y-m-d H:i')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated at')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChurchBulletins::route('/'),
            'create' => Pages\CreateChurchBulletin::route('/create'),
            'edit' => Pages\EditChurchBulletin::route('/{record}/edit'),
        ];
    }
}
