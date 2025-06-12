<?php

namespace App\Http\Controllers\API;
use App\Models\User;
use App\Models\Favorite;
use App\Models\Property;
use App\Models\Requestt;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Traits\UploadImagesTrait;

class RequestController extends Controller
{
    
public function getPendingRequests(Request $request)
{
    $user = $request->user(); // يحصل على المستخدم المسجل حالياً

    // جلب الطلبات المعلقة لهذا المستخدم
    $pendingRequests = Requestt::where('user_id', $user->id)
                                   ->where('status', 'pending')
                                   ->get();

    if ($pendingRequests->isEmpty()) {
        return response()->json([
            'message' => 'لا توجد طلبات معلقة لهذا المستخدم.',
        ], 200);
    }

    return response()->json([
        'message' => 'تم جلب الطلبات بنجاح.',
        'data' => $pendingRequests,
    ], 200);
}
public function getacceptedRequests(Request $request)
{
    $user = $request->user(); // يحصل على المستخدم المسجل حالياً

    // جلب الطلبات المقبولة لهذا المستخدم
    $acceptedRequests = Requestt::where('user_id', $user->id)
                                   ->where('status', 'accepted')
                                   ->get();

    if ($acceptedRequests->isEmpty()) {
        return response()->json([
            'message' => 'لا توجد طلبات مقبولة لهذا المستخدم.',
        ], 200);
    }

    return response()->json([
        'message' => 'تم جلب الطلبات بنجاح.',
        'data' => $acceptedRequests,
    ], 200);
}
public function getrejectedRequests(Request $request)
{
    $user = $request->user(); // يحصل على المستخدم المسجل حالياً

    // جلب الطلبات المرفوضة لهذا المستخدم
    $rejectedRequests = Requestt::where('user_id', $user->id)
                                   ->where('status', 'rejected')
                                   ->get();

    if ($rejectedRequests->isEmpty()) {
        return response()->json([
            'message' => 'لا توجد طلبات مرفوضة لهذا المستخدم.',
        ], 200);
    }

    return response()->json([
        'message' => 'تم جلب الطلبات بنجاح.',
        'data' => $rejectedRequests,
    ], 200);
}
}
