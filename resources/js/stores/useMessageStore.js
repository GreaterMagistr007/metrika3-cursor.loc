import { defineStore } from 'pinia';
import axios from '../api/axios';

export const useMessageStore = defineStore('messages', {
    state: () => ({
        messages: [],
        toasts: [],
        unreadCount: 0,
        loading: false,
    }),

    getters: {
        unreadMessages: (state) => state.messages.filter(msg => !msg.is_read),
        persistentMessages: (state) => state.messages.filter(msg => msg.type === 'persistent'),
        broadcastMessages: (state) => state.messages.filter(msg => msg.type === 'broadcast'),
    },

    actions: {
        /**
         * Load messages from API
         */
        async loadMessages() {
            this.loading = true;
            try {
                const response = await axios.get('/api/messages');
                this.messages = response.data.data || [];
                this.updateUnreadCount();
            } catch (error) {
                console.error('Failed to load messages:', error);
            } finally {
                this.loading = false;
            }
        },

        /**
         * Add message to store
         */
        addMessage(message) {
            const existingIndex = this.messages.findIndex(msg => msg.id === message.id);
            if (existingIndex >= 0) {
                this.messages[existingIndex] = message;
            } else {
                this.messages.push(message);
            }
            this.updateUnreadCount();
        },

        /**
         * Remove message from store
         */
        removeMessage(messageId) {
            this.messages = this.messages.filter(msg => msg.id !== messageId);
            this.updateUnreadCount();
        },

        /**
         * Mark message as read
         */
        async markAsRead(messageId) {
            try {
                await axios.post(`/api/messages/${messageId}/read`);
                
                const message = this.messages.find(msg => msg.id === messageId);
                if (message) {
                    message.is_read = true;
                    message.read_at = new Date().toISOString();
                }
                
                this.updateUnreadCount();
            } catch (error) {
                console.error('Failed to mark message as read:', error);
            }
        },

        /**
         * Update unread count
         */
        updateUnreadCount() {
            this.unreadCount = this.messages.filter(msg => !msg.is_read).length;
        },

        /**
         * Clear all messages
         */
        clearMessages() {
            this.messages = [];
            this.unreadCount = 0;
        },

        /**
         * Show toast message
         */
        showToast(message, type = 'info', duration = 5000) {
            const toast = {
                id: Date.now() + Math.random(),
                message,
                type,
                duration
            };
            
            this.toasts.push(toast);
            
            // Auto remove after duration
            setTimeout(() => {
                this.removeToast(toast.id);
            }, duration);
        },

        /**
         * Remove toast
         */
        removeToast(toastId) {
            this.toasts = this.toasts.filter(toast => toast.id !== toastId);
        },

        /**
         * Clear all toasts
         */
        clearToasts() {
            this.toasts = [];
        }
    }
});
