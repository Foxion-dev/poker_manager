import api from './api';

export const packService = {
	async getAll() {
		const response = await api.get('/packs');
		return response.data;
	},

	async getById(id) {
		const response = await api.get(`/packs/${id}`);
		return response.data;
	},

	async create(data) {
		const response = await api.post('/packs', data);
		return response.data;
	},

	async update(id, data) {
		const response = await api.put(`/packs/${id}`, data);
		return response.data;
	},

	async delete(id) {
		const response = await api.delete(`/packs/${id}`);
		return response.data;
	},
};
