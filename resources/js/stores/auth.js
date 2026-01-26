import { defineStore } from 'pinia';
import { authService } from '../services/authService';

export const useAuthStore = defineStore('auth', {
	state: () => ({
		user: null,
		isAuthenticated: false,
	}),

	actions: {
		async login(credentials) {
			try {
				const data = await authService.login(credentials);
				this.user = data.user;
				this.isAuthenticated = true;
				return data;
			} catch (error) {
				throw error;
			}
		},

		async register(userData) {
			try {
				const data = await authService.register(userData);
				this.user = data.user;
				this.isAuthenticated = true;
				return data;
			} catch (error) {
				throw error;
			}
		},

		async logout() {
			try {
				await authService.logout();
				this.user = null;
				this.isAuthenticated = false;
			} catch (error) {
				throw error;
			}
		},

		async fetchUser() {
			try {
				const user = await authService.getUser();
				this.user = user;
				this.isAuthenticated = true;
				return user;
			} catch (error) {
				this.user = null;
				this.isAuthenticated = false;
				throw error;
			}
		},
	},
});
