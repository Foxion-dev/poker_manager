import { defineStore } from 'pinia';
import { roomService } from '../services/roomService';

export const useRoomStore = defineStore('rooms', {
	state: () => ({
		rooms: [],
		loading: false,
		disabledRoomIds: [],
	}),

	getters: {
		roomsForSelection(state) {
			return state.rooms.filter((room) => !state.disabledRoomIds.includes(room.id));
		},
	},

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

		async fetchDisabledRoomIds() {
			try {
				const roomIds = await roomService.getDisabledRoomIds();
				this.disabledRoomIds = roomIds;
				return roomIds;
			} catch (error) {
				throw error;
			}
		},

		async setRoomDisabled(roomId, disabled) {
			try {
				if (disabled) {
					await roomService.disableRoom(roomId);
					if (!this.disabledRoomIds.includes(roomId)) {
						this.disabledRoomIds = [...this.disabledRoomIds, roomId];
					}
				} else {
					await roomService.enableRoom(roomId);
					this.disabledRoomIds = this.disabledRoomIds.filter((id) => id !== roomId);
				}
			} catch (error) {
				throw error;
			}
		},
	},
});
