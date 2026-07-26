<?php

namespace App\Filament\Resources\ChurchBulletinResource\Pages;

use App\Filament\Resources\ChurchBulletinResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChurchBulletins extends ListRecords
{
    protected static string $resource = ChurchBulletinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
