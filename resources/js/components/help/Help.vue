<template>
	<div class="max-w-4xl mx-auto">
		<div class="mb-8">
			<h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
				Помощь по системе
			</h1>
			<p class="text-gray-600 dark:text-gray-400">
				Краткое руководство по работе с Poker Manager.
			</p>
		</div>

		<div class="space-y-8">
			<section class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
				<h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
					<span class="mr-2">📊</span>
					Дашборд и статистика
				</h2>
				<p class="text-gray-600 dark:text-gray-300 mb-2">
					На главной странице отображается статистика за выбранный период: количество турниров и паков, прибыль/убыток, ITM, ROI, средний байин и кэшаут.
				</p>
				<p class="text-gray-600 dark:text-gray-300">
					Измените период в фильтре над карточками (неделя, месяц, год или свой диапазон дат).
				</p>
			</section>

			<section class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
				<h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
					<span class="mr-2">🎯</span>
					Добавление турнира
				</h2>
				<p class="text-gray-600 dark:text-gray-300 mb-3">
					Меню <strong>Турниры</strong> → кнопка <strong>Создать турнир</strong>.
				</p>
				<ol class="list-decimal list-inside space-y-2 text-gray-600 dark:text-gray-300">
					<li>Выберите рум и дату.</li>
					<li>Укажите байин и валюту (обязательно).</li>
					<li>При необходимости: кэшаут, кэшаут баунти, ребаи, место, количество игроков.</li>
					<li>Нажмите «Сохранить».</li>
				</ol>
				<p class="text-gray-600 dark:text-gray-300 mt-3">
					Редактирование: в списке турниров откройте нужный турнир и измените поля.
				</p>
			</section>

			<section class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
				<h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
					<span class="mr-2">📍</span>
					Локации
				</h2>
				<p class="text-gray-600 dark:text-gray-300 mb-2">
					Локации — клубы или точки, к которым вас подключает администратор. В списке видны только ваши локации.
				</p>
				<p class="text-gray-600 dark:text-gray-300 mb-2">
					Откройте локацию, чтобы просмотреть турниры с участниками и призами. Если вы администратор локации — можете создавать турниры, добавлять участников и указывать места.
				</p>
			</section>

			<section class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
				<h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
					<span class="mr-2">📦</span>
					Паки
				</h2>
				<p class="text-gray-600 dark:text-gray-300">
					Паки — серии турниров (бай-ин паки). Указываются даты начала и окончания, байин, валюта, кэшаут. Участвуют в общей статистике на дашборде. Управление: меню профиля → <strong>Паки</strong>.
				</p>
			</section>

			<section class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
				<h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
					<span class="mr-2">🎰</span>
					Румы и балансы
				</h2>
				<p class="text-gray-600 dark:text-gray-300 mb-2">
					Настройки → вкладка <strong>Румы</strong>: глобальные и ваши личные румы, балансы в валюте рума. Рум можно скрыть из списка (он не будет показываться при создании турнира и в боте).
				</p>
				<p class="text-gray-600 dark:text-gray-300">
					Личный рум создаётся в настройках «Румы» (название, иконка, валюта).
				</p>
			</section>

			<section class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
				<h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
					<span class="mr-2">📱</span>
					Настройка Telegram
				</h2>
				<p class="text-gray-600 dark:text-gray-300 mb-3">
					Настройки → вкладка <strong>Telegram</strong> → «Получить код». В боте отправьте: <code class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-600 rounded">/start ВАШ_КОД</code>. После привязки доступны команды бота.
				</p>
				<p class="text-gray-600 dark:text-gray-300 mb-2 font-medium">Команды бота:</p>
				<ul class="list-disc list-inside space-y-1 text-gray-600 dark:text-gray-300">
					<li><code class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-600 rounded">/balance</code> — баланс по румам</li>
					<li><code class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-600 rounded">/get_stats</code> — статистика за период (кнопки выбора)</li>
					<li><code class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-600 rounded">/add</code> — добавить турнир пошагово</li>
				</ul>
				<p class="text-gray-600 dark:text-gray-300 mt-3">
					Отвязка: Настройки → Telegram → «Отвязать аккаунт».
				</p>
			</section>

			<section class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
				<h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
					<span class="mr-2">⚙️</span>
					Профиль и пароль
				</h2>
				<p class="text-gray-600 dark:text-gray-300">
					Настройки → вкладка <strong>Профиль</strong>: изменение имени и смена пароля (текущий пароль и новый пароль дважды).
				</p>
			</section>
		</div>
	</div>
</template>

<script setup>
</script>
