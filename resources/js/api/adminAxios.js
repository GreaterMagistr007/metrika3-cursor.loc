import axios from 'axios';

// Create axios instance for admin panel
const adminApi = axios.create({
    baseURL: '/api/admin',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Request interceptor - add auth token
adminApi.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('admin_token');
        
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }

        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor - handle responses and errors
adminApi.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        // Handle 401 Unauthorized
        if (error.response?.status === 401) {
            // Clear admin auth data and redirect to login
            localStorage.removeItem('admin_token');
            delete adminApi.defaults.headers.common['Authorization'];
            
            // Only redirect if not already on login page
            if (window.location.pathname !== '/admin/login') {
                window.location.href = '/admin/login';
            }
        }

        // Handle 403 Forbidden
        if (error.response?.status === 403) {
            console.error('Access forbidden:', error.response.data);
        }

        // Handle 422 Validation Error
        if (error.response?.status === 422) {
            const errors = error.response.data.errors;
            if (errors) {
                const firstError = Object.values(errors)[0];
                if (Array.isArray(firstError) && firstError.length > 0) {
                    console.error('Validation error:', firstError[0]);
                }
            }
        }

        // Handle 500 Server Error
        if (error.response?.status >= 500) {
            console.error('Server error:', error.response.data);
        }

        // Handle network errors
        if (!error.response) {
            console.error('Network error:', error.message);
        }

        return Promise.reject(error);
    }
);

export default adminApi;
