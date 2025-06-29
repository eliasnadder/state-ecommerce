<?php

// namespace App\Http\Controllers;
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\OfficeFollower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfficeFollowerController extends Controller
{
    public function GetOfficeFollowers($id) {
    //   $user_id = auth()->user()->id;
      $office_id = $id;
      // $followers = OfficeFollower::where('office_id',$id)->with('user')->paginate(20);

    if(!Office::find($id)) {
        return response()->json(['message' => 'this office doesnt exist'], 404);
      }
    $followers = DB::table('office_followers')
        ->join('users', 'office_followers.user_id', '=', 'users.id')
        ->where('office_followers.office_id', $office_id)
        ->select('users.*')
        ->get();

      if($followers->isEmpty()) {
          return response()->json(['message' => 'this office dont have followers'], 404);
      }


      return response()->json(['followers' => $followers], 200);
    }





    // public function follow(Request $request) {
    //     $request->validate([
    //         'office_id' => 'required|exists:offices,id',
    //     ]);
    //   $user_id = auth()->user()->id;

    //   $office = Office::find($request->office_id);
    //   if(!$office) {
    //       return response()->json(['message' => 'not found office'], 404);
    //   }
    //   $office->followers_count += 1;
    //   $office->save();


    //   $followers = new OfficeFollower;
    //   $followers->user_id = $user_id;
    //   $followers->office_id = $request->office_id;
    //   $followers->save();

    //   return response()->json(['message' => 'follow success'], 200);
    // }

    // public function unfollow(Request $request)  {
    //     $request->validate([
    //         'office_id' => 'required|exists:offices,id',
    //     ]);
    //   $user_id = auth()->user()->id;

    //   $office = Office::find($request->office_id);
    //   if(!$office) {
    //       return response()->json(['message' => 'not found office'], 404);
    //   }
    //   $office->followers_count -= 1;
    //   $office->save();

    //   OfficeFollower::where('user_id',$user_id)
    //   ->where('office_id',$request->office_id)->delete();

    //   return response()->json(['message' => 'unfollow success'], 200);
    // }
}
