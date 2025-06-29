<?php

namespace App\Http\Controllers\API;

use App\Models\Office;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class OfficeController extends Controller
{
    public function OfficeStore(Request $request)
{
    $user = auth()->user();


    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'location' => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'فشل التحقق من البيانات.',
            'errors' => $validator->errors(),
        ], 422);
    }


    $office = Office::create([
        'owner_id' => $user->id,
        'owner_type' => get_class($user), // مثال: App\Models\User
        'name' => $request->name,
        'description' => $request->description,
        'location' => $request->location,
    ]);

    return response()->json([
        'message' => 'تم إنشاء المكتب بنجاح.',
        'office' => $office,
    ], 201);
}

        public function officeProperties($id)
{
    try {

        //     $authUser = auth()->user();

        // if (!$authUser) {
        //     return response()->json([
        //         'message' => 'غير مصرح.',
        //     ], 401);
        // }


        // $office = $authUser->office;

        $office = Office::find($id);

        if (!$office) {
            return response()->json(['message' => 'لا يوجد هذا المكتب .'], 404);
        }
        // if (!$office) {
        //     return response()->json([
        //         'message' => 'لا يوجد مكتب مرتبط بهذا المستخدم.',
        //     ], 404);
        // }


        $properties = $office->properties()->latest()->get();

        return response()->json([
            'message' => 'تم جلب العقارات الخاصة بالمكتب بنجاح.',
            'data' => $properties,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
        'error' => 'Something went wrong',
        'message' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile(),
    ], 500);
    }
}

        public function getAllOfficePropertyVideos($id)
{
    try {
        // $authUser = JWTAuth::parseToken()->authenticate();

        // if (!$authUser) {
        //     return response()->json(['message' => 'غير مصرح.'], 401);
        // }

        $office = Office::find($id);

        if (!$office) {
            return response()->json(['message' => 'لا يوجد هذا المكتب .'], 404);
        }


        $propertiesWithVideos = $office->properties()->with('video')->get();

        // هون رح يعمل فلتره العقارات يلي ما الها فيديوهات بشيلا وبيرجع برتبا
        $videos = $propertiesWithVideos->pluck('video')->filter()->values();

        return response()->json([
            'message' => 'تم جلب فيديوهات العقارات بنجاح.',
            'data' => $videos,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'حدث خطأ أثناء جلب الفيديوهات.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
        public function getOfficeWantedProperties(Request $request)
{
    try {
        $authUser = JWTAuth::parseToken()->authenticate();

        if (!$authUser) {
            return response()->json([
                'message' => 'غير مصرح.',
            ], 401);
        }

        $office = $authUser->office;

        if (!$office) {
            return response()->json([
                'message' => 'لا يوجد مكتب مرتبط بهذا المستخدم.',
            ], 404);
        }

        $wantedProperties = $office->wantedProperties()->latest()->get();

        return response()->json([
            'message' => 'تم جلب الطلبات المطلوبة الخاصة بالمكتب بنجاح.',
            'data' => $wantedProperties,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'حدث خطأ أثناء جلب الطلبات.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function getOfficePropertyCount($id)
{
    try {
        // $authUser = JWTAuth::parseToken()->authenticate();

        // if (!$authUser) {
        //     return response()->json(['message' => 'غير مصرح'], 401);
        // }
        // $office = $authUser->office;

        $office = Office::find($id);

        if (!$office) {
            return response()->json(['message' => 'لا يوجد هذا المكتب .'], 404);
        }

        $count = $office->properties()->count();

        return response()->json([
            'message' => 'تم جلب عدد العقارات بنجاح.',
            'count' => $count,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'حدث خطأ أثناء جلب عدد العقارات.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


        public function followOffice(Request $request, $officeId)
{
    try {
        $authUser = JWTAuth::parseToken()->authenticate();

        if (!$authUser) {
            return response()->json(['message' => 'غير مصرح, يجب تسجيل الخول اولا'], 401);
        }

        $office = Office::find($officeId);

        if (!$office) {
            return response()->json(['message' => 'المكتب غير موجود'], 404);
        }

        // تحقق إذا المتابعة موجودة
        $alreadyFollowing = $office->followers()->where('user_id', $authUser->id)->exists();

        if ($alreadyFollowing) {
            return response()->json(['message' => 'أنت تتابع هذا المكتب مسبقًا'], 409);
        }

        // تنفيذ المتابعة
        $office->followers()->attach($authUser->id);

        // زيادة عدد المتابعين
        $office->increment('followers_count');

        return response()->json(['message' => 'تمت متابعة المكتب بنجاح'], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'حدث خطأ أثناء المتابعة',
            'error' => $e->getMessage()
        ], 500);
    }
}


        public function getFollowersCount($officeId)
{
    $office = Office::find($officeId);

    if (!$office) {
        return response()->json(['message' => 'المكتب غير موجود'], 404);
    }

    $count = $office->followers()->count();

    return response()->json([
        'message' => 'عدد المتابعين للمكتب',
        'followers_count' => $count,
    ], 200);
}//تابع يجلب معلومات المكتب
public function show($id)
{
    try {
        // جلب المكتب أو إرجاع 404 إذا غير موجود
        $office = Office::findOrFail($id);

        // زيادة عدد المشاهدات بشكل أوتوماتيكي
        $office->increment('views');

        // إرجاع بيانات المكتب
        return response()->json([
            'message' => 'تم جلب بيانات المكتب بنجاح.',
            'data' => $office,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'حدث خطأ أثناء جلب بيانات المكتب.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
//تابع يجلب عدد المشاهدين
public function getOfficeViews($office_id)
{
    $office = Office::find($office_id);

    if (!$office) {
        return response()->json([
            'message' => 'المكتب غير موجود.',
        ], 404);
    }

    return response()->json([
        'message' => 'تم جلب عدد المشاهدين بنجاح.',
        'office_id' => $office_id,
        'views' => $office->views,
    ], 200);
}

}
