<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Html5Qrcode } from 'html5-qrcode';
import { 
    ArrowLeft, QrCode, Zap, AlertCircle, Search, Home 
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { route } from 'ziggy-js';

// --- STATE ---
const isScanning = ref(false);
const scannerObj = ref<Html5Qrcode | null>(null);
const errorMessage = ref('');
const manualCode = ref('');
const hasFlash = ref(false);
const isFlashOn = ref(false);

// --- LOGIC KAMERA ---
const startScanner = async () => {
    errorMessage.value = '';
    
    try {
        // 1. Cek Permission
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error("Browser tidak mendukung akses kamera.");
        }

        // 2. Init Library
        const html5QrCode = new Html5Qrcode("mobile-reader");
        scannerObj.value = html5QrCode;
        isScanning.value = true;

        // 3. Start Camera (Paksa Kamera Belakang)
        await html5QrCode.start(
            { facingMode: "environment" }, 
            { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                aspectRatio: window.innerHeight / window.innerWidth
            },
            onScanSuccess,
            (err) => { /* Ignore frame errors */ }
        );

        // 4. Cek Fitur Flash (Torch)
        // 'as any' digunakan untuk bypass type checking strict
        const settings = html5QrCode.getRunningTrackCameraCapabilities() as any;
        hasFlash.value = settings?.torchFeatureSupported || false;

    } catch (err) {
        console.error(err);
        isScanning.value = false;
        
        // Deteksi Error HTTPS (Penyebab utama di HP)
        if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
            errorMessage.value = "Kamera diblokir! Wajib menggunakan HTTPS atau Localhost.";
        } else {
            errorMessage.value = "Gagal akses kamera. Periksa izin browser Anda.";
        }
    }
};

const stopScanner = async () => {
    if (scannerObj.value && isScanning.value) {
        try {
            await scannerObj.value.stop();
            scannerObj.value.clear();
            isScanning.value = false;
        } catch (e) { console.error("Stop failed", e); }
    }
};

// --- LOGIC FLASH ---
const toggleFlash = async () => {
    if (scannerObj.value && hasFlash.value) {
        await scannerObj.value.applyVideoConstraints({
            advanced: [{ torch: !isFlashOn.value } as any]
        });
        isFlashOn.value = !isFlashOn.value;
    }
};

// --- HASIL SCAN ---
const onScanSuccess = (decodedText: string) => {
    stopScanner();
    handleRedirect(decodedText);
};

const handleRedirect = (text: string) => {
    // Skenario A: QR berisi URL Lengkap
    if (text.startsWith('http')) {
        window.location.href = text;
        return;
    }

    // Skenario B: QR berisi KODE (misal: AST-2024-001)
    // Arahkan ke halaman PUBLIC SCAN (tanpa login)
    router.visit(route('public.scan', text));
};

const handleManual = () => {
    if (manualCode.value) handleRedirect(manualCode.value);
};

// --- LIFECYCLE ---
onMounted(() => {
    startScanner(); // Auto start saat dibuka
});

onUnmounted(() => {
    stopScanner(); // Cleanup saat keluar
});
</script>

<template>
    <Head title="Scan QR Asset" />

    <div class="fixed inset-0 bg-black flex flex-col z-50">
        
        <div class="absolute top-0 left-0 right-0 p-4 flex items-center justify-between z-20 bg-gradient-to-b from-black/80 to-transparent">
            <Link :href="route('dashboard')"> 
                <Button variant="ghost" size="icon" class="text-white hover:bg-white/20 rounded-full h-10 w-10">
                    <ArrowLeft class="w-6 h-6" />
                </Button>
            </Link>
            
            <div class="text-white font-bold tracking-wider flex items-center gap-2 text-sm shadow-sm bg-black/20 px-3 py-1 rounded-full backdrop-blur-sm border border-white/10">
                <QrCode class="w-4 h-4 text-blue-400" /> SCANNER
            </div>
            
            <div class="w-10 flex justify-end">
                <Button 
                    v-if="hasFlash" 
                    @click="toggleFlash"
                    variant="ghost" 
                    size="icon" 
                    class="text-white hover:bg-white/20 rounded-full h-10 w-10 transition-all duration-300"
                    :class="{'bg-yellow-400 text-black hover:bg-yellow-500': isFlashOn}"
                >
                    <Zap class="w-5 h-5" :class="{'fill-current': isFlashOn}" />
                </Button>
            </div>
        </div>

        <div class="flex-1 relative bg-black overflow-hidden">
            <div id="mobile-reader" class="w-full h-full object-cover"></div>

            <div v-if="!isScanning && !errorMessage" class="absolute inset-0 flex flex-col items-center justify-center text-white gap-3 pointer-events-none">
                <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-xs tracking-widest uppercase opacity-80">Memulai Kamera...</p>
            </div>

            <div v-if="errorMessage" class="absolute inset-0 flex items-center justify-center bg-zinc-900 z-30 p-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <AlertCircle class="w-8 h-8 text-red-500" />
                    </div>
                    <h3 class="text-white font-bold mb-2">Kamera Error</h3>
                    <p class="text-zinc-400 text-sm mb-6">{{ errorMessage }}</p>
                    <Link :href="route('dashboard')">
                        <Button variant="outline" class="w-full border-zinc-700 text-white hover:bg-zinc-800">
                            Kembali ke Dashboard
                        </Button>
                    </Link>
                </div>
            </div>

            <div v-if="isScanning" class="absolute inset-0 pointer-events-none flex items-center justify-center">
                <div class="w-64 h-64 border-2 border-white/20 rounded-3xl relative overflow-hidden shadow-[0_0_0_9999px_rgba(0,0,0,0.5)]">
                    <div class="absolute top-0 left-0 right-0 h-0.5 bg-blue-400 shadow-[0_0_20px_rgba(59,130,246,1)] animate-[scan_2s_infinite]"></div>
                    <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-blue-500 rounded-tl-lg"></div>
                    <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-blue-500 rounded-tr-lg"></div>
                    <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-blue-500 rounded-bl-lg"></div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-blue-500 rounded-br-lg"></div>
                </div>
                <p class="absolute bottom-[15%] text-white/80 text-xs font-medium bg-black/40 px-4 py-2 rounded-full backdrop-blur-sm border border-white/10">
                    Arahkan kamera ke QR Code aset
                </p>
            </div>
        </div>

        <div class="bg-zinc-900 p-6 pb-8 rounded-t-3xl -mt-6 z-20 shadow-[0_-10px_40px_rgba(0,0,0,0.5)] border-t border-zinc-800">
            <div class="flex flex-col gap-2">
                <p class="text-zinc-500 text-[10px] uppercase font-bold tracking-wider ml-1">Alternatif</p>
                <div class="flex gap-3">
                    <div class="relative flex-1">
                        <Search class="absolute left-3 top-3.5 h-4 w-4 text-zinc-500" />
                        <Input 
                            v-model="manualCode" 
                            placeholder="Ketik Kode Aset (Cth: AST-01)..." 
                            class="bg-zinc-950 border-zinc-800 text-white pl-10 h-12 rounded-xl focus:ring-blue-600 focus:border-blue-600 placeholder:text-zinc-600"
                            @keyup.enter="handleManual"
                        />
                    </div>
                    <Button @click="handleManual" class="h-12 w-12 rounded-xl bg-blue-600 hover:bg-blue-700 p-0 shadow-lg shadow-blue-900/20">
                        <ArrowLeft class="w-5 h-5 rotate-180" />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/* CSS Reset khusus agar video full screen tanpa scroll */
#mobile-reader {
    width: 100% !important;
    height: 100% !important;
    border: none !important;
}
#mobile-reader video {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    border-radius: 0 !important;
}

/* Animasi Laser */
@keyframes scan {
    0% { top: 0; opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { top: 100%; opacity: 0; }
}
</style>