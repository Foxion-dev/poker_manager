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

	async addUser(locationId, data) {
		const response = await api.post(`/locations/${locationId}/users`, data);
		return response.data;
	},

	async removeUser(locationId, userId) {
		const response = await api.delete(`/locations/${locationId}/users/${userId}`);
		return response.data;
	},

	async getPublicLocation(id, password = null) {
		const params = password ? { password } : {};
		const response = await api.get(`/public/locations/${id}`, { params });
		return response.data;
	},

	async getPublicTournaments(locationId, password = null, limit = 10) {
		const params = { limit };
		if (password) {
			params.password = password;
		}
		const response = await api.get(`/public/locations/${locationId}/tournaments`, { params });
		return response.data;
	},

	async syncCurrencies(locationId, data) {
		const response = await api.post(`/locations/${locationId}/currencies`, data);
		return response.data;
	},

	async updateTournamentParticipants(locationId, tournamentId, data) {
		const response = await api.put(`/locations/${locationId}/tournaments/${tournamentId}/participants`, data);
		return response.data;
	},

	async finishTournament(locationId, tournamentId) {
		const response = await api.post(`/locations/${locationId}/tournaments/${tournamentId}/finish`);
		return response.data;
	},
};
