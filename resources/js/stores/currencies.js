import { defineStore } from 'pinia';
import { currencyService } from '../services/currencyService';

export const useCurrencyStore = defineStore('currencies', {
	state: () => ({
		currencies: [],
		loading: false,
	}),

	actions: {
		async fetchCurrencies() {
			this.loading = true;
			try {
				const currencies = await currencyService.getAll();
				this.currencies = currencies;
				return currencies;
			} catch (error) {
				throw error;
			} finally {
				this.loading = false;
			}
		},
	},
});
