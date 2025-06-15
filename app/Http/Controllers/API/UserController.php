<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Favorite;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Traits\UploadImagesTrait;


class UserController extends Controller
{
    use UploadImagesTrait;

    public function index()
    {
        return '2';
    }
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // محاولة تسجيل الدخول
        $credentials = $request->only('phone', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['message' => 'Invalid phone or password'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not create token'], 500);
        }

        return response()->json([
            'message' => 'Login successful.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => auth()->user(),
        ]);
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:3,25',
            'phone' => 'required|digits:10|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'url' => 'nullable|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        //(Transactions)ميزه المعاملات في حال ضار خطا بخطوه من هدول الخطوات ف ما رح تتم هذه العملية
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);

            // $path=$this->uploadImage($request->file('url'),'users');
            // إنشاء السجل في جدول الصور وربطه بالمستخدم
            // $user->image()->create([
            //     'url' => $path,
            // ]);

            $token = JWTAuth::fromUser($user);
            // تسجيل الدخول وإنشاء توكن JWT
            if (!$token) {
                DB::rollBack(); // التراجع عن المعاملة إذا فشل التوكن
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            Auth::login($user);

            // إتمام المعاملة
            DB::commit();

            // إرجاع التوكن
            return response()->json([
                'token' => $this->createNewToken($token),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage(), // عرض رسالة الخطأ الحقيقية
                'line' => $e->getLine(), // عرض رقم السطر
                'file' => $e->getFile(), // عرض الملف
            ], 500);
        }
    }


    public function refresh()
    {
        // تجديد التوكن
        $newToken = auth()->refresh();

        // إنشاء استجابة مع التوكن الجديد
        return $this->createNewToken($newToken);
    }
    // طريقة لإنشاء التوكن وإرجاعه كاستجابة
    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => auth()->user(), // معلومات المستخدم
        ]);
    }
    //تسجيل خروج
    public function logout(Request $request)
    {
        //الحصول على التوكين ر Authorization
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 400);
        }

        try {
            //   محاولة التحقق من التوكين انو هو نشط
            JWTAuth::setToken($token)->invalidate();

            // إرجاع رد بعد إلغاء التوكين
            return response()->json(['message' => 'User successfully signed out'], 200);
        } catch (Exception $e) {
            // في حال حدوث خطأ في التحقق من التوكين
            return response()->json(['error' => 'Failed to log out', 'message' => $e->getMessage()], 500);
        }
        //تابع يقوم بجلب معلومات المستخدم
    }

    public function getProfile()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'المستخدم غير مصادق عليه، يرجى إرسال التوكن بشكل صحيح.'
            ], 401);
        }

        return response()->json([
            'message' => 'تم جلب معلومات المستخدم بنجاح.',
            'user' => $user->load('image'),
        ]);
    }

    //تابع يقوم بتعديل معلومات المستخدم
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'المستخدم غير مصادق عليه، الرجاء إرسال التوكن بشكل صحيح.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|between:3,25',
            'phone' => 'sometimes|required|digits:10|unique:users,phone,' . $user->id,
            'password' => 'sometimes|required|string|min:8|confirmed',
            'url' => 'nullable|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'خطأ في التحقق من البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $isUpdated = false;

            if ($request->filled('name') && $request->name !== $user->name) {
                $user->name = $request->name;
                $isUpdated = true;
            }

            if ($request->filled('phone') && $request->phone !== $user->phone) {
                $user->phone = $request->phone;
                $isUpdated = true;
            }

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
                $isUpdated = true;
            }

            if ($request->hasFile('url')) {
                // حذف الصورة القديمة إن وُجدت
                if ($user->image) {
                    $oldPath = public_path('pictures/' . $user->image->url);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                    $user->image()->delete();
                }

                // رفع الصورة الجديدة
                $path = $this->uploadImage($request->file('url'), 'users');
                $user->image()->create(['url' => $path]);
                $isUpdated = true;
            }

            if (!$isUpdated) {
                return response()->json([
                    'message' => 'لم تقم بتعديل أي معلومات.',
                    'user' => $user->load('image'),
                ]);
            }

            $user->save();
            DB::commit();

            return response()->json([
                'message' => 'تم تحديث الملف الشخصي بنجاح.',
                'user' => $user->load('image'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'حدث خطأ أثناء تحديث البيانات.',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }
}
