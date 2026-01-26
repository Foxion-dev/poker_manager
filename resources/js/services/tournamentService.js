import api from './api';

export const tournamentService = {
	async getAll(params = {}) {
		const response = await api.get('/tournaments', { params });
		return response.data;
	},

	async getById(id) {
		const response = await api.get(`/tournaments/${id}`);
		return response.data;
	},

	async create(data) {
		const response = await api.post('/tournaments', data);
		return response.data;
	},

	async update(id, data) {
		const response = await api.put(`/tournaments/${id}`, data);
		return response.data;
	},

	async delete(id) {
		const response = await api.delete(`/tournaments/${id}`);
		return response.data;
	},
};
