import { defineStore } from 'pinia';
import { roomService } from '../services/roomService';

export const useRoomStore = defineStore('rooms', {
	state: () => ({
		rooms: [],
		loading: false,
	}),

	actions: {
		async fetchRooms() {
			this.loading = true;
			try {
				const rooms = await roomService.getAll();
				this.rooms = rooms;
				return rooms;
			} catch (error) {
				throw error;
			} finally {
				this.loading = false;
			}
		},
	},
});
