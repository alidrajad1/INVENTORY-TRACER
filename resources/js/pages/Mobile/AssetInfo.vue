<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { 
    Laptop, MapPin, User, Cpu, MemoryStick, 
    CheckCircle2, Download, AlertTriangle, ArrowLeft 
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
    if(!confirm('Pastikan aset fisik ada di depan Anda. Lanjutkan?')) return;
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
    if(s === 'AVAILABLE') return 'bg-green-500 text-white border-green-600';
    if(s === 'BORROWED') return 'bg-blue-500 text-white border-blue-600';
    if(s === 'MAINTENANCE') return 'bg-red-500 text-white border-red-600';
    return 'bg-gray-500 text-white';
};
</script>

<template>
    <Head :title="asset.name" />

    <div class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
        <Card class="w-full max-w-md shadow-xl border-0 overflow-hidden rounded-2xl bg-white">
            
            <div class="bg-slate-900 p-6 text-center text-white relative">
                <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4 backdrop-blur-md border border-white/20">
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
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 text-green-600">
                        <CheckCircle2 class="w-8 h-8" />
                    </div>
                    <h3 class="font-bold text-lg text-slate-900">Berhasil!</h3>
                    <p class="text-slate-500 text-sm mb-6">{{ successMessage }}</p>
                    <Button variant="outline" class="w-full" @click="viewMode = 'info'">Kembali</Button>
                </div>

                <div v-else-if="viewMode === 'report'" class="animate-in slide-in-from-right">
                    <div class="mb-4">
                        <Button variant="ghost" size="sm" class="-ml-3 text-slate-500" @click="viewMode = 'info'">
                            <ArrowLeft class="w-4 h-4 mr-2" /> Batal
                        </Button>
                        <h3 class="font-bold text-lg mt-2">Lapor Kerusakan</h3>
                        <p class="text-xs text-slate-500">Jelaskan kendala teknis yang dialami.</p>
                    </div>
                    <div class="space-y-3">
                        <Input v-model="reportForm.reporter_name" placeholder="Nama Anda (Cth: Budi)" />
                        <Textarea v-model="reportForm.issue" placeholder="Detail kerusakan..." rows="4" />
                        <Button class="w-full bg-red-600 hover:bg-red-700 text-white" 
                            @click="submitReport" :disabled="reportForm.processing">
                            Kirim Laporan
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

                    <div v-if="asset.specs" class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 space-y-2">
                        <p class="text-[10px] font-bold text-blue-900 uppercase mb-2 border-b border-blue-200 pb-1">Spesifikasi Utama</p>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="flex items-center gap-2" v-if="asset.specs.cpu">
                                <Cpu class="w-4 h-4 text-blue-500" />
                                <span class="text-slate-700 font-medium">{{ asset.specs.cpu }}</span>
                            </div>
                            <div class="flex items-center gap-2" v-if="asset.specs.ram">
                                <MemoryStick class="w-4 h-4 text-blue-500" />
                                <span class="text-slate-700 font-medium">{{ asset.specs.ram }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 pt-2">
                        <Button @click="doCheckin" :disabled="checkinForm.processing"
                            class="w-full h-12 bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md shadow-emerald-100">
                            <CheckCircle2 class="w-5 h-5 mr-2" />
                            Saya Pegang Aset Ini
                        </Button>

                        <div class="grid grid-cols-2 gap-3">
                            <Button variant="outline" class="w-full border-slate-200 text-slate-700"
                                v-if="asset.manual_link" as="a" :href="asset.manual_link" target="_blank">
                                <Download class="w-4 h-4 mr-2" /> Manual
                            </Button>
                            <Button variant="outline" class="w-full text-slate-300 border-slate-100" v-else disabled>
                                <Download class="w-4 h-4 mr-2" /> No Manual
                            </Button>

                            <Button variant="ghost" class="w-full bg-red-50 text-red-600 hover:bg-red-100 border border-red-100"
                                @click="viewMode = 'report'">
                                <AlertTriangle class="w-4 h-4 mr-2" /> Lapor Isu
                            </Button>
                        </div>
                    </div>

                </div>

            </CardContent>
        </Card>
        
        <p class="fixed bottom-4 text-[10px] text-slate-400">IT Asset Management System</p>
    </div>
</template>