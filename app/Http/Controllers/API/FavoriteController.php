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

class FavoriteController extends Controller
{

public function addToFavorites(Request $request)
{
    $user = auth()->user();

    $request->validate([
        'property_id' => 'required|integer|exists:properties,id',
    ]);

    $exists = Favorite::where('user_id', $user->id)
        ->where('favoriteable_id', $request->property_id)
        ->where('favoriteable_type', Property::class)
        ->exists();

    if ($exists) {
        return response()->json(['message' => 'العقار موجود مسبقًا في المفضلة'], 409);
    }

    Favorite::create([
        'user_id' => $user->id,
        'favoriteable_id' => $request->property_id,
        'favoriteable_type' => Property::class,
    ]);

    return response()->json(['message' => 'تمت إضافة العقار إلى المفضلة بنجاح']);
}
public function removeFromFavorites(Request $request)
{
    $user = auth()->user();

    $request->validate([
        'property_id' => 'required|integer|exists:properties,id',
    ]);

    $favorite = Favorite::where('user_id', $user->id)
        ->where('favoriteable_id', $request->property_id)
        ->where('favoriteable_type', Property::class)
        ->first();

    if (!$favorite) {
        return response()->json(['message' => 'العقار غير موجود في المفضلة'], 404);
    }

    $favorite->delete();

    return response()->json(['message' => 'تم حذف العقار من المفضلة بنجاح']);
}
public function getFavorites()
{
    $user = auth()->user();

    $favorites = Favorite::with('favoriteable')
        ->where('user_id', $user->id)
        ->where('favoriteable_type', Property::class)
        ->get();

    return response()->json([
        'message' => 'قائمة العقارات المفضلة',
        'data' => $favorites
    ]);
}
    
}
