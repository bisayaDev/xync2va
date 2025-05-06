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
}
