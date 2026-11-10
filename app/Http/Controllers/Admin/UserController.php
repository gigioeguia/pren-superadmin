<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Helpers\HasUploader;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use HasUploader;

    public function __construct()
    {
        $this->middleware('permission:users-read')->only('index');
        $this->middleware('permission:users-create')->only('create','store');
        $this->middleware('permission:users-update')->only('edit', 'update');
        $this->middleware('permission:users-delete')->only('destroy', 'deleteAll');
    }

    public function index(Request $request)
    {
        $users = User::whereNotIn('role', ['superadmin', 'staff', 'landlord', 'tenant'])->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function acnooFilter(Request $request)
    {
        $users = User::whereNotIn('role', ['superadmin', 'staff', 'shop-owner'])->when(request('search'), function ($q) {
            $q->where(function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%')
                    ->orWhere('email', 'like', '%' . request('search') . '%')
                    ->orWhere('role', 'like', '%' . request('search') . '%')
                    ->orWhere('phone', 'like', '%' . request('search') . '%');
            });
        })
            ->latest()
            ->paginate($request->per_page ?? 20);

        if ($request->ajax()) {
            return response()->json([
                'data' => view('admin.users.datas', compact('users'))->render()
            ]);
        }

        return redirect(url()->previous());
    }

    public function create()
    {
        $roles = Role::whereNotIn('name', ['superadmin', 'staff', 'landlord', 'tenant'])->latest()->get();
        $phone_codes = base_path('lang/phone_code.json');
        $phone_codes = json_decode(file_get_contents($phone_codes), true);
        return view('admin.users.create', compact('roles', 'phone_codes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string',
            'mobile_no' => 'nullable|string',
            'mobile_code' => 'nullable|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|confirmed',
            'image' => 'nullable|image',
        ]);

        $user = User::create($request->except('image', 'password') + [
            'image' => $request->image ? $this->upload($request, 'image') : null,
            'password' => Hash::make($request->password),
        ]);

        $role = Role::where('name', $request->role)->first();
        $user->roles()->sync($role->id);

        sendNotification($user->id, notify_users([$user->id]), admin_url: route('admin.users.index', ['id' => $user->id]),  admin_msg:__('New staff has been added'));

        return response()->json([
            'message' => __(ucfirst($request->role) . ' created successfully'),
            'redirect' => route('admin.users.index', ['users' => $request->role])
        ]);
    }

    public function edit(User $user)
    {
        if ($user->role == 'superadmin') {
            abort(403);
        }
        $roles = Role::whereNotIn('name', ['superadmin', 'staff', 'landlord', 'tenant'])->latest()->get();
        $phone_codes = base_path('lang/phone_code.json');
        $phone_codes = json_decode(file_get_contents($phone_codes), true);

        return view('admin.users.edit', compact('user', 'roles', 'phone_codes'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->role == 'superadmin') {
            return response()->json(__('You can not update a superadmin.'), 400);
        }
        $request->validate([
            'role' => 'required|string',
            'mobile_no' => 'nullable|string',
            'mobile_code' => 'nullable|string',
            'country' => 'nullable|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|confirmed',
            'image' => 'nullable|image',
        ]);

        $role = Role::where('name', $request->role)->first();
        $user->roles()->sync($role->id);
        $user->update($request->except('image', 'password') + [
            'image' => $request->image ? $this->upload($request, 'image', $user->image) : $user->image,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        return response()->json([
            'message' => __('Staff updated successfully'),
            'redirect' => route('admin.users.index')
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->role == 'superadmin') {
            return response()->json(__('You can not delete a superadmin.'), 400);
        }

        if (file_exists($user->image)) {
            Storage::delete($user->image);
        }

        $user->delete();
        return response()->json([
            'message' => __('Staff deleted successfully'),
            'redirect' => route('admin.users.index')
        ]);
    }

    public function deleteAll(Request $request)
    {
        User::whereIn('id', $request->ids)->delete();
        return response()->json([
            'message' => __('Selected Staff deleted successfully'),
            'redirect' => route('admin.users.index')
        ]);
    }
}
