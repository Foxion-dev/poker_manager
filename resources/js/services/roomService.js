import api from './api';

export const roomService = {
	async getAll() {
		const response = await api.get('/rooms');
		return response.data;
	},

	async getById(id) {
		const response = await api.get(`/rooms/${id}`);
		return response.data;
	},

	async create(data) {
		const response = await api.post('/admin/rooms', data);
		return response.data;
	},

	async update(id, data) {
		const response = await api.put(`/admin/rooms/${id}`, data);
		return response.data;
	},

	async delete(id) {
		const response = await api.delete(`/admin/rooms/${id}`);
		return response.data;
	},

	async getDisabledRoomIds() {
		const response = await api.get('/user/disabled-rooms');
		return response.data.room_ids ?? [];
	},

	async disableRoom(roomId) {
		await api.post(`/user/disabled-rooms/${roomId}`);
	},

	async enableRoom(roomId) {
		await api.delete(`/user/disabled-rooms/${roomId}`);
	},

	async getUserRooms() {
		const response = await api.get('/user-rooms');
		return response.data;
	},

	async updateUserRoomBalance(roomId, balance, currencyId = null) {
		const payload = { balance: Number(balance) };
		if (currencyId != null) payload.currency_id = currencyId;
		const response = await api.put(`/user-rooms/${roomId}/balance`, payload);
		return response.data;
	},

	async attachUserRoom(roomId, balance = 0, currencyId = null) {
		const payload = { balance: Number(balance) };
		if (currencyId != null) payload.currency_id = currencyId;
		const response = await api.post(`/user-rooms/${roomId}/attach`, payload);
		return response.data;
	},
};
