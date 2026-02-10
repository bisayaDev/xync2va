<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Models\Client;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MedicalRelationManager extends RelationManager
{
    protected static string $relationship = 'medical';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('client_id')
                    ->columnSpanFull()
                    ->disabledOn('edit')
                    ->required()
                    ->searchable()
                    ->live()
                    ->label('Patient')
                    ->options(Client::all()->pluck('full_name', 'id')),
                DatePicker::make('date')
                    ->required()
                    ->disabledOn('edit')
                    ->label('Appointment Date'),
                Select::make('type')
                    ->label('Appointment Type')
                    ->required()
                    ->disabledOn('edit')
                    ->options([
                        'MD-Intake' => 'MD-Intake (30 mins)',
                        'MD-FF' => 'MD-FF (15 mins)',
                        'Referral ' => 'Referral (15 mins)',
                    ]),
                TimePicker::make('starts_at')
                    ->required()
                    ->disabledOn('edit')
                    ->columnSpan(1),
                TimePicker::make('ends_at')
                    ->required()
                    ->disabledOn('edit')
                    ->columnSpan(1),
                Section::make()
                    ->schema([
                        Placeholder::make('Date of Birth')
                            ->content(function ($get){
                                if($get('client_id'))
                                    return Carbon::make(Client::find($get('client_id'))->date_of_birth)->format('F d, Y');
                                return "N/A";
                            }),
                        Placeholder::make('Age')
                            ->content(function ($get){
                                if($get('client_id'))
                                    return Carbon::make(Client::find($get('client_id'))->date_of_birth)->age;
                                return "N/A";
                            }),
                        Placeholder::make('Phone Number')
                            ->content(function ($get){
                                if($get('client_id'))
                                    return Client::find($get('client_id'))->phone;
                                return "N/A";
                            }),
                        Placeholder::make('Client\'s Diagnosis')
                            ->content(function ($get){
                                if($get('client_id'))
                                    return Client::find($get('client_id'))->diagnosis;
                                return "N/A";
                            })->columnSpan(2),
                    ])->columns(2),
                TextArea::make('final_diagnosis')
                    ->label('Final Diagnosis')
                    ->required()
                    ->rows(6)
                    ->columnSpan(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('med_type')
            ->columns([
                TextColumn::make('client_id')
                    ->label('Patient')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn($state) => Client::find($state)->full_name),
                TextColumn::make('type')
                    ->sortable()
                    ->searchable()
                    ->label('Appointment Type'),
                TextColumn::make('starts_at')
                    ->sortable()
                    ->searchable()
                    ->label('Appointment Date')
                    ->formatStateUsing(function($record) {
                        $date = Carbon::make($record->starts_at)->format('m/d/Y | l');
                        return $date;
                    }),
                TextColumn::make('ends_at')
                    ->sortable()
                    ->searchable()
                    ->label('Appointment Time')
                    ->formatStateUsing(function($record) {
                        $start = Carbon::make($record->starts_at)->format('h:i A - ');
                        $end = Carbon::make($record->ends_at)->format('h:i A');
                        return $start . $end;
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
//                Tables\Actions\CreateAction::make(),
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
}
