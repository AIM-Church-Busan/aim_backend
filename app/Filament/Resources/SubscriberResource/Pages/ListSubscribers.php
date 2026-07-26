<?php

namespace App\Filament\Resources\SubscriberResource\Pages;

use App\Filament\Resources\SubscriberResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Policy: none — default list page for SubscriberResource.
 */
class ListSubscribers extends ListRecords
{
    protected static string $resource = SubscriberResource::class;
}
