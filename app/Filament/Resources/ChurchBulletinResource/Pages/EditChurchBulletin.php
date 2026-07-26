<?php

namespace App\Filament\Resources\ChurchBulletinResource\Pages;

use App\Filament\Resources\ChurchBulletinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChurchBulletin extends EditRecord
{
    protected static string $resource = ChurchBulletinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
