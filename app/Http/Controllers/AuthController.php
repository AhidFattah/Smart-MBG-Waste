<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;

class AuthController extends Controller
{
    // Menampilkan halaman login
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    // Memproses data login (Validasi email & password dengan Remember Me)
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Simpan bahasa default ke session jika belum ada
            if (!$request->session()->has('locale')) {
                $request->session()->put('locale', 'id');
            }
            if (!$request->session()->has('theme')) {
                $request->session()->put('theme', 'dark');
            }

            // Log aktivitas login sukses
            ActivityLogger::log('LOGIN_SUCCESS', 'User ' . Auth::user()->name . ' berhasil masuk ke sistem.');

            // Record notification
            DB::table('notifications')->insert([
                'tipe' => 'success',
                'message' => 'Login berhasil: Selamat datang kembali, ' . Auth::user()->name,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->intended('dashboard')
                ->with('success', 'Selamat datang kembali, ' . Auth::user()->name);
        }

        // Log aktivitas login gagal
        DB::table('activity_logs')->insert([
            'user_id' => null,
            'aktivitas' => 'LOGIN_FAILED',
            'deskripsi' => 'Percobaan login gagal untuk email: ' . $request->input('email'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('notifications')->insert([
            'tipe' => 'danger',
            'message' => '⚠️ Gagal login: Percobaan login ilegal pada email ' . $request->input('email'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    // Memproses keluar sistem (Logout)
    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLogger::log('LOGOUT', 'User ' . Auth::user()->name . ' keluar dari sistem.');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    // Mengirim kode OTP simulasi
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->input('email');

        $user = DB::table('users')->where('email', $email)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Email tidak terdaftar di sistem.'], 404);
        }

        $otp = rand(100000, 999999);
        session(['reset_email' => $email, 'reset_otp' => $otp]);

        // Simpan notifikasi OTP di database (mockup)
        DB::table('notifications')->insert([
            'tipe' => 'info',
            'message' => '🔑 MOCKUP OTP: Kode OTP reset password Anda adalah ' . $otp . ' untuk email ' . $email,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP berhasil disimulasikan! Sila cek notifikasi/lonceng sistem atau gunakan kode OTP: ' . $otp,
            'otp' => $otp // dikembalikan untuk kemudahan simulasi frontend
        ]);
    }

    // Memvalidasi kode OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric'
        ]);

        $otpInput = $request->input('otp');
        $sessionOtp = session('reset_otp');

        if ($otpInput == $sessionOtp && !empty($sessionOtp)) {
            session(['otp_verified' => true]);
            return response()->json(['success' => true, 'message' => 'Kode OTP valid! Silakan masukkan kata sandi baru.']);
        }

        return response()->json(['success' => false, 'message' => 'Kode OTP yang Anda masukkan salah atau kedaluwarsa.'], 400);
    }

    // Mengatur ulang kata sandi
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed'
        ]);

        if (!session('otp_verified')) {
            return response()->json(['success' => false, 'message' => 'Harap verifikasi kode OTP terlebih dahulu.'], 403);
        }

        $email = session('reset_email');
        
        DB::table('users')->where('email', $email)->update([
            'password' => Hash::make($request->input('password')),
            'updated_at' => now()
        ]);

        // Log Aktivitas
        $user = DB::table('users')->where('email', $email)->first();
        DB::table('activity_logs')->insert([
            'user_id' => $user->id,
            'aktivitas' => 'RESET_PASSWORD',
            'deskripsi' => 'User ' . $user->name . ' mereset kata sandi melalui OTP.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Clear session
        session()->forget(['reset_email', 'reset_otp', 'otp_verified']);

        return response()->json(['success' => true, 'message' => 'Kata sandi berhasil diubah! Silakan login kembali.']);
    }

    // Toggle Bahasa (ID/EN)
    public function setLocale($lang)
    {
        if (in_array($lang, ['id', 'en'])) {
            session(['locale' => $lang]);
        }
        return redirect()->back();
    }

    // Toggle Dark Mode
    public function toggleTheme()
    {
        $current = session('theme', 'dark');
        $newTheme = $current === 'dark' ? 'light' : 'dark';
        session(['theme' => $newTheme]);
        return response()->json(['success' => true, 'theme' => $newTheme]);
    }
}