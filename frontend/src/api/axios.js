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