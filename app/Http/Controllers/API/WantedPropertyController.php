<?php

namespace App\Http\Controllers\API;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use App\Models\WantedProperty;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class WantedPropertyController extends Controller
{
    public function WonProStore(Request $request)
    {

         $validator = Validator::make($request->all(),[
            'buy_or_rent' => 'required|in:buy,rent',
            'governorate' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

              $authUser = JWTAuth::user();

            if (!$authUser) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }



        // إذا المستخدم لديه مكتب واحد فقط
        $office = $authUser->office;

        if ($office) {
            $owner_id = $office->id;
            $owner_type = 'App\Models\Office';
        } else {
            $owner_id = $authUser->id;
            $owner_type = 'App\Models\User';
        }


            $wantedProperty = WantedProperty::create([
                'wanted_Pable_id' => $owner_id, // أو استخدام id متعلق إذا كان تابع لمكتب أو مستخدم
                'wanted_Pable_type' => $owner_type, // المكتب أو المستخدم
                'buy_or_rent' => $request->buy_or_rent,
                'governorate' => $request->governorate,
                'area' => $request->area,
                'budget' => $request->budget,
                'description' => $request->description,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'تم إنشاء الطلب بنجاح.',
                'data' => $wantedProperty,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ أثناء إنشاء الطلب.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getWanPro()
    {
        try {
            $wantedProperties = WantedProperty::with('wantedPropertyable')->get();

            return response()->json([
                'message' => 'تم جلب جميع طلبات العقارات بنجاح.',
                'data' => $wantedProperties,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب الطلبات.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
