<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {

        $oldData = array_key_exists('data', $this->oldFormState) ? $this->oldFormState["data"] : [];
        $log = [
            "timestamp" => now()->format('Y-m-d H:i:s'),
            "user" => Auth()->user()->name
        ];
        $has_edit = false;

        $resultData = [];

        foreach ($data as $key => $value) {
            if($value != $oldData[$key]) {
                $resultData[$key] = [
                    "new" => $value,
                    "old" => $oldData[$key],
                ];
                $has_edit = true;
            }
        }
        $log["data"] = $resultData;

        if($has_edit && !empty($oldData)) {
            $dummyArray = $oldData["logs"];
            $dummyArray[] = $log;

            if($oldData["logs"] == []) {
                $data["logs"] = [$log];
            }else{
                $data["logs"] = $dummyArray;
            }
        }

        return $data;
    }
}
