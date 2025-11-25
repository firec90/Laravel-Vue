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