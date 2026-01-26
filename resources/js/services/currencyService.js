import api from './api';

export const currencyService = {
	async getAll() {
		const response = await api.get('/currencies');
		return response.data;
	},
};
