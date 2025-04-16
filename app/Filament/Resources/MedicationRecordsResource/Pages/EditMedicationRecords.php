<?php

namespace App\Filament\Resources\MedicationRecordsResource\Pages;

use App\Filament\Resources\MedicationRecordsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedicationRecords extends EditRecord
{
    protected static string $resource = MedicationRecordsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
