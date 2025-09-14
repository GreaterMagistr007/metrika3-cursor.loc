import { defineStore } from 'pinia';
import api from '../api/axios.js';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('auth_token'),
        isAuthenticated: false,
        currentCabinet: null,
        loading: false,
        error: null,
    }),

    getters: {
        isLoggedIn: (state) => state.isAuthenticated && !!state.token,
        userCabinets: (state) => state.user?.cabinets || [],
        hasMultipleCabinets: (state) => (state.user?.cabinets || []).length > 1,
    },

    actions: {
        /**
         * Initialize authentication state from localStorage
         */
        initAuth() {
            const token = localStorage.getItem('auth_token');
            const userData = localStorage.getItem('user_data');
            
            if (token && userData) {
                this.token = token;
                this.user = JSON.parse(userData);
                this.isAuthenticated = true;
                this.setAxiosToken(token);
            }
        },

        /**
         * Set axios default authorization header
         */
        setAxiosToken(token) {
            if (token) {
                api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            } else {
                delete api.defaults.headers.common['Authorization'];
            }
        },

        /**
         * Start registration process (send OTP)
         */
        async register(userData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await api.post('/auth/register', userData);

                return {
                    success: true,
                    message: response.data.message,
                    expires_in: response.data.expires_in
                };
            } catch (error) {
                this.error = this.getErrorMessage(error);
                return {
                    success: false,
                    message: this.error
                };
            } finally {
                this.loading = false;
            }
        },

        /**
         * Check if user exists by Telegram ID, create if not exists
         */
        async checkUserByTelegram(telegramId) {
            this.loading = true;
            this.error = null;

            try {
                const response = await api.post('/auth/check-user-by-telegram', {
                    telegram_id: telegramId
                });

                // User found or created - set auth data
                this.setAuthData(response.data.user, response.data.token);
                return {
                    success: true,
                    user_exists: true,
                    needs_profile_completion: response.data.needs_profile_completion,
                    message: response.data.message
                };
            } catch (error) {
                this.error = this.getErrorMessage(error);
                return {
                    success: false,
                    message: this.error
                };
            } finally {
                this.loading = false;
            }
        },

        /**
         * Complete registration with OTP verification
         */
        async completeRegistration(phone, otp) {
            this.loading = true;
            this.error = null;

            try {
                const response = await api.post('/auth/complete-registration', {
                    phone: phone,
                    otp: otp
                });

                const { user, token } = response.data;

                // Store authentication data
                this.setAuthData(user, token);

                return {
                    success: true,
                    message: response.data.message
                };
            } catch (error) {
                this.error = this.getErrorMessage(error);
                return {
                    success: false,
                    message: this.error
                };
            } finally {
                this.loading = false;
            }
        },

        /**
         * Login with phone number (request OTP)
         */
        async loginWithPhone(phone) {
            this.loading = true;
            this.error = null;

            try {
                const response = await api.post('/auth/request-otp', {
                    phone: phone
                });

                return {
                    success: true,
                    message: response.data.message,
                    expiresIn: response.data.expires_in
                };
            } catch (error) {
                this.error = this.getErrorMessage(error);
                return {
                    success: false,
                    message: this.error
                };
            } finally {
                this.loading = false;
            }
        },

        /**
         * Verify OTP and complete login
         */
        async verifyOtp(phone, otp) {
            this.loading = true;
            this.error = null;

            try {
                const response = await api.post('/auth/verify-otp', {
                    phone: phone,
                    otp: otp
                });

                const { user, token } = response.data;

                // Store authentication data
                this.setAuthData(user, token);

                return {
                    success: true,
                    message: response.data.message
                };
            } catch (error) {
                this.error = this.getErrorMessage(error);
                return {
                    success: false,
                    message: this.error
                };
            } finally {
                this.loading = false;
            }
        },

        /**
         * Login with Telegram Mini App
         */
        async loginWithTelegram(initData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await api.post('/auth/telegram', {
                    init_data: initData
                });

                const { user, token } = response.data;

                // Store authentication data
                this.setAuthData(user, token);

                return {
                    success: true,
                    message: response.data.message
                };
            } catch (error) {
                this.error = this.getErrorMessage(error);
                return {
                    success: false,
                    message: this.error
                };
            } finally {
                this.loading = false;
            }
        },

        /**
         * Set authentication data
         */
        setAuthData(user, token) {
            this.user = user;
            this.token = token;
            this.isAuthenticated = true;

            // Store in localStorage
            localStorage.setItem('auth_token', token);
            localStorage.setItem('user_data', JSON.stringify(user));

            // Set axios token
            this.setAxiosToken(token);
        },

        /**
         * Logout user
         */
        async logout() {
            this.loading = true;

            try {
                // Call logout endpoint
                await api.post('/auth/logout');
            } catch (error) {
                console.warn('Logout API call failed:', error);
            } finally {
                // Clear local state regardless of API call result
                this.clearAuthData();
                this.loading = false;
            }
        },

        /**
         * Clear authentication data
         */
        clearAuthData() {
            this.user = null;
            this.token = null;
            this.isAuthenticated = false;
            this.currentCabinet = null;
            this.error = null;

            // Clear localStorage
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_data');

            // Clear axios token
            this.setAxiosToken(null);
        },

        /**
         * Fetch current user data
         */
        async fetchUser() {
            if (!this.token) return;

            try {
                const response = await api.get('/auth/me');
                this.user = response.data.user;
                localStorage.setItem('user_data', JSON.stringify(this.user));
            } catch (error) {
                console.error('Failed to fetch user data:', error);
                // If token is invalid, logout
                if (error.response?.status === 401) {
                    this.clearAuthData();
                }
            }
        },

        /**
         * Fetch user cabinets
         */
        async fetchUserCabinets() {
            if (!this.token) return;

            try {
                const response = await api.get('/cabinets');
                const cabinets = response.data.data || [];
                
                // Update user cabinets
                if (this.user) {
                    this.user.cabinets = cabinets;
                    localStorage.setItem('user_data', JSON.stringify(this.user));
                }
                
                return cabinets;
            } catch (error) {
                console.error('Failed to fetch user cabinets:', error);
                return [];
            }
        },

        /**
         * Set current cabinet
         */
        setCurrentCabinet(cabinet) {
            this.currentCabinet = cabinet;
            localStorage.setItem('current_cabinet', JSON.stringify(cabinet));
        },

        /**
         * Get current cabinet from localStorage
         */
        getCurrentCabinet() {
            const cabinetData = localStorage.getItem('current_cabinet');
            if (cabinetData) {
                this.currentCabinet = JSON.parse(cabinetData);
            }
            return this.currentCabinet;
        },

        /**
         * Check if user has permission in current cabinet
         */
        hasPermission(permission) {
            if (!this.user || !this.currentCabinet) return false;
            
            // This would need to be implemented based on your permission system
            // For now, return true for demo purposes
            return true;
        },

        /**
         * Get error message from axios error
         */
        getErrorMessage(error) {
            if (error.response?.data?.message) {
                return error.response.data.message;
            }
            
            if (error.response?.data?.error_code) {
                switch (error.response.data.error_code) {
                    case 'USER_NOT_FOUND':
                        return 'Пользователь не найден. Обратитесь к администратору для регистрации.';
                    case 'INVALID_OTP':
                        return 'Неверный код подтверждения.';
                    case 'OTP_SEND_FAILED':
                        return 'Ошибка отправки кода. Попробуйте позже.';
                    case 'PHONE_REQUIRED':
                        return 'Необходимо указать номер телефона.';
                    case 'INVALID_TELEGRAM_DATA':
                        return 'Неверные данные Telegram.';
                    case 'USER_EXISTS':
                        return 'Пользователь с таким номером телефона уже существует.';
                    case 'TELEGRAM_ID_EXISTS':
                        return 'Пользователь с таким Telegram ID уже существует.';
                    case 'REGISTRATION_START_ERROR':
                        return 'Ошибка начала регистрации. Попробуйте позже.';
                    case 'REGISTRATION_DATA_NOT_FOUND':
                        return 'Данные регистрации не найдены. Начните регистрацию заново.';
                    case 'REGISTRATION_COMPLETION_ERROR':
                        return 'Ошибка завершения регистрации. Попробуйте позже.';
                    default:
                        return 'Произошла ошибка. Попробуйте позже.';
                }
            }

            return 'Произошла ошибка. Попробуйте позже.';
        }
    }
});
