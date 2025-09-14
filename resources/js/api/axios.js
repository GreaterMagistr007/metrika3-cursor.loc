import axios from 'axios';
import { useAuthStore } from '../stores/useAuthStore.js';
import { useMessageStore } from '../stores/useMessageStore.js';

// Create axios instance
const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Request interceptor - add auth token
api.interceptors.request.use(
    (config) => {
        const authStore = useAuthStore();
        
        if (authStore.token) {
            config.headers.Authorization = `Bearer ${authStore.token}`;
        }

        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor - handle responses and errors
api.interceptors.response.use(
    (response) => {
        // Check for system messages in response headers
        const systemMessages = response.headers['x-system-messages'];
        if (systemMessages) {
            try {
                const messages = JSON.parse(systemMessages);
                const messageStore = useMessageStore();
                
                messages.forEach(message => {
                    if (message.type === 'toast') {
                        messageStore.showToast(message.text, message.level || 'info');
                    } else {
                        messageStore.addMessage(message);
                    }
                });
            } catch (error) {
                console.warn('Failed to parse system messages:', error);
            }
        }

        return response;
    },
    (error) => {
        const authStore = useAuthStore();
        const messageStore = useMessageStore();

        // Handle 401 Unauthorized
        if (error.response?.status === 401) {
            // Clear auth data and redirect to login
            authStore.clearAuthData();
            
            // Only redirect if not already on login page
            if (window.location.pathname !== '/login') {
                window.location.href = '/login';
            }
        }

        // Handle 403 Forbidden
        if (error.response?.status === 403) {
            // Check if it's a profile incomplete error
            if (error.response.data?.error_code === 'PROFILE_INCOMPLETE') {
                // Only redirect if not already on complete-profile page
                if (window.location.pathname !== '/complete-profile') {
                    window.location.href = '/complete-profile';
                }
                return;
            }
            
            messageStore.showToast('Недостаточно прав для выполнения действия', 'error');
        }

        // Handle 422 Validation Error
        if (error.response?.status === 422) {
            const errors = error.response.data.errors;
            if (errors) {
                const firstError = Object.values(errors)[0];
                if (Array.isArray(firstError) && firstError.length > 0) {
                    messageStore.showToast(firstError[0], 'error');
                }
            }
        }

        // Handle 500 Server Error
        if (error.response?.status >= 500) {
            messageStore.showToast('Ошибка сервера. Попробуйте позже.', 'error');
        }

        // Handle network errors
        if (!error.response) {
            messageStore.showToast('Ошибка сети. Проверьте подключение к интернету.', 'error');
        }

        return Promise.reject(error);
    }
);

export default api;
