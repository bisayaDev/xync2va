<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\EventResource;
use App\Filament\Resources\MedicationRecordsResource;
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
use Illuminate\Support\HtmlString;
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
                           'MD-Intake' => 'MD-Intake (30 mins)',
                           'MD-FF' => 'MD-FF (15 mins)',
                           'Referral ' => 'Referral (15 mins)',
                        ]),
                    Select::make('client_id')
                        ->required()
                        ->searchable()
                        ->live()
                        ->label('Patient')
                        ->options(
                            Client::all()->mapWithKeys(function ($client) {
                                // Format the date of birth to mm/dd/yyyy
                                $formattedDob = $client->date_of_birth ?
                                    Carbon::parse($client->date_of_birth)->format('m/d/Y') :
                                    'N/A';

                                // Create the custom label with full_name | date_of_birth
                                $label = "{$client->full_name} | {$formattedDob}";

                                return [$client->id => $label];
                            })
                        ),
                    Placeholder::make('anything')
                        ->hiddenLabel()
                        ->reactive()
                        ->content(function (callable $get) {
                            if(!$get('client_id'))
                                return "";
                            $id = $get('client_id');
                            $client = Client::find($id);
                            $name = $client->full_name;

                            if($client->med_type)
                            {
                                return "";
                            }

                            $url = '/app/clients?tableSearch=' . $name;
                            return new HtmlString("<div style='color: red;'>Patient $name doesn't have Med Type. Click <a href='$url' ><u>here</u></a> to set the med type.</div>");
                        }),
                    TimePicker::make('starts_time')
                        ->required()
                        ->live()
                        ->seconds(false)
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if ($state) {
                                // Parse the time into a Carbon instance
                                $startsTime = Carbon::parse($state);
                                $addTime = 15;
                                if($get('type') == 'MD-Intake'){
                                    $addTime = 30;
                                }

                                elseif($get('type') == 'MD-FF' || $get('type') == 'Referral'){
                                    $addTime = 15;
                                }

                                // Add 30 minutes to the start time
                                $endsTime = $startsTime->copy()->addMinutes($addTime)->format('H:i');


                                // Set the ends_time value

                                $set('ends_time', $endsTime);
                            }
                        })
                        ->columnSpan(1),
                    TimePicker::make('ends_time')
                        ->required()
                        ->live()
                        ->seconds(false)
                        ->columnSpan(1),
                    Section::make()
                     ->schema([
                         Placeholder::make('Date of Birth')
                            ->content(function ($get){
                                if($get('client_id'))
                                    return Carbon::make(Client::find($get('client_id'))->date_of_birth)->format('m/d/Y');
                                return "N/A";
                            })->columnSpan(1),
                         Placeholder::make('Phone Number')
                             ->content(function ($get){
                                 if($get('client_id'))
                                     return Client::find($get('client_id'))->phone;
                                 return "N/A";
                             })->columnSpan(1),
                         Placeholder::make('Medication Type')
                             ->content(function ($get){
                                 if($get('client_id'))
                                     return ucfirst(Client::find($get('client_id'))->med_type);
                                 return "N/A";
                             })->columnSpan(1),
                         Placeholder::make('Diagnosis')
                             ->content(function ($get){
                                 if($get('client_id'))
                                     return Client::find($get('client_id'))->diagnosis;
                                 return "N/A";
                             })
                             ->columnSpan(3),
                     ])->columns(3)
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
                        if(!Client::find($data['client_id'])->med_type)
                        {
                            Notification::make()
                                ->title('Missing Medication Type')
                                ->danger()
                                ->body('Patient ' . Client::find($data['client_id'])->full_name . ' doesn\'t have a med type.')
                                ->send();
                            return false;
                        }

                        $data['date'] = Carbon::make($data['date'])->format('m/d/Y');

                        $startsAt = \Carbon\Carbon::createFromFormat('m/d/Y H:i', $data['date'] . ' ' . $data['starts_time']);
                        $endsAt = \Carbon\Carbon::createFromFormat('m/d/Y H:i', $data['date'] . ' ' . $data['ends_time']);

                        $new_event = new Event();
                        $new_event->med_type = Client::find($data['client_id'])->med_type;
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
                    'url' => $event->med_type === "medical" ? EventResource::getUrl(name: 'edit', parameters: ['record' => $event])
                        : MedicationRecordsResource::getUrl(name: 'edit', parameters: ['record' => $event]),

                ]
            )
            ->all();
    }

}
