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
          <input v-model="harga" class="form-control" placeholder="Harga" type="number" />
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
            <button class="btn btn-danger btn-sm" @click="remove(p.kode_barang)">Hapus</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- MODAL EDIT (PALING SIMPLE) -->
    <div v-if="editing" class="card p-3 mt-4">
      <h5>Edit Produk</h5>

      <input v-model="editData.nama_barang" class="form-control mb-2" />
      <input v-model="editData.harga" class="form-control mb-2" />

      <button class="btn btn-success me-2" @click="updateProduct">Simpan</button>
      <button class="btn btn-secondary" @click="editing = null">Batal</button>
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

// === LOAD DATA PRODUK ===
const loadProducts = async () => {
  const response = await api.get("/products");
  products.value = response.data;
};

onMounted(loadProducts);

// === TAMBAH PRODUK ===
const addProduct = async () => {
  loading.value = true;
  await api.post("/products", {
    kode_barang: kode_barang.value,
    nama_barang: nama_barang.value,
    harga: harga.value,
  });

  kode_barang.value = "";
  nama_barang.value = "";
  harga.value = "";

  loadProducts();
  loading.value =false;
};

// === HAPUS ===
const remove = async (kode) => {
  loading.value = true;
  await api.delete(`/products/${kode}`);
  loadProducts();
  loading.value =false;
};

// === EDIT ===
const edit = (p) => {
  editing.value = p.kode_barang;
  editData.value = { ...p };
};

// === UPDATE ===
const updateProduct = async () => {
  loading.value = true;
  await api.put(`/products/${editing.value}`, {
    nama_barang: editData.value.nama_barang,
    harga: editData.value.harga,
  });

  editing.value = null;
  loadProducts();
  loading.value =false;
};
</script>
