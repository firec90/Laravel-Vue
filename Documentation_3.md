# Belajar VueJS 3.5.22 (FRONTEND)
## 1. Pastikan `node` dan `npm` sudah terinstall
Ketikkan perintah `node -v` dan `nmp -v` untuk mengetahui apakah sudah diinstall atau belum, kalau sudah akan menampilkan versi masing-masing

## 2. Buat folder project & init
Ketikkan perintah berikut
> mkdir ~/projects  
> cd ~/projects  
> npm create vue@latest frontend  

Penjelasan:
* npm create vue@latest memulai wizard Vite+Vue. Pilih opsi:
    - Framework: Vue
    - Variant: Vue 3 + Single File Components
    - Router: Yes (pilih vue-router)
    - Pinia/State: optional (bisa No dulu)
    - TypeScript: pilih No jika masih awam
* Hasil: folder frontend/ berisi template minimal Vite+Vue.

## 3. Masuk folder & install dependency dasar
Tujuan: pasang library yang umum dipakai (axios, vue-router jika belum, bootstrap).
```
cd frontend
npm install
npm install axios bootstrap
npm install vue-router@4
```

Penjelasan:
* axios untuk mengirim request ke backend (Laravel).
* bootstrap untuk gaya cepat (CSS + JS).
* vue-router meng-handle navigasi antara halaman (Login → Products dll).

## 4. Struktur file minimal yang saya rekomendasikan
Tujuan: tahu di mana taruh file agar rapi.
```
frontend/
├─ index.html
├─ package.json
├─ src/
│  ├─ main.js
│  ├─ App.vue
│  ├─ router/
│  │  └─ index.js
│  ├─ api/
│  │  └─ axios.js
│  ├─ views/
│  │  ├─ Login.vue
│  │  └─ Products.vue
│  └─ components/
│     └─ Navbar.vue
```

Penjelasan singkat:
* main.js = entry app; mount Vue.
* router/index.js = definisi route.
* api/axios.js = konfigurasi axios (baseURL + interceptor).
* views/* = halaman (Login, Products).
* components/* = komponen ulang-pakai (nav, modal).

SAMPAI `Langkah 4 — Konfigurasi environment (Vite)`