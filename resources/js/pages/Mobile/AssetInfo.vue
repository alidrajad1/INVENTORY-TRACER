<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    Laptop, MapPin, User, Cpu, MemoryStick,
    CheckCircle2, AlertTriangle, ArrowLeft,
    HardDrive, AppWindow
} from 'lucide-vue-next';

// Components
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{ asset: any }>();

// State Tampilan
const viewMode = ref<'info' | 'report' | 'success'>('info');
const successMessage = ref('');

// --- ACTION 1: CHECK-IN (HIJAU) ---
const checkinForm = useForm({});
const doCheckin = () => {
    if (!confirm('Pastikan aset fisik ada di depan Anda. Lanjutkan?')) return;
    checkinForm.post(route('public.checkin', props.asset.asset_tag), {
        onSuccess: () => {
            successMessage.value = 'Audit Mandiri Berhasil! Terima kasih sudah konfirmasi.';
            viewMode.value = 'success';
        }
    });
};

// --- ACTION 3: LAPOR ISU (MERAH) ---
const reportForm = useForm({ reporter_name: '', issue: '' });
const submitReport = () => {
    reportForm.post(route('public.report', props.asset.asset_tag), {
        onSuccess: () => {
            successMessage.value = 'Laporan terkirim. Tiket maintenance telah dibuat.';
            viewMode.value = 'success';
            reportForm.reset();
        }
    });
};

// Helper Warna Status
const statusColor = (s: string) => {
    if (s === 'AVAILABLE') return 'bg-green-500 text-white border-green-600';
    if (s === 'BORROWED') return 'bg-blue-500 text-white border-blue-600';
    if (s === 'MAINTENANCE') return 'bg-red-500 text-white border-red-600';
    return 'bg-gray-500 text-white';
};
</script>

<template>

    <Head :title="asset.name" />

    <div class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
        <Card class="w-full max-w-md shadow-xl border-0 overflow-hidden rounded-2xl bg-white">

            <div class="bg-slate-900 p-6 text-center text-white relative">
                <div
                    class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4 backdrop-blur-md border border-white/20">
                    <Laptop class="w-8 h-8 text-blue-400" />
                </div>
                <h1 class="text-xl font-bold mb-1">{{ asset.name }}</h1>
                <p class="text-slate-400 text-sm font-mono tracking-wider mb-3">{{ asset.asset_tag }}</p>
                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border shadow-sm"
                    :class="statusColor(asset.status)">
                    {{ asset.status }}
                </span>
            </div>

            <CardContent class="p-6">

                <div v-if="viewMode === 'success'" class="text-center py-10 animate-in zoom-in">
                    <div
                        class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 text-green-600">
                        <CheckCircle2 class="w-8 h-8" />
                    </div>
                    <h3 class="font-bold text-lg text-slate-900">Berhasil!</h3>
                    <p class="text-slate-500 text-sm mb-6">{{ successMessage }}</p>
                    <Button variant="outline" class="w-full" @click="viewMode = 'info'">Kembali</Button>
                </div>

                <div v-else-if="viewMode === 'report'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">

                    <div class="mb-6">
                        <button @click="viewMode = 'info'"
                            class="text-sm text-slate-500 hover:text-slate-800 flex items-center gap-1 mb-3 transition-colors">
                            <ArrowLeft class="w-4 h-4" /> Kembali
                        </button>
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Lapor Kerusakan</h2>
                        <p class="text-slate-500 text-sm mt-1">Aset ini bermasalah? Beritahu kami.</p>
                    </div>

                    <div class="space-y-5">
                        <div class="space-y-2">
                            <Label class="text-slate-900 font-medium">Apa kendalanya?</Label>
                            <Textarea v-model="reportForm.issue" placeholder="Ceritakan detail kerusakan di sini..."
                                class="bg-slate-100 border-0 focus:bg-white focus:ring-2 focus:ring-slate-200 transition-all min-h-[140px] resize-none py-3" />
                        </div>

                        <Button class="w-full h-12 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg mt-2"
                            @click="submitReport" :disabled="reportForm.processing">
                            {{ reportForm.processing ? 'Mengirim...' : 'Kirim Laporan' }}
                        </Button>

                    </div>
                </div>

                <div v-else class="space-y-6">

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                            <div class="flex items-center gap-2 text-slate-400 mb-1">
                                <MapPin class="w-3 h-3" /> <span class="text-[10px] font-bold uppercase">Lokasi</span>
                            </div>
                            <p class="font-medium text-sm text-slate-800 truncate">{{ asset.location }}</p>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                            <div class="flex items-center gap-2 text-slate-400 mb-1">
                                <User class="w-3 h-3" /> <span class="text-[10px] font-bold uppercase">Pemegang</span>
                            </div>
                            <p class="font-medium text-sm text-slate-800 truncate">{{ asset.user_name }}</p>
                        </div>
                    </div>

                    <div v-if="asset.specs"
                        class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-4">

                        <h4 class="text-xs font-semibold text-slate-500 uppercase mb-3 flex items-center gap-2">
                            <Cpu class="w-3.5 h-3.5" />
                            Spesifikasi Hardware
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">

                            <div class="flex flex-col" v-if="asset.specs.cpu">
                                <span class="text-[10px] text-slate-500 uppercase tracking-wide">Processor</span>
                                <span class="text-sm font-medium text-slate-900 dark:text-slate-100 break-words">
                                    {{ asset.specs.cpu }}
                                </span>
                            </div>

                            <div class="flex flex-col" v-if="asset.specs.ram">
                                <span class="text-[10px] text-slate-500 uppercase tracking-wide">Memory (RAM)</span>
                                <span class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                    {{ asset.specs.ram }}
                                </span>
                            </div>

                            <div class="flex flex-col" v-if="asset.specs.storage">
                                <span class="text-[10px] text-slate-500 uppercase tracking-wide">Storage</span>
                                <span class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                    {{ asset.specs.storage }}
                                </span>
                            </div>

                            <div class="flex flex-col" v-if="asset.specs.os">
                                <span class="text-[10px] text-slate-500 uppercase tracking-wide">Operating System</span>
                                <span class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                    {{ asset.specs.os }}
                                </span>
                            </div>

                        </div>
                    </div>

                    <div class="space-y-3 pt-2">
                        <div class="space-y-3 pt-2">
                            <Button variant="ghost"
                                class="w-full h-12 bg-red-50 text-red-600 hover:bg-red-100 border border-red-100"
                                @click="viewMode = 'report'">
                                <AlertTriangle class="w-5 h-5 mr-2" /> Lapor Isu
                            </Button>
                        </div>
                    </div>

                </div>

            </CardContent>
        </Card>
    </div>
</template>