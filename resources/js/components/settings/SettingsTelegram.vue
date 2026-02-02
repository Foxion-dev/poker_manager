<template>
	<section class="mb-8">
		<h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Подключение к Telegram</h3>

		<div v-if="loading" class="flex items-center justify-center py-8">
			<svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
				<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
				<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
			</svg>
		</div>

		<template v-else>
			<div v-if="!status.bot_enabled" class="rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4 text-amber-800 dark:text-amber-200">
				Бот Telegram пока не подключён администратором. После подключения здесь можно будет привязать аккаунт к боту и добавлять турниры из Telegram.
			</div>

			<template v-else>
				<div v-if="status.connected" class="space-y-4">
					<div class="flex items-center gap-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/50 p-4">
						<span class="text-2xl">✓</span>
						<div>
							<p class="font-medium text-gray-900 dark:text-white">
								Подключено
								<span v-if="status.telegram_username" class="text-gray-600 dark:text-gray-400 font-normal">как @{{ status.telegram_username }}</span>
							</p>
							<p class="text-sm text-gray-500 dark:text-gray-400">Вы можете добавлять турниры через бота в Telegram.</p>
						</div>
					</div>
					<button
						type="button"
						:disabled="disconnecting"
						@click="disconnect"
						class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50"
					>
						{{ disconnecting ? 'Отключение…' : 'Отключить' }}
					</button>
				</div>

				<div v-else class="space-y-4">
					<div v-if="!linkCode" class="max-w-md">
						<p class="text-gray-600 dark:text-gray-400 mb-4">
							Привяжите аккаунт к боту в Telegram, чтобы добавлять турниры через мессенджер. Нажмите «Получить код», затем отправьте полученный код боту (команда /start или /link).
						</p>
						<button
							type="button"
							:disabled="fetchingCode"
							@click="getLinkCode"
							class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
						>
							{{ fetchingCode ? 'Получение кода…' : 'Получить код' }}
						</button>
					</div>

					<div v-else class="max-w-md rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 p-4 space-y-3">
						<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Отправьте этот код боту в Telegram:</p>
						<div class="flex items-center gap-3">
							<code class="flex-1 text-center text-2xl font-mono font-bold tracking-widest py-3 px-4 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white">
								{{ linkCode }}
							</code>
						</div>
						<p class="text-xs text-gray-500 dark:text-gray-400">
							Код действителен до {{ codeExpiresAt }}. В боте используйте команду /start {{ linkCode }} или /link {{ linkCode }}. После привязки обновите страницу.
						</p>
						<button
							type="button"
							@click="linkCode = null; codeExpiresAt = null"
							class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
						>
							Закрыть
						</button>
					</div>
				</div>
			</template>
		</template>
	</section>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';

const loading = ref(true);
const disconnecting = ref(false);
const fetchingCode = ref(false);
const status = reactive({
	connected: false,
	telegram_username: null,
	bot_enabled: false,
});
const linkCode = ref(null);
const codeExpiresAt = ref(null);

const fetchStatus = async () => {
	loading.value = true;
	try {
		const { data } = await api.get('user/telegram');
		status.connected = data.connected;
		status.telegram_username = data.telegram_username ?? null;
		status.bot_enabled = data.bot_enabled ?? false;
	} catch (e) {
		status.bot_enabled = false;
		status.connected = false;
		status.telegram_username = null;
	} finally {
		loading.value = false;
	}
};

const getLinkCode = async () => {
	fetchingCode.value = true;
	linkCode.value = null;
	codeExpiresAt.value = null;
	try {
		const { data } = await api.post('user/telegram/link-code');
		linkCode.value = data.code;
		codeExpiresAt.value = data.expires_at ? new Date(data.expires_at).toLocaleString() : null;
	} catch (e) {
		const msg = e.response?.data?.message || 'Не удалось получить код';
		alert(msg);
	} finally {
		fetchingCode.value = false;
	}
};

const disconnect = async () => {
	disconnecting.value = true;
	try {
		await api.delete('user/telegram');
		status.connected = false;
		status.telegram_username = null;
	} catch (e) {
		alert(e.response?.data?.message || 'Не удалось отключить');
	} finally {
		disconnecting.value = false;
	}
};

onMounted(() => {
	fetchStatus();
});
</script>
