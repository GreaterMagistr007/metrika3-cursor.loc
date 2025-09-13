import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createRouter, createWebHistory } from 'vue-router';
import App from './components/MainApp.vue';
import '../css/main-app.css';
import './api/axios.js'; // Initialize axios interceptors

// Импорт страниц
import Dashboard from './components/pages/Dashboard.vue';
import Settings from './components/pages/Settings.vue';
import Login from './components/pages/Login.vue';

// Настройка роутера
const routes = [
    { 
        path: '/', 
        name: 'dashboard', 
        component: Dashboard,
        meta: { requiresAuth: true }
    },
    { 
        path: '/settings', 
        name: 'settings', 
        component: Settings,
        meta: { requiresAuth: true }
    },
    { 
        path: '/login', 
        name: 'login', 
        component: Login,
        meta: { requiresGuest: true }
    },
    { 
        path: '/register', 
        name: 'register', 
        component: () => import('./components/pages/Register.vue'),
        meta: { requiresGuest: true }
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation guards
router.beforeEach(async (to, from, next) => {
    const { useAuthStore } = await import('./stores/useAuthStore.js');
    const authStore = useAuthStore();
    
    // Initialize auth state if not already done
    if (!authStore.isAuthenticated && authStore.token) {
        authStore.initAuth();
    }
    
    // Check if route requires authentication
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login');
        return;
    }
    
    // Check if route requires guest (not authenticated)
    if (to.meta.requiresGuest && authStore.isAuthenticated) {
        next('/');
        return;
    }
    
    next();
});

// Создание приложения
const app = createApp(App);
app.use(createPinia());
app.use(router);
app.mount('#main-app');
