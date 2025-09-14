import { defineStore } from 'pinia';
import axios from '../api/adminAxios';

export const useAdminAuthStore = defineStore('adminAuth', {
    state: () => ({
        admin: null,
        token: localStorage.getItem('admin_token'),
        isAuthenticated: false,
        loading: false,
        error: null
    }),

    getters: {
        isLoggedIn: (state) => state.isAuthenticated && !!state.token,
        adminName: (state) => state.admin?.name || 'Администратор',
        adminPhone: (state) => state.admin?.phone || '',
        adminRole: (state) => state.admin?.role || 'admin'
    },

    actions: {
        async login(phone) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post('/auth/login', {
                    phone: phone
                });

                if (response.data.token) {
                    this.token = response.data.token;
                    this.admin = response.data.admin;
                    this.isAuthenticated = true;
                    
                    // Сохраняем токен в localStorage
                    localStorage.setItem('admin_token', this.token);
                    
                    // Устанавливаем токен в axios
                    axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                    
                    return { success: true, data: response.data };
                } else {
                    this.error = 'Ошибка авторизации';
                    return { success: false, error: this.error };
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Ошибка входа в систему';
                return { success: false, error: this.error };
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                if (this.token) {
                    await axios.post('/auth/logout');
                }
            } catch (error) {
                console.error('Ошибка при выходе:', error);
            } finally {
                // Очищаем состояние
                this.admin = null;
                this.token = null;
                this.isAuthenticated = false;
                this.error = null;
                
                // Удаляем токен из localStorage
                localStorage.removeItem('admin_token');
                
                // Удаляем токен из axios
                delete axios.defaults.headers.common['Authorization'];
            }
        },

        async fetchProfile() {
            if (!this.token) return;

            try {
                const response = await axios.get('/auth/profile');
                this.admin = response.data.admin;
                this.isAuthenticated = true;
            } catch (error) {
                console.error('Ошибка получения профиля:', error);
                // Если токен недействителен, выходим из системы
                if (error.response?.status === 401) {
                    await this.logout();
                }
            }
        },

        async checkAuth() {
            if (!this.token) {
                this.isAuthenticated = false;
                return false;
            }

            try {
                // Устанавливаем токен в axios
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                
                await this.fetchProfile();
                return this.isAuthenticated;
            } catch (error) {
                console.error('Ошибка проверки авторизации:', error);
                await this.logout();
                return false;
            }
        },

        clearError() {
            this.error = null;
        }
    }
});
