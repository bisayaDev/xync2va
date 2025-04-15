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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?int $navigationSort = 2;

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
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date_of_birth')
                    ->formatStateUsing(fn($state)=> date('m/d/Y', strtotime($state)))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->searchable()
                    ->label('Age')
                    ->sortable()
                    ->formatStateUsing(fn($record)=> Carbon::make($record->date_of_birth)->age),
                TextColumn::make('phone')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('diagnosis')
                    ->searchable()
                    ->sortable()
                    ->words(8)
                    ->tooltip(fn($state) => $state),
            ])
            ->filters([
                //
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
