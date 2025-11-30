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

## 8. Buat Halaman Login
Buat file `src/views/Login.vue` isi dengan kode berikut :
```javascript
<template>
  <div class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="card shadow p-4" style="width: 380px; border-radius: 15px;">
      <h3 class="text-center mb-4">Login</h3>

      <div class="mb-3">
        <label>UserID</label>
        <input v-model="userid" type="text" class="form-control" ref="refUserID" @keyup.enter="focusPassword" />
      </div>

      <div class="mb-3">
        <label>Password</label>
        <input v-model="password" type="password" class="form-control" ref="refPassword" @keyup.enter="login" />
      </div>

      <div v-if="loading" class="text-center mb-3">
        <div class="spinner-border text-primary" role="status">
        </div>
      </div>
      <button @click="login" class="btn btn-primary w-100" :disabled="loading">
        <span v-if="!loading">Login</span>
        <span v-else>Memproses...</span>
      </button>

      <div v-if="error" class="alert alert-danger mt-3">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import api from "../api/axios.js";
import { useRouter } from "vue-router";

const router = useRouter();

const userid = ref("");
const password = ref("");
const error = ref("");
const loading = ref(false);

/* Tambahan: referensi input */
const refUserID = ref(null);
const refPassword = ref(null);

const focusPassword = () => {
  refPassword.value.focus();
};

const login = async() => {
  loading.value = true;
  error.value = "";

    try {
        const response = await api.post("/login", { userid: userid.value, password: password.value });
        localStorage.setItem("auth_token", response.data.token);
        router.push("/products");
    } catch (err) {
        if (err.response?.status === 401) {
          error.value = "UserID atau Password salah";
        } else {
          error.value = "Terjadi kesalahan. Coba lagi nanti.";
        }
    } finally {
        loading.value =false;
    }
};
</script>
```

## 9. Setup Router Agar Login Bisa Dibuka
Buat file `src/router/index.js` isi dengan kode berikut :
```javascript
import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/Login.vue';
import Products from "../views/Products.vue";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: "/", name: "home", redirect: "/login" },
    { path: "/login", name: "login", component: Login },
    { path: "/products", name: "products", component: Products }
  ],
});

export default router;
```

Lalu aktifkan router di `/src/main.js` :
```javascript
import { createApp } from 'vue'
import App from './App.vue'
import router from './router/index.js'

// import bootstrap
import "bootstrap/dist/css/bootstrap.min.css"
import "bootstrap/dist/js/bootstrap.bundle.min.js";
//import "bootstrap" // js optional, tapi kita import aja untuk modal/button dll

createApp(App).use(router).mount('#app')

/*
const app = createApp(App)
app.use(router)
app.mount('#app')
*/
```

## 10. Buat Halaman Products → src/views/Products.vue
Buat file `src/views/Products.vue` :
```javascript
<template>
  <div v-if="loading" class="full-loading">
    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem"></div>
  </div>

  <div class="container mt-4">
    <h3>Daftar Produk</h3>

    <!-- FORM TAMBAH -->
    <div class="card p-3 mb-4">
      <h5>Tambah Produk</h5>

      <div class="row">
        <div class="col-md-3">
          <input v-model="kode_barang" class="form-control" placeholder="Kode Barang" />
        </div>
        <div class="col-md-4">
          <input v-model="nama_barang" class="form-control" placeholder="Nama Barang" />
        </div>
        <div class="col-md-3">
          <input v-model="harga" class="form-control" placeholder="Harga" type="text" @input="harga = harga.replace(/[^0-9]/g, '')" />
        </div>
        <div class="col-md-2">
          <button @click="addProduct" class="btn btn-primary w-100">Tambah</button>
        </div>
      </div>
    </div>

    <!-- TABEL PRODUK -->
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Nama</th>
          <th>Harga</th>
          <th style="width:150px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="p in products" :key="p.kode_barang">
          <td>{{ p.kode_barang }}</td>
          <td>{{ p.nama_barang }}</td>
          <td>{{ p.harga }}</td>
          <td>
            <button class="btn btn-warning btn-sm me-2" @click="edit(p)">Edit</button>
            <button class="btn btn-danger btn-sm" @click="openDeleteModal(p.kode_barang)">Hapus</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- MODAL EDIT (PALING SIMPLE) -->
    <div v-if="editing" 
    class="modal fade show" 
    tabindex="-1" 
    style="display: block; background: rgba(0, 0, 0, 0.5);" 
    @click.self="closeModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Product</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <label>Kode Barang</label>
            <input class="form-control mb-2" v-model="editData.kode_barang" disabled />

            <label>Nama Barang</label>
            <input class="form-control mb-2" v-model="editData.nama_barang" />

            <label>Harga</label>
            <input class="form-control mb-2" v-model="editData.harga" type="text" @input="editData.harga = editData.harga.replace(/[^0-9]/g, '')" />
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeModal">Batal</button>
            <button class="btn btn-primary" @click="updateProduct">Simpan Perubahan</button>
          </div>
        </div>
      </div>
    </div>

    <!-- DELETE FROM MODAL -->
     <div class="modal fade show" v-if="showDeleteModal" style="display:block; background:rgba(0,0,0,0.5)">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <h5 class="modal-title">Konfirmasi Hapus</h5>
            <button type="button" class="btn-close" @click="showDeleteModal = false"></button>
          </div>

          <div class="modal-body">
            Apakah Anda yakin ingin menghapus data ini?
          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showDeleteModal = false">Batal</button>
            <button class="btn btn-danger" @click="confirmDelete">Hapus</button>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<style>
.full-loading {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255,255,255,0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal {
  display: block;
}
</style>

<script setup>
import { ref, onMounted } from "vue";
import api from "../api/axios.js";

const loading = ref(false);
const products = ref([]);

const kode_barang = ref("");
const nama_barang = ref("");
const harga = ref("");

const editing = ref(null);
const editData = ref({
  kode_barang: "",
  nama_barang: "",
  harga: 0,
});

const showDeleteModal = ref(false);
const deleteTarget = ref(null);

// === LOAD DATA PRODUK ===
const loadProducts = async () => {
  loading.value = true;
  const response = await api.get("/products");
  products.value = response.data;
  loading.value = false;
};

onMounted(loadProducts);

// === TAMBAH PRODUK ===
const addProduct = async () => {
  await api.post("/products", {
    kode_barang: kode_barang.value,
    nama_barang: nama_barang.value,
    harga: harga.value,
  });

  kode_barang.value = "";
  nama_barang.value = "";
  harga.value = "";

  await loadProducts();
};

// === HAPUS ===
const openDeleteModal = (kode) => {
  deleteTarget.value = kode;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  await api.delete(`/products/${deleteTarget.value}`);
  showDeleteModal.value = false;
  deleteTarget.value = null;
  await loadProducts();
};

// === EDIT - MODAL OPEN ===
const edit = (p) => {
  editing.value = true;
  editData.value = { ...p };
};

// === TUTUP MODAL ===
const closeModal = () => {
  editing.value = false;
};

// === UPDATE ===
const updateProduct = async () => {
  await api.put(`/products/${editData.value.kode_barang}`, {
    nama_barang: editData.value.nama_barang,
    harga: editData.value.harga,
  });

  closeModal();
  await loadProducts();
};
</script>
```

# SELESAI