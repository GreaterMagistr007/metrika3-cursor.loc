import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createRouter, createWebHistory } from 'vue-router';
import App from './components/MainApp.vue';
import '../css/main-app.css';

// Импорт страниц
import Dashboard from './components/pages/Dashboard.vue';
import Settings from './components/pages/Settings.vue';
import Login from './components/pages/Login.vue';

// Настройка роутера
const routes = [
    { path: '/', name: 'dashboard', component: Dashboard },
    { path: '/settings', name: 'settings', component: Settings },
    { path: '/login', name: 'login', component: Login },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Создание приложения
const app = createApp(App);
app.use(createPinia());
app.use(router);
app.mount('#main-app');
