<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Smart MBG Waste</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Outfit', sans-serif;
    }
  </style>
</head>
<body x-data="{ forgotModal: false, otpSent: false, otpVerified: false, emailForgot: '', otpCode: '', newPassword: '', confirmPassword: '', infoMsg: '', errorMsg: '' }" 
      class="bg-slate-950 text-slate-100 flex items-center justify-center min-h-screen relative overflow-hidden px-4">

  <!-- Background Gradients -->
  <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] bg-emerald-500/10 rounded-full blur-3xl"></div>
  <div class="absolute bottom-[-20%] right-[-10%] w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-3xl"></div>

  <!-- MAIN LOGIN CARD -->
  <div class="bg-slate-900/40 border border-slate-800/80 backdrop-blur-md p-8 rounded-3xl shadow-2xl w-full max-w-md relative z-10 transition-all duration-300">
    
    <!-- HEADER -->
    <div class="text-center mb-8">
      <div class="inline-flex bg-gradient-to-tr from-emerald-500 to-amber-500 p-3 rounded-2xl text-white mb-3 shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sprout"><path d="M7 20h10"/><path d="M10 20c5.5-2.5 8-6.4 8-12a4 4 0 0 0-8 0c0 5.6 2.5 9.5 8 12Z"/><path d="M14 20c-5.5-2.5-8-6.4-8-12a4 4 0 0 1 8 0c0 5.6-2.5 9.5-8 12Z"/></svg>
      </div>
      <h2 class="text-2xl font-black tracking-tight uppercase bg-gradient-to-r from-emerald-400 to-amber-400 bg-clip-text text-transparent">SMART MBG</h2>
      <p class="text-xs text-slate-400 font-mono tracking-wider uppercase mt-1">Inventory & Food Waste System</p>
    </div>

    @if ($errors->has('email'))
      <div class="mb-5 p-3.5 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs rounded-xl flex items-center gap-2">
        <span>🚨</span> {{ $errors->first('email') }}
      </div>
    @endif

    @if(session('success'))
      <div class="mb-5 p-3.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs rounded-xl flex items-center gap-2">
        <span>🎉</span> {{ session('success') }}
      </div>
    @endif

    <form action="{{ route('login.auth') }}" method="POST" class="space-y-4">
      @csrf
      
      <div>
        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Alamat Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus
               class="w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all duration-200">
      </div>

      <div>
        <div class="flex justify-between items-center mb-1">
          <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Kata Sandi</label>
          <button type="button" @click="forgotModal = true" class="text-[10px] text-emerald-400 hover:text-emerald-300 font-bold uppercase cursor-pointer">Lupa Sandi?</button>
        </div>
        <input type="password" name="password" required
               class="w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all duration-200">
      </div>

      <!-- Remember Me -->
      <div class="flex items-center">
        <input id="remember" type="checkbox" name="remember" class="h-4 w-4 rounded bg-slate-900 border-slate-800 text-emerald-600 focus:ring-0 cursor-pointer">
        <label for="remember" class="ml-2 block text-xs text-slate-400 font-medium select-none cursor-pointer">Ingat saya di perangkat ini</label>
      </div>

      <button type="submit" 
              class="w-full bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white font-bold py-3 px-4 rounded-xl text-xs uppercase tracking-wider transition-all duration-200 shadow-lg cursor-pointer transform active:scale-98">
        Masuk Dashboard
      </button>
    </form>

    <!-- Akun Demo -->
    <div class="mt-8 border-t border-slate-800/40 pt-5 text-center">
      <p class="text-[10px] font-mono text-slate-500 uppercase tracking-wider mb-2">Gunakan Akun Simulasi</p>
      <div class="space-y-1 text-[10px] font-mono text-emerald-500/80">
        <p>👨‍💼 Admin: <span class="text-slate-300 select-all">admin@mbg.com</span> / password123</p>
        <p>🍳 Dapur: <span class="text-slate-300 select-all">dapur@mbg.com</span> / password123</p>
        <p>🏫 Sekolah: <span class="text-slate-300 select-all">petugas.sdn01@mbg.com</span> / password123</p>
      </div>
    </div>
  </div>

  <!-- FORGOT PASSWORD / OTP MODAL -->
  <div x-show="forgotModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div @click.away="forgotModal = false" class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-2xl overflow-hidden shadow-2xl p-6 relative">
      
      <button @click="forgotModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-white cursor-pointer">&times;</button>

      <div class="mb-5">
        <h3 class="text-sm font-bold text-white uppercase tracking-wider">Pemulihan Kata Sandi OTP</h3>
        <p class="text-[10px] text-slate-400 font-mono mt-1">Simulator verifikasi OTP reset password</p>
      </div>

      <!-- Alerts -->
      <div x-show="infoMsg" class="mb-4 p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs rounded-xl font-mono" x-text="infoMsg"></div>
      <div x-show="errorMsg" class="mb-4 p-3 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs rounded-xl font-mono" x-text="errorMsg"></div>

      <!-- Step 1: Input Email -->
      <div x-show="!otpSent" class="space-y-4">
        <div>
          <label class="block text-slate-400 text-xs mb-1 font-mono">Alamat Email Terdaftar</label>
          <input type="email" x-model="emailForgot" placeholder="nama@email.com" 
                 class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-xs focus:outline-none focus:border-emerald-500">
        </div>
        <button type="button" @click="
          if(!emailForgot) { errorMsg = 'Email wajib diisi!'; return; }
          errorMsg = '';
          fetch('{{ route('password.forgot') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ email: emailForgot })
          })
          .then(res => res.json())
          .then(data => {
            if(data.success) {
              otpSent = true;
              infoMsg = data.message;
            } else {
              errorMsg = data.message;
            }
          })
          .catch(() => errorMsg = 'Error koneksi');
        " class="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold py-2.5 rounded-xl transition cursor-pointer">
          KIRIM KODE OTP
        </button>
      </div>

      <!-- Step 2: Input OTP -->
      <div x-show="otpSent && !otpVerified" class="space-y-4">
        <div>
          <label class="block text-slate-400 text-xs mb-1 font-mono">Kode OTP (6-Digit)</label>
          <input type="text" x-model="otpCode" placeholder="Masukkan 6 digit angka" 
                 class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-xs text-center font-bold tracking-widest focus:outline-none focus:border-emerald-500">
        </div>
        <button type="button" @click="
          if(!otpCode) { errorMsg = 'OTP wajib diisi!'; return; }
          errorMsg = '';
          fetch('{{ route('password.verify-otp') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ otp: otpCode })
          })
          .then(res => res.json())
          .then(data => {
            if(data.success) {
              otpVerified = true;
              infoMsg = data.message;
            } else {
              errorMsg = data.message;
            }
          })
          .catch(() => errorMsg = 'Error koneksi');
        " class="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold py-2.5 rounded-xl transition cursor-pointer">
          VERIFIKASI OTP
        </button>
      </div>

      <!-- Step 3: Input Kata Sandi Baru -->
      <div x-show="otpVerified" class="space-y-4">
        <div>
          <label class="block text-slate-400 text-xs mb-1 font-mono">Kata Sandi Baru</label>
          <input type="password" x-model="newPassword" 
                 class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-xs focus:outline-none focus:border-emerald-500">
        </div>
        <div>
          <label class="block text-slate-400 text-xs mb-1 font-mono">Konfirmasi Kata Sandi</label>
          <input type="password" x-model="confirmPassword" 
                 class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-xs focus:outline-none focus:border-emerald-500">
        </div>
        <button type="button" @click="
          if(newPassword !== confirmPassword) { errorMsg = 'Password tidak cocok!'; return; }
          errorMsg = '';
          fetch('{{ route('password.reset') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ password: newPassword, password_confirmation: confirmPassword })
          })
          .then(res => res.json())
          .then(data => {
            if(data.success) {
              alert(data.message);
              window.location.reload();
            } else {
              errorMsg = data.message;
            }
          })
          .catch(() => errorMsg = 'Error koneksi');
        " class="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold py-2.5 rounded-xl transition cursor-pointer">
          SETEL ULANG SANDI
        </button>
      </div>

    </div>
  </div>

</body>
</html>