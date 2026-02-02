<template>
	<div>
		<h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Документация для разработчика</h2>
		<p class="text-gray-600 dark:text-gray-400 mb-8">
			Ссылки на разделы документации в репозитории проекта.
		</p>

		<div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2">
			<component
				:is="repoUrl ? 'a' : 'div'"
				v-for="doc in docs"
				:key="doc.path"
				:href="repoUrl ? docUrl(doc.path) : undefined"
				:target="repoUrl ? '_blank' : undefined"
				:rel="repoUrl ? 'noopener noreferrer' : undefined"
				class="block p-5 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 transition-colors"
				:class="repoUrl ? 'hover:border-indigo-400 dark:hover:border-indigo-500 cursor-pointer' : ''"
			>
				<div class="flex items-start">
					<span class="text-2xl mr-3">{{ doc.icon }}</span>
					<div>
						<h3 class="font-semibold text-gray-900 dark:text-white mb-1">
							{{ doc.title }}
						</h3>
						<p class="text-sm text-gray-600 dark:text-gray-400">
							{{ doc.description }}
						</p>
						<p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
							{{ doc.path }}
						</p>
					</div>
				</div>
			</component>
		</div>

		<div v-if="!repoUrl" class="mt-6 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
			<p class="text-sm text-amber-800 dark:text-amber-200">
				Чтобы ссылки открывали файлы в репозитории, задайте в <code class="px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded">.env</code> переменную <code class="px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded">VITE_REPO_URL</code> (URL репозитория без слэша в конце, например https://github.com/user/poker-manager). Иначе откройте папку <code class="px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900/40 rounded">docs/</code> в репозитории вручную.
			</p>
		</div>
	</div>
</template>

<script setup>
import { computed } from 'vue';

const repoUrl = computed(() => import.meta.env.VITE_REPO_URL || '');

const docs = [
	{
		title: 'Обзор документации',
		description: 'Индекс разделов: для пользователей и для администратора.',
		path: 'docs/README.md',
		icon: '📚',
	},
	{
		title: 'Установка и требования',
		description: 'Требования, окружение, миграции, сидеры.',
		path: 'docs/admin/installation.md',
		icon: '🔧',
	},
	{
		title: 'Деплой',
		description: 'Выкладка на сервер, GitHub Actions, Makefile.',
		path: 'docs/DEPLOY.md',
		icon: '🚀',
	},
	{
		title: 'Настройка Telegram-бота',
		description: 'Вебхук, админка, устранение проблем.',
		path: 'docs/TELEGRAM_BOT_SETUP.md',
		icon: '📱',
	},
	{
		title: 'Разработка',
		description: 'Локальный запуск, Docker, Makefile, структура проекта.',
		path: 'docs/admin/development.md',
		icon: '💻',
	},
	{
		title: 'Руководство пользователя',
		description: 'Пошаговые инструкции для пользователей системы.',
		path: 'docs/users/guide.md',
		icon: '📖',
	},
];

function docUrl(path) {
	if (repoUrl.value) {
		const base = repoUrl.value.replace(/\/?$/, '');
		return base.includes('github.com')
			? `${base}/blob/main/${path}`
			: `${base}/${path}`;
	}
	return '#';
}
</script>
