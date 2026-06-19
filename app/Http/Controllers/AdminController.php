<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ActivityLogger;

class AdminController extends Controller
{
    // ==========================================
    // MODUL SEKOLAH CRUD
    // ==========================================
    public function manageSchools(Request $request)
    {
        $search = $request->input('search');
        $query = DB::table('schools');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('school_code', 'like', '%' . $search . '%');
        }

        $schools = $query->orderBy('name', 'asc')->get();
        return view('admin.schools', compact('schools', 'search'));
    }

    public function storeSchool(Request $request)
    {
        $request->validate([
            'school_code' => 'required|string|unique:schools,school_code',
            'name' => 'required|string',
            'address' => 'required|string',
            'total_students' => 'required|integer|min:1',
        ]);

        DB::table('schools')->insert([
            'school_code' => $request->input('school_code'),
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'total_students' => $request->input('total_students'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ActivityLogger::log('CREATE_SCHOOL', 'Menambahkan sekolah baru: ' . $request->input('name'));
        return redirect()->back()->with('success', 'Sekolah baru berhasil didaftarkan!');
    }

    public function updateSchool(Request $request, $id)
    {
        $request->validate([
            'school_code' => 'required|string|unique:schools,school_code,' . $id,
            'name' => 'required|string',
            'address' => 'required|string',
            'total_students' => 'required|integer|min:1',
        ]);

        DB::table('schools')->where('id', $id)->update([
            'school_code' => $request->input('school_code'),
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'total_students' => $request->input('total_students'),
            'updated_at' => now(),
        ]);

        ActivityLogger::log('UPDATE_SCHOOL', 'Memperbarui data sekolah: ' . $request->input('name'));
        return redirect()->back()->with('success', 'Data sekolah berhasil diperbarui!');
    }

    public function destroySchool($id)
    {
        $school = DB::table('schools')->where('id', $id)->first();
        if ($school) {
            ActivityLogger::log('DELETE_SCHOOL', 'Menghapus sekolah: ' . $school->name);
            DB::table('schools')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Sekolah berhasil dihapus dari sistem.');
        }
        return redirect()->back()->with('error', 'Sekolah tidak ditemukan.');
    }

    // ==========================================
    // MODUL USER CRUD & ROLE SETTING
    // ==========================================
    public function manageUsers(Request $request)
    {
        $search = $request->input('search');
        $query = DB::table('users')
            ->leftJoin('schools', 'users.school_id', '=', 'schools.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->select('users.*', 'schools.name as nama_sekolah', 'roles.display_name as nama_role');

        if ($search) {
            $query->where('users.name', 'like', '%' . $search . '%')
                  ->orWhere('users.email', 'like', '%' . $search . '%');
        }

        $users = $query->orderBy('users.name', 'asc')->get();
        $roles = DB::table('roles')->get();
        $schools = DB::table('schools')->get();

        return view('admin.users', compact('users', 'roles', 'schools', 'search'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id' => 'required|integer',
            'school_id' => 'nullable|integer',
        ]);

        $roleId = (int) $request->input('role_id');
        $roleName = DB::table('roles')->where('id', $roleId)->value('role_name') ?: 'petugas_sekolah';

        DB::table('users')->insert([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => $roleName,
            'role_id' => $roleId,
            'school_id' => $request->input('school_id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ActivityLogger::log('CREATE_USER', 'Mendaftarkan user baru: ' . $request->input('name') . ' (' . $roleName . ')');
        return redirect()->back()->with('success', 'Akun pengguna baru berhasil dibuat!');
    }

    public function updateUser(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'role_id' => 'required|integer',
            'school_id' => 'nullable|integer',
        ]);

        $roleId = (int) $request->input('role_id');
        $roleName = DB::table('roles')->where('id', $roleId)->value('role_name') ?: 'petugas_sekolah';

        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'role' => $roleName,
            'role_id' => $roleId,
            'school_id' => $request->input('school_id'),
            'updated_at' => now(),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        DB::table('users')->where('id', $id)->update($data);

        ActivityLogger::log('UPDATE_USER', 'Memperbarui akun pengguna: ' . $request->input('name'));
        return redirect()->back()->with('success', 'Akun pengguna berhasil diperbarui!');
    }

    public function destroyUser($id)
    {
        $user = DB::table('users')->where('id', $id)->first();
        if ($user) {
            ActivityLogger::log('DELETE_USER', 'Menghapus pengguna: ' . $user->name);
            DB::table('users')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Akun pengguna berhasil dihapus.');
        }
        return redirect()->back()->with('error', 'User tidak ditemukan.');
    }

    // ==========================================
    // MODUL MENU MAKANAN CRUD
    // ==========================================
    public function manageMenus(Request $request)
    {
        $search = $request->input('search');
        $query = DB::table('menus');

        if ($search) {
            $query->where('menu_name', 'like', '%' . $search . '%')
                  ->orWhere('menu_code', 'like', '%' . $search . '%');
        }

        $menus = $query->orderBy('menu_code', 'asc')->get();
        return view('admin.menus', compact('menus', 'search'));
    }

    public function storeMenu(Request $request)
    {
        $request->validate([
            'menu_code' => 'required|string|unique:menus,menu_code',
            'menu_name' => 'required|string',
            'calories' => 'required|integer|min:0',
        ]);

        DB::table('menus')->insert([
            'menu_code' => $request->input('menu_code'),
            'menu_name' => $request->input('menu_name'),
            'calories' => $request->input('calories'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ActivityLogger::log('CREATE_MENU', 'Menambahkan menu gizi baru: ' . $request->input('menu_name'));
        return redirect()->back()->with('success', 'Menu gizi baru berhasil ditambahkan!');
    }

    public function updateMenu(Request $request, $id)
    {
        $request->validate([
            'menu_code' => 'required|string|unique:menus,menu_code,' . $id,
            'menu_name' => 'required|string',
            'calories' => 'required|integer|min:0',
        ]);

        DB::table('menus')->where('id', $id)->update([
            'menu_code' => $request->input('menu_code'),
            'menu_name' => $request->input('menu_name'),
            'calories' => $request->input('calories'),
            'updated_at' => now(),
        ]);

        ActivityLogger::log('UPDATE_MENU', 'Memperbarui data menu: ' . $request->input('menu_name'));
        return redirect()->back()->with('success', 'Menu gizi berhasil diperbarui!');
    }

    public function destroyMenu($id)
    {
        $menu = DB::table('menus')->where('id', $id)->first();
        if ($menu) {
            ActivityLogger::log('DELETE_MENU', 'Menghapus menu gizi: ' . $menu->menu_name);
            DB::table('menus')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Menu gizi berhasil dihapus.');
        }
        return redirect()->back()->with('error', 'Menu tidak ditemukan.');
    }

    // ==========================================
    // AUDIT LOGS / ACTIVITY LOGS
    // ==========================================
    public function activityLogs(Request $request)
    {
        $logs = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->select('activity_logs.*', 'users.name as nama_user', 'users.email as email_user')
            ->orderBy('activity_logs.created_at', 'desc')
            ->paginate(20);

        return view('admin.activity_logs', compact('logs'));
    }

    // ==========================================
    // SYSTEM SETTINGS
    // ==========================================
    public function settings()
    {
        $settings = DB::table('settings')->get()->pluck('value', 'key');
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $inputs = $request->except('_token');
        
        foreach ($inputs as $key => $value) {
            DB::table('settings')->where('key', $key)->update([
                'value' => $value,
                'updated_at' => now()
            ]);
        }

        ActivityLogger::log('UPDATE_SETTINGS', 'Memperbarui pengaturan parameter sistem.');
        return redirect()->back()->with('success', 'Pengaturan sistem berhasil disimpan!');
    }
}
