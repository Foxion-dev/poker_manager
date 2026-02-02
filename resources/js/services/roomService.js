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

	async createPersonalRoom(data) {
		const hasFile = data.image instanceof File;
		const payload = hasFile ? new FormData() : { ...data };
		if (hasFile) {
			payload.append('name', data.name);
			if (data.icon != null && data.icon !== '') payload.append('icon', data.icon);
			payload.append('image', data.image);
			if (data.currency_id != null) payload.append('currency_id', data.currency_id);
			if (data.currency_ids?.length) {
				data.currency_ids.forEach((id, i) => payload.append(`currency_ids[${i}]`, id));
			}
		} else {
			if (data.currency_id != null) payload.currency_id = data.currency_id;
			if (data.currency_ids?.length) payload.currency_ids = data.currency_ids;
		}
		const response = await api.post('/user/rooms', hasFile ? payload : data);
		return response.data;
	},

	async deletePersonalRoom(roomId) {
		const response = await api.delete(`/user/rooms/${roomId}`);
		return response.data;
	},
};
