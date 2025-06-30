<?php

namespace App\Http\Controllers\API;

use App\Models\Property;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\User;
use App\Traits\UploadImagesTrait;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PropertiesController extends Controller
{

    use UploadImagesTrait;

    public function propertyStore(Request $request)
    {


        $validator = Validator::make($request->all(), [
            //     'title' => 'required|string|max:255',
            //     'description' => 'required|string',
            //     'price' => 'required|numeric',
            //     'location' => 'required|string',
            //     'latitude' => 'required|numeric',
            //     'longitude' => 'required|numeric',
            //     'area' => 'nullable|numeric',
            //     'floor_number' => 'nullable|integer',
            //     'ad_type' => 'required|in:sale,rent',
            //     'type' => 'required|in:apartment,villa,office,land,commercial,farm,building,chalet',
            //     'status' => 'required|in:sale,sold,rent',
            //     'bathrooms' => 'required|integer',
            //     'rooms' => 'required|integer',
            //     'seller_type' => 'required|in:owner,agent,developer',
            //     'direction' => 'required|string',
            //     'furnishing' => 'required|in:furnished,unfurnished,semi-furnished',
            //     'url' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            //     'Vurl' => 'nullable|mimes:mp4,mov,avi,wmv|max:10000',
            //     'is_offer' => 'nullable|boolean',
            //     'offer_expires_at' => 'required_if:is_offer,true|nullable|date',
            //    'currency' => 'nullable|in:SYP,USD,EUR',
            //     'features' => 'nullable|string',
            'owner_id' => 'required|integer|exists:users,id', // or offices, depending on morph
            'owner_type' => 'required|string|in:App\\Models\\User,App\\Models\\Office',

            'ad_number' => 'required|string|unique:properties,ad_number',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'area' => 'nullable|numeric|min:0',
            'floor_number' => 'nullable|integer|min:0',
            'ad_type' => 'required|in:sale,rent',
            'type' => 'required|in:apartment,villa,office,land,commercial,farm,building,chalet',
            'status' => 'required|in:available,sold,rented',
            'is_offer' => 'boolean',
            'offer_expires_at' => 'nullable|date|after:today',
            'currency' => 'required|string|size:3', // e.g. USD, SAR
            'views' => 'integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'rooms' => 'required|integer|min:0',
            'seller_type' => 'required|in:owner,agent,developer',
            'direction' => 'required|string|max:50',
            'furnishing' => 'required|in:furnished,unfurnished,semi-furnished',
            'features' => 'nullable|string',
            'is_available' => 'required|boolean',
        ]);


        try {
            DB::beginTransaction();

            $authUser = JWTAuth::user();

            if (!$authUser) {
                return response()->json(['error' => 'Unauthorized'], 401);  // إذا لم يتم العثور على المستخدم
            }

            // التحقق مما إذا كان المستخدم مرتبط بمكتب
            if ($authUser->office) {
                $owner_id = $authUser->office->id;
                $owner_type = 'App\Models\Office';

                // $office = Office::find($authUser->office->id);
                // if($office->ad_counter == 0) {
                //     return response()->json(['message' => 'you use the free 2 ads'], 400);
                // }
            } else {
                $owner_id = $authUser->id;
                $owner_type = 'App\Models\User';
            }
            $user = User::find($authUser->id);
            if ($user->ad_counter == 0) {
                return response()->json(['message' => 'you use the free 2 ads'], 400);
            }


            $property = Property::create([
                'owner_id' => $owner_id,
                'owner_type' => $owner_type,
                'ad_number' => uniqid('ad_'),
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'location' => $request->location,
                'latitude' => floatval(trim($request->latitude)),
                'longitude' => floatval(trim($request->longitude)),
                'area' => $request->area ? floatval(trim($request->area)) : null,
                'floor_number' => $request->floor_number, // عمود رقم الطابق
                'ad_type' => $request->ad_type, // نوع الإعلان (بيع/تأجير)
                'type' => $request->type,
                'status' => $request->status,
                'is_offer' => $request->is_offer ?? false,
                'offer_expires_at' => $request->offer_expires_at ?? now()->addDays(5),
                'currency' => $request->currency ?? 'USD',
                'views' => 0,
                'bathrooms' => $request->bathrooms,
                'rooms' => $request->rooms,
                'seller_type' => $request->seller_type,
                'direction' => $request->direction,
                'furnishing' => $request->furnishing,
                'features' => $request->features,
                'is_available' => $request->is_available,
            ]);


            $path = $this->uploadImage($request->file('url'), 'property');


            $property->images()->create([
                'url' => $path,
            ]);
            if ($request->hasFile('Vurl')) {
                $file = $request->file('Vurl');
                $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('properties/videos', $fileName, 'public');
                $videoUrl = 'storage/' . $filePath;

                if ($videoUrl) {
                    // إنشاء الفيديو في جدول الفيديوهات
                    $property->video()->create([
                        'vurl' => $videoUrl,  // التأكد من تمرير رابط الفيديو
                        'videoable_id' => $property->id,
                        'videoable_type' => Property::class
                    ]);
                } else {
                    return response()->json(['error' => 'Video URL could not be generated'], 400);
                }
            }

            DB::commit();
            // if ($authUser->office) {
            //     $office->ad_counter -= 1;
            //     $office->save();
            // } else {
            $user->ad_counter -= 1;
            $user->save();
            // }

            return response()->json([
                'message' => 'تم إنشاء الإعلان بنجاح.',
                'data' => $property->load('images', 'video'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function changePropertyStatus(Request $request, $propertyId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:available,sold,rented',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $authUser = JWTAuth::parseToken()->authenticate();

            if (!$authUser) {
                return response()->json(['message' => 'غير مصرح'], 401);
            }

            $property = Property::find($propertyId);

            if (!$property) {
                return response()->json(['message' => 'العقار غير موجود'], 404);
            }

            // تحقق إذا العقار يخص المستخدم أو مكتبه (حسب نظامك)
            // مثلاً، إذا عندك علاقة بين العقار وصاحب أو المكتب:

            if ($property->owner_id !== $authUser->id) {
                return response()->json(['message' => 'غير مصرح بتعديل هذا العقار'], 403);
            }

            // تحديث الحالة
            $property->status = $request->status;
            $property->save();

            return response()->json([
                'message' => 'تم تحديث حالة العقار بنجاح',
                'property' => $property,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء تحديث الحالة',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    //تابع يجلب جميع العقارات
    public function index()
    {
        $properties = Property::with(['owner', 'images', 'video'])->paginate(20);
        if (!$properties) {
            return response()->json(['message' => 'not found'], 404);
        }

        return response()->json($properties);
    }

    public function show($id)
    {
        $property = Property::find($id)->with(['owner', 'images', 'video'])->get();

        if (!$property) {
            return response()->json(['message' => 'not found'], 404);
        }
        $property->views += 1;
        $property->save();

        $relaitedproperties = Property::where('type', $property->type)
            ->where('ad_type', $property->ad_type)
            ->with(['owner', 'images', 'video'])->get();

        return response()->json(['property' => $property, 'relaitedproperties' => $relaitedproperties]);
    }

    public function getPropertyVideos()
    {
        $videos = Video::where('videoable_type', Property::class)
            ->with('videoable') // هذا سيجلب معلومات العقار المرتبط
            ->get();

        if ($videos->isEmpty()) {
            return response()->json([
                'message' => 'لا توجد فيديوهات لعقارات حالياً.',
                'data' => []
            ], 404); // يمكن تغيير الكود إلى 200 لو أردت
        }

        return response()->json([
            'message' => 'تم جلب فيديوهات العقارات بنجاح.',
            'data' => $videos
        ]);
    }
    //تابع بجيب العقارات يلي عليها عرض آخر 3 أيام بدءاً من الأحدث
    public function getRecentOffers()
    {
        // حساب التاريخ قبل 3 أيام
        $recentDate = now()->subDays(3);

        // جلب العقارات التي عليها عروض خلال آخر 3 أيام
        $properties = Property::where('is_offer', true)
            ->where('created_at', '>=', $recentDate)
            ->orderBy('created_at', 'desc')
            ->get();

        // التحقق من وجود عروض
        if ($properties->isEmpty()) {
            return response()->json(['message' => 'لا توجد عروض حالياً'], 200);
        }

        return response()->json($properties);
    }


    public function searchByAdNumber($ad_number)
    {
        $property = Property::with(['owner', 'images', 'video'])
            ->where('ad_number', $ad_number)
            ->first();

        if (!$property) {
            return response()->json(['message' => 'Property not found'], 404);
        }

        return response()->json($property);
    }

    public function filter(Request $request)
    {
        $query = Property::with(['owner', 'images', 'video']);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('views')) {
            if ($request->views === 'most') {
                $query->orderBy('views', 'desc');
            } elseif ($request->views === 'least') {
                $query->orderBy('views', 'asc');
            }
        }
        $properties = $query->paginate(20);

        if ($properties->isEmpty()) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($properties);
    }
    public function availability(Request $request)
    {
        // $user_id = JWTAuth::user()->office->id;

        $authUser = JWTAuth::user();
        if ($authUser->office) {
            $owner_id = $authUser->office->id;
        } else {
            $owner_id = $authUser->id;
        }

        $request->validate([
            'property_id' => 'required|string|max:255',
            'availability' => 'required|accepted',
        ]);
        $property = Property::where('id', $request->property_id)
            ->where('owner_id', $owner_id)->get();

        if (!$property) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if ($request->availability == $property->is_available) {
            return response()->json(['message' => 'its already same'], 400);
        }
        $property->is_available = !$property->is_available;
        $property->save();

        return response()->json(['message' => 'done']);
    }

    public function receiveCard(Request $request)
    {
        // Validate incoming data
        $validated = $request->validate([
            'card_number' => 'required|digits_between:13,19',
            'expiry_month' => 'required|digits:2',
            'expiry_year' => 'required|digits:4',
            'cvv' => 'required|digits_between:3,4',
        ]);
        $authUser = JWTAuth::user();

        if (!$authUser) {
            return response()->json(['error' => 'Unauthorized'], 401);  // إذا لم يتم العثور على المستخدم
        }

        // التحقق مما إذا كان المستخدم مرتبط بمكتب
        // if ($authUser->office) {
        //     $owner_id = $authUser->office->id;
        //     $owner_type = 'App\Models\Office';

        //     $office = Office::find($authUser->office->id);
        //     $office->ad_counter += 10;
        //     $office->save();
        // } else {
        $owner_id = $authUser->id;
        $owner_type = 'App\Models\User';

        $user = User::find($authUser->id);
        $user->ad_counter += 10;
        $user->save();
        // }


        // Dummy response
        return response()->json([
            'status' => 'success',
            'message' => 'Card data received (demo only).',
            'user' => $authUser,
        ]);
    }
}
