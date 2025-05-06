<?php

namespace App\Filament\Resources\MeetingResource\RelationManagers;

use App\Models\Passcode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PasscodesRelationManager extends RelationManager
{
    protected static string $relationship = 'passcodes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('person_name')
                    ->required(),
                Forms\Components\TextInput::make('passcode')
                    ->default($this->generateUniquePasscode(8,20))
                    ->readonly()
                    ->required()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('passcode')
            ->columns([
                Tables\Columns\TextColumn::make('passcode'),
                Tables\Columns\TextColumn::make('person_name')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    function generateUniquePasscode($length = 6, $maxAttempts = 10)
    {
        $attempts = 0;

        do {
            // Generate a random alphanumeric passcode
            $characters = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
            $passcode = '';

            for ($i = 0; $i < $length; $i++) {
                $index = random_int(0, strlen($characters) - 1);
                $passcode .= $characters[$index];
            }

            // Check if the passcode exists in the database
            $exists = Passcode::where('passcode', $passcode)->exists();

            // If it doesn't exist, return it
            if (!$exists) {
                return $passcode;
            }

            $attempts++;
        } while ($attempts < $maxAttempts);

        // If we've reached max attempts and still haven't found a unique code
        return null;
    }


}
