<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Client;
use App\Models\Event;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    protected static ?string $label = "Medical Records";
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-identification';


    public static function form(Form $form): Form
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

                TextArea::make('notes')
                    ->rows(5)
                    ->columnSpan(2),
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
                Placeholder::make("Log Info")
                    ->label("Edit Logs")
                    ->content(function ($record){
                        $logs = $record?->logs ?? [];
                        if (empty($logs)) {
                            return new HtmlString('<p style="font-size:13px;color:#9ca3af;">No edit logs yet.</p>');
                        }

                        $html = '
                        <style>
                            .log-eye { cursor:pointer; position:relative; display:inline-flex; align-items:center; color:#6b7280; }
                            .log-eye:hover { color:#374151; }
                            .log-tooltip {
                                visibility:hidden; opacity:0;
                                position:absolute; left:22px; top:50%; transform:translateY(-50%);
                                z-index:9999;
                                background:#fff; border:1px solid #e5e7eb; border-radius:6px;
                                padding:8px; box-shadow:0 8px 24px rgba(0,0,0,.12);
                                min-width:360px; white-space:nowrap;
                                transition:opacity .15s ease;
                                pointer-events:none;
                            }
                            .log-eye:hover .log-tooltip { visibility:visible; opacity:1; pointer-events:auto; }
                            .log-tooltip table { border-collapse:collapse; font-size:12px; width:100%; }
                            .log-tooltip th { background:#f3f4f6; padding:5px 10px; border:1px solid #e5e7eb; text-align:left; font-weight:600; color:#374151; }
                            .log-tooltip td { padding:5px 10px; border:1px solid #e5e7eb; color:#4b5563; vertical-align:top; white-space:pre-wrap; max-width:200px; }
                        </style>
                        <div style="display:flex;flex-direction:column;gap:6px;">';

                        foreach ($logs as $log) {
                            $timestamp = htmlspecialchars($log['timestamp'] ?? '');
                            $user      = htmlspecialchars($log['user'] ?? 'Unknown');
                            $data      = $log['data'] ?? [];

                            $rows = '';
                            foreach ($data as $field => $values) {
                                $label = htmlspecialchars(ucwords(str_replace('_', ' ', $field)));
                                $old   = htmlspecialchars($values['old'] ?? '');
                                $new   = htmlspecialchars($values['new'] ?? '');
                                $rows .= "<tr><td>{$label}</td><td>{$old}</td><td>{$new}</td></tr>";
                            }

                            $eyeSvg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:15px;height:15px;">'
                                . '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />'
                                . '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />'
                                . '</svg>';

                            $html .= '
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="font-size:13px;color:#374151;"><strong>' . $user . '</strong> (' . $timestamp . ')</span>
                                <span class="log-eye">
                                    ' . $eyeSvg . '
                                    <div class="log-tooltip">
                                        <table>
                                            <thead><tr><th>Field</th><th>Old Value</th><th>New Value</th></tr></thead>
                                            <tbody>' . $rows . '</tbody>
                                        </table>
                                    </div>
                                </span>
                            </div>';
                        }

                        $html .= '</div>';
                        return new HtmlString($html);
                    })
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client_id')
                    ->label('Patient')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn($state) => Client::find($state)?->fullName),
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
                Filter::make('med_type')
                    ->baseQuery(fn (Builder $query) => $query->where('med_type', 'medical'))
                    ->form([])
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->closeModalByClickingAway(false)
                    ->modalWidth('md'),
                Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ListEvents::route('/'),
//            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
            'view' => Pages\ViewEvent::route('/{record}/view'),
        ];
    }
}
