import api from './api';

export const statisticsService = {
	async getStats(params = {}) {
		const response = await api.get('/dashboard/stats', { params });
		return response.data;
	},
};
