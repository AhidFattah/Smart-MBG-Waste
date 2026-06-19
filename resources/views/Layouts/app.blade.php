<!DOCTYPE html>
<html lang="{{ session('locale', 'id') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart MBG Waste & Inventory</title>
  
  <!-- CSS Tailwind 4.0 -->
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  
  <!-- Alpine.js & Plugins -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  
  <!-- Lucide Icons CDN -->
  <script src="https://unpkg.com/lucide@latest"></script>
  
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Google Fonts Outfit & Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
  
  <!-- Manifest PWA -->
  <link rel="manifest" href="/manifest.json">

  <style>
    body {
      font-family: 'Outfit', sans-serif;
    }
    .font-mono-tech {
      font-family: 'Share Tech Mono', monospace;
    }
  </style>

  <script>
    // Inisialisasi service worker PWA
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js');
      });
    }
  </script>
</head>
<body x-data x-cloak :class="$store.theme.current === 'dark' ? 'bg-slate-950 text-slate-100' : 'bg-slate-50 text-slate-800'" class="min-h-screen transition-colors duration-300 antialiased overflow-x-hidden">

  <!-- TOAST NOTIFICATION STACK -->
  <div x-data class="fixed bottom-5 right-5 z-50 space-y-2 max-w-sm">
    <template x-for="(toast, index) in $store.toasts.list" :key="index">
      <div x-show="toast.show" 
           x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="opacity-0 translate-y-2"
           x-transition:enter-end="opacity-100 translate-y-0"
           x-transition:leave="transition ease-in duration-200"
           x-transition:leave-start="opacity-100 translate-y-0"
           x-transition:leave-end="opacity-0 translate-y-2"
           :class="toast.type === 'success' ? 'bg-emerald-600' : (toast.type === 'danger' ? 'bg-rose-600' : 'bg-amber-600')"
           class="text-white text-xs font-semibold px-4 py-3 rounded-xl shadow-2xl flex items-center justify-between gap-3 border border-white/10">
        <span x-text="toast.message"></span>
        <button @click="$store.toasts.remove(index)" class="hover:text-white/80 cursor-pointer">&times;</button>
      </div>
    </template>
  </div>

  <!-- SKELETON SCREEN LOADER SIMULATOR -->
  <div x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 600)" x-show="loading" class="fixed inset-0 z-[99] flex flex-col justify-center items-center" :class="$store.theme.current === 'dark' ? 'bg-slate-950' : 'bg-white'">
    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-emerald-500 mb-4"></div>
    <div class="h-2.5 bg-slate-800 rounded-full w-48 animate-pulse"></div>
  </div>

  <div class="flex min-h-screen">
    
    <!-- SIDEBAR NAVIGASI COLLAPSIBLE -->
    <aside :class="$store.sidebar.open ? 'w-64' : 'w-20'" 
           class="hidden md:flex flex-col border-r shrink-0 transition-all duration-300"
           :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'">
      
      <!-- LOGO PUSAT -->
      <div class="h-16 flex items-center gap-3 px-5 border-b border-slate-800/20">
        <div class="bg-gradient-to-tr from-emerald-500 to-amber-500 p-2 rounded-xl text-white">
          <i data-lucide="sprout" class="h-6 w-6"></i>
        </div>
        <span x-show="$store.sidebar.open" class="font-extrabold text-sm tracking-wider uppercase bg-gradient-to-r from-emerald-400 to-amber-400 bg-clip-text text-transparent">SMART MBG</span>
      </div>

      <!-- LINK MENU UTAMA (SINKRON HAK AKSES) -->
      <nav class="flex-1 py-6 px-3 space-y-1.5 overflow-y-auto">
        
        <!-- DASHBOARD -->
        <a href="{{ route('dashboard') }}" 
           class="flex items-center gap-4 px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('dashboard') ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-500/20' : 'hover:bg-slate-500/5' }}">
          <i data-lucide="layout-dashboard" class="h-5 w-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200' }}"></i>
          <span x-show="$store.sidebar.open" x-text="$store.lang.t('Dashboard')"></span>
        </a>

        @auth
          <!-- SUPER ADMIN EXCLUSIVE (admin_pusat) -->
          @if(auth()->user()->role == 'admin_pusat')
            <div x-show="$store.sidebar.open" class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 pt-4 pb-1">Master Data</div>
            
            <a href="{{ route('admin.schools.index') }}" 
               class="flex items-center gap-4 px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('admin.schools.*') ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-500/20' : 'hover:bg-slate-500/5' }}">
              <i data-lucide="graduation-cap" class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.schools.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200' }}"></i>
              <span x-show="$store.sidebar.open" x-text="$store.lang.t('Sekolah')"></span>
            </a>

            <a href="{{ route('admin.users.index') }}" 
               class="flex items-center gap-4 px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('admin.users.*') ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-500/20' : 'hover:bg-slate-500/5' }}">
              <i data-lucide="users" class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.users.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200' }}"></i>
              <span x-show="$store.sidebar.open" x-text="$store.lang.t('Pengguna')"></span>
            </a>
          @endif

          <!-- ADMIN DAPUR (petugas_dapur) & ADMIN PUSAT -->
          @if(in_array(auth()->user()->role, ['admin_pusat', 'petugas_dapur']))
            <div x-show="$store.sidebar.open" class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 pt-4 pb-1">Dapur & Logistik</div>

            <a href="{{ route('dapur.inventaris.index') }}" 
               class="flex items-center gap-4 px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('dapur.inventaris.*') ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-500/20' : 'hover:bg-slate-500/5' }}">
              <i data-lucide="package-search" class="h-5 w-5 shrink-0 {{ request()->routeIs('dapur.inventaris.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200' }}"></i>
              <span x-show="$store.sidebar.open" x-text="$store.lang.t('Inventaris')"></span>
            </a>

            <a href="{{ route('dapur.suppliers.index') }}" 
               class="flex items-center gap-4 px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('dapur.suppliers.*') ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-500/20' : 'hover:bg-slate-500/5' }}">
              <i data-lucide="truck" class="h-5 w-5 shrink-0 {{ request()->routeIs('dapur.suppliers.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200' }}"></i>
              <span x-show="$store.sidebar.open" x-text="$store.lang.t('Supplier')"></span>
            </a>

            <a href="{{ route('dapur.menus.index') }}" 
               class="flex items-center gap-4 px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('dapur.menus.*') ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-500/20' : 'hover:bg-slate-500/5' }}">
              <i data-lucide="utensils" class="h-5 w-5 shrink-0 {{ request()->routeIs('dapur.menus.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200' }}"></i>
              <span x-show="$store.sidebar.open" x-text="$store.lang.t('Menu Makanan')"></span>
            </a>

            <a href="{{ route('dapur.distributions.index') }}" 
               class="flex items-center gap-4 px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('dapur.distributions.*') ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-500/20' : 'hover:bg-slate-500/5' }}">
              <i data-lucide="navigation" class="h-5 w-5 shrink-0 {{ request()->routeIs('dapur.distributions.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200' }}"></i>
              <span x-show="$store.sidebar.open" x-text="$store.lang.t('Distribusi')"></span>
            </a>
          @endif

          <!-- PETUGAS SEKOLAH (petugas_sekolah) & ADMIN PUSAT -->
          @if(in_array(auth()->user()->role, ['admin_pusat', 'petugas_sekolah']))
            <div x-show="$store.sidebar.open" class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 pt-4 pb-1">Sekolah Hilir</div>

            <a href="{{ route('sekolah.waste.input') }}" 
               class="flex items-center gap-4 px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('sekolah.waste.input') ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-500/20' : 'hover:bg-slate-500/5' }}">
              <i data-lucide="clipboard-signature" class="h-5 w-5 shrink-0 {{ request()->routeIs('sekolah.waste.input') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200' }}"></i>
              <span x-show="$store.sidebar.open" x-text="$store.lang.t('Input Food Waste')"></span>
            </a>

            <a href="{{ route('sekolah.waste.riwayat') }}" 
               class="flex items-center gap-4 px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('sekolah.waste.riwayat') ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-500/20' : 'hover:bg-slate-500/5' }}">
              <i data-lucide="history" class="h-5 w-5 shrink-0 {{ request()->routeIs('sekolah.waste.riwayat') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200' }}"></i>
              <span x-show="$store.sidebar.open" x-text="$store.lang.t('Riwayat Waste')"></span>
            </a>
          @endif

          <div x-show="$store.sidebar.open" class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 pt-4 pb-1">Laporan & Analisa</div>

          <!-- ANALITIK & LAPORAN (Semua User) -->
          <a href="{{ route('analitik.index') }}" 
             class="flex items-center gap-4 px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('analitik.index') ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-500/20' : 'hover:bg-slate-500/5' }}">
            <i data-lucide="line-chart" class="h-5 w-5 shrink-0 {{ request()->routeIs('analitik.index') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200' }}"></i>
            <span x-show="$store.sidebar.open" x-text="$store.lang.t('Analitik DSS')"></span>
          </a>

          <a href="{{ route('laporan.index') }}" 
             class="flex items-center gap-4 px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('laporan.index') ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-500/20' : 'hover:bg-slate-500/5' }}">
            <i data-lucide="files" class="h-5 w-5 shrink-0 {{ request()->routeIs('laporan.index') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200' }}"></i>
            <span x-show="$store.sidebar.open" x-text="$store.lang.t('Unduh Laporan')"></span>
          </a>

          <!-- SYSTEM CONTROLS (admin_pusat) -->
          @if(auth()->user()->role == 'admin_pusat')
            <div x-show="$store.sidebar.open" class="text-[10px] font-bold text-slate-500 uppercase tracking-widest px-3 pt-4 pb-1">Sistem</div>

            <a href="{{ route('admin.backup.index') }}" 
               class="flex items-center gap-4 px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('admin.backup.*') ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-500/20' : 'hover:bg-slate-500/5' }}">
              <i data-lucide="database" class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.backup.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200' }}"></i>
              <span x-show="$store.sidebar.open" x-text="$store.lang.t('Backup & Restore')"></span>
            </a>

            <a href="{{ route('admin.activity-logs.index') }}" 
               class="flex items-center gap-4 px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('admin.activity-logs.*') ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-500/20' : 'hover:bg-slate-500/5' }}">
              <i data-lucide="fingerprint" class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.activity-logs.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200' }}"></i>
              <span x-show="$store.sidebar.open" x-text="$store.lang.t('Audit Log')"></span>
            </a>

            <a href="{{ route('admin.settings.index') }}" 
               class="flex items-center gap-4 px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('admin.settings.*') ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-500/20' : 'hover:bg-slate-500/5' }}">
              <i data-lucide="sliders" class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.settings.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-slate-200' }}"></i>
              <span x-show="$store.sidebar.open" x-text="$store.lang.t('Pengaturan')"></span>
            </a>
          @endif
        @endauth
      </nav>

      <!-- BOTTOM PROFILE -->
      <div class="p-4 border-t" :class="$store.theme.current === 'dark' ? 'border-slate-800' : 'border-slate-100'">
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center font-bold text-sm">
            {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
          </div>
          <div x-show="$store.sidebar.open" class="min-w-0 flex-1">
            <p class="text-xs font-bold truncate">{{ Auth::user()->name ?? 'Pengguna' }}</p>
            <p class="text-[10px] text-slate-500 uppercase font-mono tracking-wider truncate">
              {{ str_replace('_', ' ', Auth::user()->role ?? 'Viewer') }}
            </p>
          </div>
        </div>
      </div>
    </aside>

    <!-- CONTENT WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0">
      
      <!-- TOP NAVBAR -->
      <header class="h-16 flex items-center justify-between px-6 border-b z-20 shrink-0"
              :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'">
        
        <div class="flex items-center gap-4">
          <!-- Toggle Sidebar -->
          <button @click="$store.sidebar.toggle()" class="text-slate-400 hover:text-white cursor-pointer hidden md:block">
            <i data-lucide="menu" class="h-5 w-5"></i>
          </button>
          
          <!-- Mobile Sidebar Toggle -->
          <button @click="$store.mobileMenu.toggle()" class="text-slate-400 hover:text-white cursor-pointer block md:hidden">
            <i data-lucide="menu" class="h-5 w-5"></i>
          </button>

          <!-- GLOBAL PENCARIAN SYSTEM -->
          <div class="relative max-w-xs hidden sm:block">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
              <i data-lucide="search" class="h-4 w-4"></i>
            </span>
            <input type="text" 
                   @keydown.enter="$store.toasts.add('Searching: ' + $el.value, 'info')"
                   placeholder="Pencarian global..." 
                   class="bg-slate-800/10 border border-slate-800/20 text-xs rounded-xl pl-9 pr-4 py-2 focus:outline-none focus:border-emerald-500 w-64 transition-all">
          </div>
        </div>

        <div class="flex items-center gap-4">
          
          <!-- switcher bahasa (Alpine client-side & session callback) -->
          <div class="flex items-center border border-slate-800/20 rounded-lg overflow-hidden text-xs">
            <a href="{{ route('lang.switch', 'id') }}" 
               class="px-2.5 py-1 text-[10px] font-bold"
               :class="$store.lang.current === 'id' ? 'bg-emerald-600 text-white' : 'text-slate-500 hover:text-slate-300'">ID</a>
            <a href="{{ route('lang.switch', 'en') }}" 
               class="px-2.5 py-1 text-[10px] font-bold"
               :class="$store.lang.current === 'en' ? 'bg-emerald-600 text-white' : 'text-slate-500 hover:text-slate-300'">EN</a>
          </div>

          <!-- Lonceng Notifikasi Center (API-based) -->
          <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="relative text-slate-400 hover:text-slate-200 cursor-pointer p-1">
              <i data-lucide="bell" class="h-5 w-5"></i>
              <span x-show="$store.notifications.unread > 0" 
                    class="absolute top-0 right-0 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-slate-900 animate-pulse"></span>
            </button>
            
            <div x-show="open" 
                 @click.away="open = false" 
                 x-transition 
                 class="absolute right-0 mt-3 w-80 rounded-2xl shadow-2xl border text-xs overflow-hidden z-50 font-mono-tech"
                 :class="$store.theme.current === 'dark' ? 'bg-slate-950 border-slate-800 text-slate-300' : 'bg-white border-slate-200 text-slate-800'">
              <div class="p-3 border-b flex justify-between items-center" :class="$store.theme.current === 'dark' ? 'border-slate-800' : 'border-slate-100'">
                <span class="font-bold text-emerald-400 uppercase">Notifikasi Sistem</span>
                <button @click="$store.notifications.markAllRead()" class="text-[10px] text-slate-500 hover:text-emerald-400">Sapu Bersih</button>
              </div>
              <div class="max-h-64 overflow-y-auto divide-y" :class="$store.theme.current === 'dark' ? 'divide-slate-900' : 'divide-slate-100'">
                <template x-for="notif in $store.notifications.items" :key="notif.id">
                  <div class="p-3 text-[11px] leading-relaxed">
                    <p x-text="notif.message"></p>
                    <span class="text-[9px] text-slate-500 mt-1 block" x-text="new Date(notif.created_at).toLocaleString('id-ID')"></span>
                  </div>
                </template>
                <div x-show="$store.notifications.items.length === 0" class="p-8 text-center text-slate-500 font-mono">
                  📭 Tidak ada notifikasi baru
                </div>
              </div>
            </div>
          </div>

          <!-- Dark / Light Mode Switcher -->
          <button @click="$store.theme.toggle()" class="text-slate-400 hover:text-slate-200 cursor-pointer p-1">
            <i x-show="$store.theme.current === 'dark'" data-lucide="sun" class="h-5 w-5"></i>
            <i x-show="$store.theme.current === 'light'" data-lucide="moon" class="h-5 w-5"></i>
          </button>

          <!-- Profile Dropdown & Logout -->
          <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 cursor-pointer focus:outline-none">
              <div class="h-8 w-8 rounded-xl bg-emerald-500/20 text-emerald-400 font-bold border border-emerald-500/30 flex items-center justify-center text-sm">
                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
              </div>
            </button>
            <div x-show="open" 
                 @click.away="open = false" 
                 x-transition 
                 class="absolute right-0 mt-3 w-48 rounded-xl border text-xs shadow-2xl overflow-hidden z-50 font-semibold"
                 :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800 text-slate-300' : 'bg-white border-slate-200 text-slate-800'">
              <div class="p-3 border-b" :class="$store.theme.current === 'dark' ? 'border-slate-800' : 'border-slate-100'">
                <p class="truncate">{{ Auth::user()->name }}</p>
                <p class="text-[10px] text-slate-500 truncate">{{ Auth::user()->email }}</p>
              </div>
              <a href="{{ route('dashboard') }}" class="block px-4 py-2.5 hover:bg-slate-500/5 transition">Dashboard</a>
              <form action="{{ route('logout') }}" method="POST" class="border-t" :class="$store.theme.current === 'dark' ? 'border-slate-800' : 'border-slate-100'">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2.5 text-rose-500 hover:bg-rose-500/5 transition font-bold cursor-pointer">
                  {{ session('locale') == 'en' ? 'Log Out' : 'Keluar Sistem' }}
                </button>
              </form>
            </div>
          </div>

        </div>
      </header>

      <!-- MOBILE SIDEBAR DRAWER -->
      <div x-show="$store.mobileMenu.open" class="fixed inset-0 z-40 flex md:hidden" x-transition>
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="$store.mobileMenu.open = false"></div>
        <aside :class="$store.theme.current === 'dark' ? 'bg-slate-950 text-slate-100' : 'bg-white text-slate-800'" 
               class="relative flex flex-col w-64 max-w-xs h-full z-50 p-5 shadow-2xl">
          <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800/10">
            <span class="font-extrabold text-sm text-emerald-500">SMART MBG</span>
            <button @click="$store.mobileMenu.open = false" class="text-slate-400 hover:text-white cursor-pointer">&times;</button>
          </div>
          <!-- Menu list same as sidebar but in mobile format -->
          <div class="flex-1 space-y-1 overflow-y-auto text-xs font-semibold">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2.5 rounded-xl hover:bg-slate-500/5">Dashboard</a>
            <a href="{{ route('laporan.index') }}" class="block px-3 py-2.5 rounded-xl hover:bg-slate-500/5">Laporan</a>
            <a href="{{ route('analitik.index') }}" class="block px-3 py-2.5 rounded-xl hover:bg-slate-500/5">Analitik DSS</a>
          </div>
        </aside>
      </div>

      <!-- MAIN PAGE RENDER SECTION -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8">
        
        <!-- ALERT FLASH MESSAGES -->
        @if(session('success'))
          <div x-init="$store.toasts.add('{{ session('success') }}', 'success')" class="hidden"></div>
          <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl text-xs font-semibold flex items-center gap-2">
             <span>🎉</span> {{ session('success') }}
          </div>
        @endif

        @if(session('error'))
          <div x-init="$store.toasts.add('{{ session('error') }}', 'danger')" class="hidden"></div>
          <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-xl text-xs font-semibold flex items-center gap-2">
             <span>🚨</span> {{ session('error') }}
          </div>
        @endif

        @yield('content')
      </main>

    </div>
  </div>

  <script>
    // STORES MANAGEMENT KELAS GLOBAL ALPINE
    document.addEventListener('alpine:init', () => {
      
      // Store Tema (Dark/Light)
      Alpine.store('theme', {
        current: "{{ session('theme', 'dark') }}",
        toggle() {
          fetch("{{ route('theme.toggle') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              this.current = data.theme;
            }
          });
        }
      });

      // Store Collapsible Sidebar
      Alpine.store('sidebar', {
        open: true,
        toggle() { this.open = !this.open; }
      });

      // Store Mobile Drawer Menu
      Alpine.store('mobileMenu', {
        open: false,
        toggle() { this.open = !this.open; }
      });

      // Store Toast Notification Stack
      Alpine.store('toasts', {
        list: [],
        add(message, type = 'success') {
          this.list.push({ message, type, show: true });
          const index = this.list.length - 1;
          setTimeout(() => this.remove(index), 4000);
        },
        remove(index) {
          if(this.list[index]) {
            this.list[index].show = false;
          }
        }
      });

      // Store Multi-Language (Instant Client-side Translation Engine)
      Alpine.store('lang', {
        current: "{{ session('locale', 'id') }}",
        dictionary: {
          'Dashboard': { 'id': 'Dasbor Pemantauan', 'en': 'Overview Dashboard' },
          'Sekolah': { 'id': 'Master Sekolah', 'en': 'Schools Registry' },
          'Pengguna': { 'id': 'Hak Pengguna', 'en': 'Users Management' },
          'Inventaris': { 'id': 'Logistik Gudang', 'en': 'Logistics & Inventory' },
          'Supplier': { 'id': 'Supplier Bahan', 'en': 'Suppliers List' },
          'Menu Makanan': { 'id': 'Variasi Menu Gizi', 'en': 'Menu Cook Registry' },
          'Distribusi': { 'id': 'Pengiriman Kargo', 'en': 'Food Distribution' },
          'Input Food Waste': { 'id': 'Pencatatan Waste', 'en': 'Log Food Waste' },
          'Riwayat Waste': { 'id': 'Histori Waste', 'en': 'Waste Logs History' },
          'Analitik DSS': { 'id': 'Analitik & Rekomendasi', 'en': 'Analytics & Recommendations' },
          'Unduh Laporan': { 'id': 'Ekspor Dokumen', 'en': 'Export Reports' },
          'Backup & Restore': { 'id': 'Cadangan Basis Data', 'en': 'Backup & Restore Database' },
          'Audit Log': { 'id': 'Log Audit Aktivitas', 'en': 'System Audit Logs' },
          'Pengaturan': { 'id': 'Parameter Sistem', 'en': 'System Settings' }
        },
        t(key) {
          if (this.dictionary[key]) {
            return this.dictionary[key][this.current] || key;
          }
          return key;
        }
      });

      // Store Real-Time Notification Center
      Alpine.store('notifications', {
        items: [],
        unread: 0,
        init() {
          this.fetchData();
          // Poll every 30 seconds for dynamic logistics warnings
          setInterval(() => this.fetchData(), 30000);
        },
        fetchData() {
          fetch("{{ route('api.notifications') }}")
            .then(res => res.json())
            .then(data => {
              this.unread = data.unread_count;
              this.items = data.notifications;
            });
        },
        markAllRead() {
          fetch("{{ route('api.notifications.read') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              this.unread = 0;
              this.items = [];
              Alpine.store('toasts').add('Notifikasi ditandai dibaca.', 'success');
            }
          });
        }
      });

      // Initialize Lucide Icons
      lucide.createIcons();
    });
  </script>
</body>
</html>