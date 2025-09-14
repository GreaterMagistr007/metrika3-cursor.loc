import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createRouter, createWebHistory } from 'vue-router';
import App from './components/AdminPanel.vue';
import '../css/admin-panel.css';

// Импорт страниц админки
import AdminDashboard from './components/admin/Dashboard.vue';
import UsersManagement from './components/admin/UsersManagement.vue';
import CabinetsManagement from './components/admin/CabinetsManagement.vue';
import AuditLogs from './components/admin/AuditLogs.vue';
import MessagesManagement from './components/admin/MessagesManagement.vue';
import AdminLogin from './components/admin/Login.vue';

// Настройка роутера
const routes = [
    { path: '/', name: 'admin-dashboard', component: AdminDashboard },
    { path: '/users', name: 'admin-users', component: UsersManagement },
    { path: '/cabinets', name: 'admin-cabinets', component: CabinetsManagement },
    { path: '/audit-logs', name: 'admin-audit-logs', component: AuditLogs },
    { path: '/messages', name: 'admin-messages', component: MessagesManagement },
    { path: '/login', name: 'admin-login', component: AdminLogin },
];

const router = createRouter({
    history: createWebHistory('/admin'),
    routes,
});

// Настройка axios для админ-панели уже выполнена в adminAxios.js

// Создание приложения
const app = createApp(App);
app.use(createPinia());
app.use(router);
app.mount('#admin-panel');
