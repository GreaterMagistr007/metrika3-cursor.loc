import { defineStore } from 'pinia';
import axios from 'axios';

export const useMessageStore = defineStore('messages', {
    state: () => ({
        messages: [],
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
        showToast(message, type = 'info') {
            // This would integrate with a toast notification system
            console.log(`Toast [${type}]:`, message);
        }
    }
});
