<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\EventResource;
use App\Models\Client;
use App\Models\Event;
use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    public string|null|\Illuminate\Database\Eloquent\Model $model = Event::class;

    protected function headerActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Event Today')
                ->color(Color::hex("#01A2E6"))
                ->form([
                    DatePicker::make('date')
                        ->required(),
                    Select::make('type')
                        ->required()
                        ->options([
                            'Initial' => 'Initial',
                            'Ongoing' => 'Ongoing',
                            'Follow-up' => 'Follow-up',
                        ]),
                    Select::make('client_id')
                        ->required()
                        ->searchable()
                        ->live()
                        ->label('Client / Patient')
                        ->options(Client::all()->pluck('full_name', 'id')),
                    TimePicker::make('starts_time')
                        ->required()
                        ->columnSpan(1),
                    TimePicker::make('ends_time')
                        ->required()
                        ->columnSpan(1),
                    Section::make()
                     ->schema([
                         Placeholder::make('Client\' Birthday')
                            ->content(function ($get){
                                if($get('client_id'))
                                    return Carbon::make(Client::find($get('client_id'))->date_of_birth)->format('m/d/Y');
                                return "N/A";
                            }),
                         Placeholder::make('Client\'s Phone Number')
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
                             })
                             ->columnSpan(2),
                     ])->columns(2)
                ])
                ->mountUsing(
                    function (Form $form, array $arguments) {
                        if(!array_key_exists('start', $arguments)) {
                            $arguments['start'] = Carbon::now();
                        }
                        $form->fill([
                            'date' => Carbon::make($arguments['start'])->format('m/d/Y'),
                        ]);
                    }
                )
                ->action(
                    function (Form $form) {
                        $data = $form->getState();
                        $data['date'] = Carbon::make($data['date'])->format('m/d/Y');
                        $startsAt = \Carbon\Carbon::createFromFormat('m/d/Y H:i:s', $data['date'] . ' ' . $data['starts_time']);
                        $endsAt = \Carbon\Carbon::createFromFormat('m/d/Y H:i:s', $data['date'] . ' ' . $data['ends_time']);

                        $new_event = new Event();
                        $new_event->client_id = $data['client_id'];
                        $new_event->date = date('Y-m-d', strtotime($data['date']));
                        $new_event->type = $data['type'];
                        $new_event->starts_at = $startsAt;
                        $new_event->ends_at = $endsAt;
                        $new_event->created_by = Auth()->id();
                        $new_event->save();

                        if ($new_event) {
                            Notification::make()
                                ->title('New Record')
                                ->success()
                                ->body('Added successfully!')
                                ->send();
                            return redirect(request()->header('Referer'));
                        } else {
                            Notification::make()
                                ->title('New Record')
                                ->danger()
                                ->body('Failed to add new record!')
                                ->send();
                        }
                    }
                )
        ];
    }

    public function fetchEvents(array $info): array
    {
        return Event::query()
            ->where('starts_at', '>=', $info['start'])
            ->where('ends_at', '<=', $info['end'])
            ->get()
            ->map(
                fn (Event $event) => [
                    'id' => $event->id,
                    'title' => Client::find($event->client_id)->full_name,
                    'start' => $event->starts_at,
                    'end' => $event->ends_at,
                    'url' => EventResource::getUrl(name: 'edit', parameters: ['record' => $event]),
//                    'shouldOpenUrlInNewTab' => true
                ]
            )
            ->all();
    }

}
