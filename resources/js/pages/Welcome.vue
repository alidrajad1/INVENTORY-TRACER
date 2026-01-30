<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    QrCode, 
    FileText, 
    LogIn, 
    Box,
    ChevronRight
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

    <div class="min-h-screen bg-slate-50 dark:bg-[#0a0a0a] text-slate-900 dark:text-[#EDEDEC] flex flex-col items-center justify-center p-6 relative overflow-hidden">
        
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-blue-50/50 to-transparent dark:from-blue-900/10 pointer-events-none"></div>

        <div class="z-10 text-center mb-10">
            <div class="bg-white dark:bg-slate-900 p-3 rounded-2xl shadow-sm inline-flex mb-4">
                <Box class="w-8 h-8 text-blue-600 dark:text-blue-400" />
            </div>
            <h1 class="text-2xl font-bold tracking-tight">Portal Aset</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Silakan pilih menu layanan</p>
        </div>

        <div class="w-full max-w-sm space-y-4 z-10">
            
            <Link :href="route('public.loan.create')" 
                class="group w-full flex items-center justify-between p-5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg shadow-blue-200 dark:shadow-none transition-all duration-200 hover:scale-[1.02]"
            >
                <div class="flex items-center gap-4">
                    <div class="p-2 bg-white/20 rounded-lg">
                        <FileText class="w-6 h-6" />
                    </div>
                    <div class="text-left">
                        <div class="font-bold text-lg">Pinjam Aset</div>
                        <div class="text-blue-100 text-xs font-medium">Isi form pengajuan</div>
                    </div>
                </div>
                <ChevronRight class="w-5 h-5 opacity-70 group-hover:translate-x-1 transition-transform" />
            </Link>

            <Link :href="route('scan.index')" 
                class="group w-full flex items-center justify-between p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-purple-300 dark:hover:border-purple-800 rounded-xl shadow-sm transition-all duration-200 hover:scale-[1.02]"
            >
                <div class="flex items-center gap-4">
                    <div class="p-2 bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 rounded-lg">
                        <QrCode class="w-6 h-6" />
                    </div>
                    <div class="text-left">
                        <div class="font-bold text-slate-800 dark:text-slate-100">Scan QR Code</div>
                        <div class="text-slate-500 dark:text-slate-400 text-xs">Cek detail barang & laporkan kerusakan</div>
                    </div>
                </div>
                <ChevronRight class="w-5 h-5 text-slate-400 group-hover:translate-x-1 transition-transform" />
            </Link>

            <Link :href="route('login')" 
                class="group w-full flex items-center justify-between p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-600 rounded-xl shadow-sm transition-all duration-200 hover:scale-[1.02]"
            >
                <div class="flex items-center gap-4">
                    <div class="p-2 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-lg">
                        <LogIn class="w-6 h-6" />
                    </div>
                    <div class="text-left">
                        <div class="font-bold text-slate-800 dark:text-slate-100">Login Staff</div>
                        <div class="text-slate-500 dark:text-slate-400 text-xs">Masuk ke dashboard</div>
                    </div>
                </div>
                <ChevronRight class="w-5 h-5 text-slate-400 group-hover:translate-x-1 transition-transform" />
            </Link>

        </div>

        <footer class="mt-12 text-xs text-slate-400">
            &copy; 2026 Asset Management System
        </footer>

    </div>
</template>