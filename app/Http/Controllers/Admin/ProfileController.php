<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
// Api controller for managing profiles in the admin panel
class ProfileController extends Controller
{
    public function profile(Request $request)
    {
        $user = Auth::user();
        if(!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized',
                'success' => false,
            ], 401);
        }
        return response()->json([
            'status' => 200,
            'message' => 'Profile Retrieved',
            'success' => true,
            'data' => $user,
        ]);
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if(!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized',
                'success' => false,
            ], 401);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|unique:users,phone,' . $user->id,
            'password' => 'sometimes|string|min:6|confirmed',
            'avatar' => 'sometimes|image|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation Error',
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }
        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarPath = $avatar->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }
        $user->save();
        return response()->json([
            'status' => 200,
            'message' => 'Profile Updated',
            'success' => true,
            'data' => $user,
        ]);
    }
}