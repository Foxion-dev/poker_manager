import { defineStore } from 'pinia';
import { tournamentService } from '../services/tournamentService';

export const useTournamentStore = defineStore('tournaments', {
	state: () => ({
		tournaments: [],
		currentTournament: null,
		loading: false,
		pagination: null,
	}),

	actions: {
		async fetchTournaments(params = {}) {
			this.loading = true;
			try {
				const data = await tournamentService.getAll(params);
				this.tournaments = data.data || data;
				this.pagination = data;
				return data;
			} catch (error) {
				throw error;
			} finally {
				this.loading = false;
			}
		},

		async fetchTournament(id) {
			this.loading = true;
			try {
				const tournament = await tournamentService.getById(id);
				this.currentTournament = tournament;
				return tournament;
			} catch (error) {
				throw error;
			} finally {
				this.loading = false;
			}
		},

		async createTournament(data) {
			try {
				const tournament = await tournamentService.create(data);
				this.tournaments.unshift(tournament);
				return tournament;
			} catch (error) {
				throw error;
			}
		},

		async updateTournament(id, data) {
			try {
				const tournament = await tournamentService.update(id, data);
				const index = this.tournaments.findIndex((t) => t.id === id);
				if (index !== -1) {
					this.tournaments[index] = tournament;
				}
				if (this.currentTournament?.id === id) {
					this.currentTournament = tournament;
				}
				return tournament;
			} catch (error) {
				throw error;
			}
		},

		async deleteTournament(id) {
			try {
				await tournamentService.delete(id);
				this.tournaments = this.tournaments.filter((t) => t.id !== id);
				if (this.currentTournament?.id === id) {
					this.currentTournament = null;
				}
			} catch (error) {
				throw error;
			}
		},
	},
});
