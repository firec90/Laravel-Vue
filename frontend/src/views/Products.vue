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
