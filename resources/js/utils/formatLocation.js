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

export function formatBuyin(tournament) {
	if (!tournament || !tournament.buyin) return '';
	if (!tournament.currency || tournament.currency.code === 'USD') {
		return formatCurrency(tournament.buyin);
	}

	const buyinInCurrency = parseFloat(tournament.buyin) || 0;
	const rate = parseFloat(tournament.currency.rate_to_usd || 1);
	const buyinInUSD = buyinInCurrency / rate;

	return `${formatCurrency(buyinInCurrency, tournament.currency.code, tournament.currency.symbol)} (${formatCurrency(buyinInUSD)})`;
}

export function formatDate(dateString) {
	const date = new Date(dateString);
	return date.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
}
