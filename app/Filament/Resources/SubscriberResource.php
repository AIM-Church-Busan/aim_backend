<?php


namespace App\Filament\Resources;

use App\Filament\Resources\SubscriberResource\Pages;
use App\Jobs\DispatchNewsletterJob;
use App\Models\Subscriber;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Policy:
 * - Newsletter sends must always go through DispatchNewsletterJob — never
 *   send synchronously (e.g. looping Mail::send()) from the admin panel.
 * - DeleteAction is reserved for exceptional cases like GDPR-style deletion
 *   requests; normal unsubscribing should go through the visitor-facing
 *   unsubscribe link, not admin deletion.
 */
class SubscriberResource extends Resource
{
    protected static ?string $model = Subscriber::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = '뉴스레터 구독자';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\IconColumn::make('is_confirmed')->boolean()->label('확인됨'),
                Tables\Columns\TextColumn::make('confirmed_at')->dateTime('Y-m-d H:i'),
                Tables\Columns\TextColumn::make('unsubscribed_at')->dateTime('Y-m-d H:i'),
            ])
            ->filters([Tables\Filters\TernaryFilter::make('is_confirmed')->label('확인 여부')])
            ->headerActions([
                Tables\Actions\Action::make('sendNewsletter')
                    ->label('뉴스레터 발송')
                    ->form([
                        Forms\Components\TextInput::make('subject')->label('제목')->required(),
                        Forms\Components\RichEditor::make('body')->label('내용')->required(),
                    ])
                    ->action(function (array $data) {
                        DispatchNewsletterJob::dispatch($data['subject'], $data['body']);
                        Notification::make()->title('발송 큐에 등록되었습니다.')->success()->send();
                    }),
            ])
            ->actions([Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListSubscribers::route('/')];
    }
}
