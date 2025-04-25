<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class BotcakeHelperController extends Controller
{
    public function isDatesEqual(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'date1' => 'required|date_format:Y-m-d H:i:s',
            'date2' => 'required|date_format:Y-m-d H:i:s',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed. Dates must be in format: YYYY-MM-DD HH:MM:SS',
                'errors' => $validator->errors()
            ], 422);
        }

        // Parse the dates using Carbon
        $date1 = Carbon::createFromFormat('Y-m-d H:i:s', $request->date1);
        $date2 = Carbon::createFromFormat('Y-m-d H:i:s', $request->date2);

        // Perform comparisons
        $result = [
            'date1' => [
                'original' => $request->date1,
                'formatted_date' => $date1->format('Y-m-d'),
                'formatted_time' => $date1->format('H:i:s'),
                'formatted_datetime' => $date1->format('Y-m-d H:i:s'),
                'day_of_week' => $date1->format('l'),
                'timestamp' => $date1->timestamp,
            ],
            'date2' => [
                'original' => $request->date2,
                'formatted_date' => $date2->format('Y-m-d'),
                'formatted_time' => $date2->format('H:i:s'),
                'formatted_datetime' => $date2->format('Y-m-d H:i:s'),
                'day_of_week' => $date2->format('l'),
                'timestamp' => $date2->timestamp,
            ],
            'comparison' => [
                'equal' => $date1->equalTo($date2),
                'date1_before_date2' => $date1->lessThan($date2),
                'date1_after_date2' => $date1->greaterThan($date2),
                'difference_in_seconds' => $date1->diffInSeconds($date2),
                'difference_in_minutes' => $date1->diffInMinutes($date2),
                'difference_in_hours' => $date1->diffInHours($date2),
                'difference_in_days' => $date1->diffInDays($date2),
                'difference_in_days_absolute' => $date1->diffInDays($date2, false),
                'difference_in_months' => $date1->diffInMonths($date2),
                'difference_in_years' => $date1->diffInYears($date2),
                'formatted_difference' => $date1->diffForHumans($date2),
            ],
            'time' => [
                'processed_at' => now()->toIso8601String(),
            ]
        ];

        // Return JSON response
        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }


    public function getNextThursday()
    {
        // Get the current date
        $currentDate = now();

        // Calculate days until next Thursday (where 4 = Thursday in Carbon/DateTime)
        // If today is Thursday, get next week's Thursday
        $daysUntilThursday = (4 - $currentDate->dayOfWeek + 7) % 7;

        // If today is Thursday, we want to get next week's Thursday
        if ($daysUntilThursday === 0) {
            $daysUntilThursday = 7;
        }

        // Add the required number of days to get to next Thursday
        $nextThursday = $currentDate->copy()->addDays($daysUntilThursday);

        $nextThursday->startOfDay();

        // Create an array with the date information
        $response = [
            'next_thursday' => $nextThursday->format('Y-m-d H:i:s'),
            'timestamp' => $nextThursday->timestamp,
            'iso_format' => $nextThursday->toIso8601String()
        ];

        // Return as JSON
        return response()->json($response);
    }

    /**
     * Get the next Friday date from the current date
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNextFriday()
    {
        // Get the current date
        $currentDate = now();

        // Calculate days until next Friday (where 5 = Friday in Carbon/DateTime)
        // If today is Friday, get next week's Friday
        $daysUntilFriday = (5 - $currentDate->dayOfWeek + 7) % 7;

        // If today is Friday, we want to get next week's Friday
        if ($daysUntilFriday === 0) {
            $daysUntilFriday = 7;
        }

        // Add the required number of days to get to next Friday
        $nextFriday = $currentDate->copy()->addDays($daysUntilFriday);

        // Set the time to 00:00:00
        $nextFriday->startOfDay();

        // Create response array with multiple date formats
        $response = [
            'next_friday' => $nextFriday->format('Y-m-d H:i:s'),
            'timestamp' => $nextFriday->timestamp,
            'iso_format' => $nextFriday->toIso8601String()
        ];

        return response()->json($response);
    }

    /**
     * Compare two dates based on a condition
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function compareDates(Request $request)
    {

        // Parse the dates
        $checkDate = $request->get('check_date') === "now" ? now()->startOfDay() : Carbon::parse($request->get('check_date'))->startOfDay();
        $refDate = Carbon::parse($request->get('ref_date'))->startOfDay();
        $condition = $request->get('condition');

        // Perform the comparison based on condition
        $result = false;

        switch ($condition) {
            case 'before':
                $result = $checkDate->lt($refDate);
                break;
            case 'after':
                $result = $checkDate->gt($refDate);
                break;
            case 'equal':
                $result = $checkDate->eq($refDate);
                break;
        }

        // Return the result
        return response()->json([
            'value' => ($result ? 'yes' : 'no') . ' | ' . $checkDate->format('Y-m-d H:i:s') . ' | ' . $condition . ' | ' . $refDate->format('Y-m-d H:i:s')  ,
            'condition' => $condition,
            'check_date' => $checkDate->format('Y-m-d H:i:s'),
            'ref_date' => $refDate->format('Y-m-d H:i:s'),
        ]);
    }
}
