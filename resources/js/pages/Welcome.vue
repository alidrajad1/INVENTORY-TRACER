<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    QrCode, 
    FileText, 
    LogIn, 
    LayoutDashboard, 
    Box,
    ArrowRight
} from 'lucide-vue-next';
import { route } from 'ziggy-js';

defineProps<{
    canRegister?: boolean;
    laravelVersion?: string;
    phpVersion?: string;
}>();
</script>

<template>
    <Head title="Portal Aset" />

    <div class="min-h-screen bg-slate-50 text-slate-900 dark:bg-[#0a0a0a] dark:text-[#EDEDEC] flex flex-col relative overflow-hidden">
        
        <div class="absolute top-0 left-0 w-full h-64 bg-gradient-to-b from-blue-50 to-transparent dark:from-blue-900/10 dark:to-transparent -z-10"></div>

        <nav class="w-full p-6 flex justify-between items-center max-w-5xl mx-auto">
            <div class="flex items-center gap-2 font-bold text-xl tracking-tight">
                <div class="bg-primary/10 p-2 rounded-lg text-primary">
                    <Box class="w-6 h-6" />
                </div>
                <span>Asset<span class="text-primary">Manager</span></span>
            </div>

            <div>
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-primary transition-colors flex items-center gap-1"
                >
                    Dashboard <ArrowRight class="w-4 h-4" />
                </Link>
                <Link
                    v-else
                    :href="route('login')"
                    class="text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-primary transition-colors"
                >
                    Staff Login
                </Link>
            </div>
        </nav>

        <main class="flex-grow flex flex-col items-center justify-center p-6 w-full max-w-5xl mx-auto">
            
            <div class="text-center mb-10 max-w-2xl">
                <h1 class="text-4xl md:text-5xl font-extrabold mb-4 leading-tight">
                    Kelola Aset Kantor<br/>
                    <span class="text-primary bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400">
                        Lebih Mudah
                    </span>
                </h1>
                <p class="text-slate-500 dark:text-slate-400 text-lg">
                    Portal layanan mandiri untuk peminjaman aset, cek inventaris, dan administrasi gudang.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full">
                
                <Link :href="route('public.loan.create')" 
                    class="group relative overflow-hidden bg-white dark:bg-[#161615] border border-slate-200 dark:border-slate-800 p-8 rounded-2xl shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col items-center text-center"
                >
                    <div class="h-16 w-16 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <FileText class="w-8 h-8" />
                    </div>
                    <h3 class="text-xl font-bold mb-2">Pinjam Aset</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Isi form pengajuan peminjaman barang untuk keperluan operasional.
                    </p>
                    <span class="mt-6 text-sm font-semibold text-blue-600 dark:text-blue-400 flex items-center gap-1">
                        Buat Request <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                    </span>
                </Link>

                <Link :href="route('scan.index')" 
                    class="group relative overflow-hidden bg-white dark:bg-[#161615] border border-slate-200 dark:border-slate-800 p-8 rounded-2xl shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col items-center text-center"
                >
                    <div class="absolute inset-0 bg-gradient-to-br from-transparent to-purple-50 dark:to-purple-900/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="h-16 w-16 bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <QrCode class="w-8 h-8" />
                    </div>
                    <h3 class="text-xl font-bold mb-2">Scan QR Code</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Pindai stiker pada aset untuk melihat detail atau meminjam cepat.
                    </p>
                    <span class="mt-6 text-sm font-semibold text-purple-600 dark:text-purple-400 flex items-center gap-1">
                        Mulai Scan <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                    </span>
                </Link>

                <Link :href="route('login')" 
                    class="group relative overflow-hidden bg-slate-900 dark:bg-white border border-slate-900 dark:border-white p-8 rounded-2xl shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer flex flex-col items-center text-center"
                >
                    <div class="h-16 w-16 bg-white/10 dark:bg-slate-900/10 text-white dark:text-slate-900 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <LogIn class="w-8 h-8" />
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-white dark:text-slate-900">Login Sistem</h3>
                    <p class="text-sm text-slate-300 dark:text-slate-600">
                        Akses dashboard admin untuk manajemen stok dan persetujuan.
                    </p>
                    <span class="mt-6 text-sm font-semibold text-white dark:text-slate-900 flex items-center gap-1">
                        Masuk Sekarang <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                    </span>
                </Link>

            </div>
        </main>

        <footer class="py-6 text-center text-sm text-slate-400 dark:text-slate-600">
            &copy; 2026 Asset Management System v1.0
        </footer>
    </div>
</template>