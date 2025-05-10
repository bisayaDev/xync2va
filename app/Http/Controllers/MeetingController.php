<?php

namespace App\Http\Controllers;

use App\Models\Passcode;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function getMeeting(Request $request, $passcode)
    {
        $passcode = Passcode::where('passcode', $passcode)->with('meeting')->first();
        if (!$passcode) {
            return response()->json(['error'=>'Something went wrong!','message' => 'Passcode not found'], 404);
        }

        return $passcode;
    }

    public function updatePasscode(Request $request, $passcode, $status)
    {
        $passcode = $passcode = Passcode::where('passcode', $passcode)->first();

        if (!$passcode) {
            return response()->json(['error'=>'Something went wrong!','message' => 'Passcode not found'], 404);
        }

        if($status === 'join' && !$passcode->has_joined){
            $passcode->update([
                'has_joined' => true,
                'date_time_joined' => now(),
            ]);
        }elseif($status === 'left') {
            $passcode->update([
                'date_time_left' => now(),
            ]);
        }else{
            return response()->json(['error'=>'Something went wrong!','message' => 'Route not found or passcode already used.'], 404);
        }

        return response()->json(['status'=>'success','message' => 'Passcode updated.'], 200);
    }

    public function insertAction(Request $request)
    {

        // Validate the request
        $validated = $request->validate([
            'passcode' => 'required|string',
            'action' => 'required|string',
            'content' => 'required|string',
        ]);

        // Find the passcode record
        $passcodeRecord = Passcode::where('passcode', $request->input('passcode'))->first();

        if (!$passcodeRecord) {
            return response()->json(['message' => 'Passcode not found'], 404);
        }

        // Create the new log entry
        $newLog = [
            'action' => $request->input('action'),
            'content' => $request->input('content'),
            'timestamp' => now(),
        ];

        // Get existing logs and append the new log
        $currentLogs = $passcodeRecord->logs ?? [];
        $currentLogs[] = $newLog;

        // Update the logs field
        $passcodeRecord->logs = $currentLogs;
        $passcodeRecord->save();

        return response()->json([
            'message' => 'Log entry added successfully',
            'logs' => $passcodeRecord->logs,
        ]);
    }
}
