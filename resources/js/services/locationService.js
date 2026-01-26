import api from './api';

export const locationService = {
	async getAll(params = {}) {
		const response = await api.get('/locations', { params });
		return response.data;
	},

	async getById(id, params = {}) {
		const response = await api.get(`/locations/${id}`, { params });
		return response.data;
	},

	async create(data) {
		const response = await api.post('/locations', data);
		return response.data;
	},

	async update(id, data) {
		const response = await api.put(`/locations/${id}`, data);
		return response.data;
	},

	async delete(id) {
		const response = await api.delete(`/locations/${id}`);
		return response.data;
	},

	async getTournaments(locationId) {
		const response = await api.get(`/locations/${locationId}/tournaments`);
		return response.data;
	},

	async createTournament(locationId, data) {
		const response = await api.post(`/locations/${locationId}/tournaments`, data);
		return response.data;
	},

	async getTournament(locationId, tournamentId) {
		const response = await api.get(`/locations/${locationId}/tournaments/${tournamentId}`);
		return response.data;
	},

	async updateTournament(locationId, tournamentId, data) {
		const response = await api.put(`/locations/${locationId}/tournaments/${tournamentId}`, data);
		return response.data;
	},

	async deleteTournament(locationId, tournamentId) {
		const response = await api.delete(`/locations/${locationId}/tournaments/${tournamentId}`);
		return response.data;
	},

	async addAdmin(locationId, data) {
		const response = await api.post(`/locations/${locationId}/admins`, data);
		return response.data;
	},

	async removeAdmin(locationId, adminId) {
		const response = await api.delete(`/locations/${locationId}/admins/${adminId}`);
		return response.data;
	},
};
