<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="csrf-token" content="{{ csrf_token() }}">

		<title>Poker Manager - Отслеживание прогресса в онлайн покере</title>
		<meta name="description" content="Мини CRM для отслеживания прогресса игры в онлайн покер. Управление турнирами, статистика, динамика банкролла.">

		<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
		<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
		<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon.svg') }}">

		@vite(['resources/css/app.css', 'resources/js/app.js'])
	</head>
	<body>
		<div id="app"></div>
	</body>
</html>
