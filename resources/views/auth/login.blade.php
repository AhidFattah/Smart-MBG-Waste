<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart MBG Waste</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-900 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-extrabold text-gray-800">Smart MBG Waste</h2>
            <p class="text-sm text-gray-500 mt-1">Sistem Pendukung Keputusan Sisa Makanan</p>
        </div>

        @if ($errors->has('email'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-600 text-sm p-3 rounded">
                {{ $errors->first('email') }}
            </div>
        @endif

        <form action="{{ route('login.auth') }}" method="POST" class="space-y-4">
            @csrf
            
            <div>
                <label class="block text-sm font-semibold text-gray-700">Alamat Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="mt-1 w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-gray-800">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700">Kata Sandi</label>
                <input type="password" name="password" required
                    class="mt-1 w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-gray-800">
            </div>

            <button type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-150 shadow-md">
                Masuk ke Sistem
            </button>
        </form>

        <div class="mt-6 border-t border-gray-100 pt-4 text-xs text-center text-gray-400">
            Gunakan akun seeder (contoh: <span class="text-blue-500">petugas.sdn01@mbg.com</span> / password: <span class="text-blue-500">password123</span>)
        </div>
    </div>

</body>
</html>