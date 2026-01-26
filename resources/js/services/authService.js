import api from './api';

export const authService = {
	async register(data) {
		const response = await api.post('/register', data);
		if (response.data.token) {
			localStorage.setItem('auth_token', response.data.token);
		}
		return response.data;
	},

	async login(data) {
		const response = await api.post('/login', data);
		if (response.data.token) {
			localStorage.setItem('auth_token', response.data.token);
		}
		return response.data;
	},

	async logout() {
		await api.post('/logout');
		localStorage.removeItem('auth_token');
	},

	async getUser() {
		const response = await api.get('/user');
		return response.data;
	},
};
