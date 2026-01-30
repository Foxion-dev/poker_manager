export function toUsd(amount, rateToUsd) {
	const num = typeof amount === 'number' ? amount : parseFloat(amount) || 0;
	const rate = rateToUsd != null ? parseFloat(rateToUsd) : null;
	if (rate == null || rate === 0 || rate === 1) {
		return num;
	}
	return num / rate;
}

export function formatCurrency(value, currencyCode = 'USD', symbol = '$') {
	const numValue = typeof value === 'number' ? value : parseFloat(value) || 0;
	if (currencyCode === 'USD') {
		return new Intl.NumberFormat('ru-RU', {
			style: 'currency',
			currency: 'USD',
		}).format(numValue);
	}
	return `${symbol}${numValue.toFixed(2)}`;
}

export function formatAmount(amount, currency) {
	const value = typeof amount === 'number' ? amount : parseFloat(amount) || 0;
	if (!currency || currency.code === 'USD') {
		return formatCurrency(value);
	}
	const rateToUsd = parseFloat(currency.rate_to_usd);
	const usd = toUsd(value, rateToUsd);
	return `${formatCurrency(value, currency.code, currency.symbol)} (${formatCurrency(usd)})`;
}
