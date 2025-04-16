<?php

namespace App\Filament\Resources\MedicationRecordsResource\Pages;

use App\Filament\Resources\MedicationRecordsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedicationRecords extends ListRecords
{
    protected static string $resource = MedicationRecordsResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }
}
