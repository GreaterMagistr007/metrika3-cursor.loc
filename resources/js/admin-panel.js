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
    { path: '/admin', name: 'admin-dashboard', component: AdminDashboard },
    { path: '/admin/users', name: 'admin-users', component: UsersManagement },
    { path: '/admin/cabinets', name: 'admin-cabinets', component: CabinetsManagement },
    { path: '/admin/audit-logs', name: 'admin-audit-logs', component: AuditLogs },
    { path: '/admin/messages', name: 'admin-messages', component: MessagesManagement },
    { path: '/admin/login', name: 'admin-login', component: AdminLogin },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Создание приложения
const app = createApp(App);
app.use(createPinia());
app.use(router);
app.mount('#admin-panel');
