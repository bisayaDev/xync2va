<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?int $navigationSort = 3;
    protected static ?string $label = "Patients";

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('first_name'),
                TextInput::make('last_name'),
                DatePicker::make('date_of_birth'),
                TextInput::make('phone'),
                TextArea::make('diagnosis'),
                Forms\Components\Select::make('med_type')
                    ->options([
                        'medical' => 'Medical',
                        'medication' => 'Medication'
                    ]),
                FileUpload::make('documents')
                    ->multiple()
                    ->directory('clients-documents')
                    ->previewable()
                    ->getUploadedFileNameForStorageUsing(
                        function (TemporaryUploadedFile $file, $get){
                            $fullName = $get('first_name') . $get('last_name') . '-';
                            return str($file->getClientOriginalName())->prepend($fullName);
                        },
                    )
                    ->downloadable()
                    ->openable()
                    ->appendFiles(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date_of_birth')
                    ->label('DOB')
                    ->date('m/d/Y')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('age')
                    ->label('AGE')
                    ->sortable(query: function ($query, string $direction): void {
                        $query->orderByRaw("(julianday('now') - julianday(date_of_birth)) / 365.25 {$direction}");
                    }),
                TextColumn::make('phone')
                    ->label('PHONE')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('diagnosis')
                    ->searchable()
                    ->sortable()
                    ->words(8)
                    ->tooltip(fn($state) => $state),
                TextColumn::make('med_type')
                    ->label('Med Type')
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        'medical' => 'info',
                        'medication' => 'warning',
                    })
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('date_of_birth')
                    ->form([
                        DatePicker::make('date_of_birth')
                            ->displayFormat('m/d/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['date_of_birth'],
                            fn (Builder $query, $date): Builder =>
                            $query->whereDate('date_of_birth', $date)
                        );
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->modalWidth('md'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
//            'create' => Pages\CreateClient::route('/create'),
//            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
