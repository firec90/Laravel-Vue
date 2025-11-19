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