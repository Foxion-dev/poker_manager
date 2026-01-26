import { defineStore } from 'pinia';
import { statisticsService } from '../services/statisticsService';

export const useStatisticsStore = defineStore('statistics', {
	state: () => ({
		stats: null,
		loading: false,
	}),

	actions: {
		async fetchStats(params = {}) {
			this.loading = true;
			try {
				const stats = await statisticsService.getStats(params);
				this.stats = stats;
				return stats;
			} catch (error) {
				throw error;
			} finally {
				this.loading = false;
			}
		},
	},
});
