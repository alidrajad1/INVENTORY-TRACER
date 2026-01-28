# 📦 IT Asset Management System

Sistem Informasi Manajemen Aset (SIM Aset) berbasis web modern untuk membantu perusahaan mengelola inventaris IT, melacak peminjaman ke karyawan, dan memonitor riwayat perawatan perangkat.

Dibangun menggunakan **Laravel**, **Inertia.js**, dan **Vue 3** dengan antarmuka yang bersih menggunakan **Shadcn UI** & **Tailwind CSS**.

![Asset Dashboard Screenshot](path/to/screenshot.png) **

## ✨ Fitur Utama

### 🖥️ Manajemen Aset
* **Detail Spesifikasi:** Input detail hardware (CPU, RAM, Storage, OS) secara terstruktur.
* **Status Lifecycle:** Lacak status aset (Available, Borrowed, Maintenance, Broken, Lost).
* **Monitoring Garansi:** Indikator otomatis untuk aset yang masa garansinya habis.

### 🔄 Sirkulasi & Peminjaman (Check-in/Check-out)
* **Assign to Employee:** Peminjaman aset ke karyawan dengan mode *Short Term* (ada tenggat waktu) atau *Long Term* (inventaris tetap).
* **Smart Due Date:** Notifikasi visual jika aset melewati batas waktu pengembalian.
* **Request System:** Karyawan dapat mengajukan permintaan peminjaman aset melalui sistem.

### 🖨️ QR Code & Labeling
* **Auto-Generate QR:** Setiap aset memiliki QR Code unik.
* **Cetak Label:** Fitur cetak stiker aset (satuan atau batch) siap tempel format PDF.
* **Scan to View:** Scan QR untuk melihat detail dan histori aset.

### 🛡️ Audit Trail & Keamanan
* **Activity Logs:** Mencatat siapa melakukan apa dan kapan (Create, Update, Delete, Login).
* **Data Diff:** Melihat perbedaan data sebelum dan sesudah diedit (Old Value vs New Value).
* **Role Based:** Akses berbeda untuk Admin dan Karyawan.

### 📊 Laporan & Utilitas
* **Export Data:** Unduh laporan aset dalam format Excel (.xlsx).
* **Maintenance Log:** Catatan riwayat perbaikan dan kondisi aset.

## 🛠️ Teknologi yang Digunakan

* **Backend:** [Laravel 10/11](https://laravel.com)
* **Frontend:** [Vue.js 3](https://vuejs.org) (Composition API)
* **Adapter:** [Inertia.js](https://inertiajs.com)
* **UI Components:** [Shadcn Vue](https://www.shadcn-vue.com) + [Tailwind CSS](https://tailwindcss.com)
* **Database:** MySQL
* **Packages Unggulan:**
    * `spatie/laravel-activitylog` (Audit Trail)
    * `barryvdh/laravel-dompdf` (Cetak PDF)
    * `maatwebsite/excel` (Export Excel)
    * `lucide-vue-next` (Icons)

## 🚀 Cara Instalasi

1.  **Clone Repository**
    ```bash
    git clone [https://github.com/username/repo-name.git](https://github.com/username/repo-name.git)
    cd repo-name
    ```

2.  **Install Dependencies**
    ```bash
    composer install
    npm install
    ```

3.  **Setup Environment**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Sesuaikan konfigurasi database di file `.env`*

4.  **Migrate & Seed**
    ```bash
    php artisan migrate --seed
    ```

5.  **Run Application**
    ```bash
    npm run dev
    php artisan serve
    ```

## 📄 Lisensi

Project ini bersifat open-source di bawah lisensi [MIT](https://opensource.org/licenses/MIT).