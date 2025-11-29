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

## 5. Langkah 4 — Konfigurasi environment (Vite)
Tujuan: letakkan alamat backend agar mudah ganti (dev/production).
Buat file `.env` (di root `frontend/`):
```
VITE_API_URL=http://localhost:8000
```

Penjelasan:
* VITE_ prefix diperlukan agar Vite exposed ke import.meta.env.
* Gunakan VITE_API_URL dalam axios.js supaya semua request mengarah ke Laravel.

Tapi  disini saya tidak memakainya, karena masih belajar saya tanamkan langsung ke file `src/api/axios.js`

## 6. Buat file axios instance (src/api/axios.js)
Tujuan: sentralisasi konfigurasi HTTP (baseURL, token header) sehingga tidak perlu copy–paste.

Contoh sederhana (salin ke src/api/axios.js):
```javascript
import axios from "axios";

const api = axios.create({
    baseURL: "http://localhost:8000/api"
});

// === REQUEST INTERCEPTOR ===
// Dipanggil sebelum request dikirim
api.interceptors.request.use(config => {
    const token = localStorage.getItem("auth_token");
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

api.interceptors.response.use(
    response => response,
    error => {
        const status = error.response?.status;
        const url = error.config?.url;

        // Jika error berasal dari /login -> biarkan Login.vue yang tangani
        if (url === "/login" && status === 401) {
            return Promise.reject(error);
        }

        // Token expired pada API lain
        if (status == 401) {
            localStorage.removeItem("auth_token");
            alert("Sesi Anda telah berakhir. Silahkan login kembali!");
            window.location.href = "/login";
        }
        
        return Promise.reject(error);
    }
);

export default api;
```
Penjelasan:
* `baseURL` → semua `api.get('/products')` jadi `http://localhost:8000/api/products`.
* `interceptor` → jika ada token di localStorage, otomatis ditambahkan header Authorization.

## 7. Setup main.js & load Bootstrap
Tujuan: bootstrapping Vue app dan CSS Bootstrap.  
`src/main.js` contoh:
```javascript
import { createApp } from 'vue'
import App from './App.vue'
import router from './router/index.js'

// import bootstrap
import "bootstrap/dist/css/bootstrap.min.css"

createApp(App).use(router).mount('#app')

/*
const app = createApp(App)
app.use(router)
app.mount('#app')
*/
```

Penjelasan:
* `bootstrap.bundle.min.js` mengaktifkan modal, dropdown, dll.

# SAMPAI `Buat Halaman Login`