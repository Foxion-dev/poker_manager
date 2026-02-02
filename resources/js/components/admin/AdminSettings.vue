<template>
	<div>
		<h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Настройки</h2>

		<AppTabs
			v-model="activeTab"
			:tabs="settingsTabs"
			aria-label="Разделы настроек"
		/>

		<AdminRooms v-show="activeTab === 'rooms'" />
		<AdminCurrencies v-show="activeTab === 'currencies'" />
		<AdminUsers v-show="activeTab === 'users'" />
		<AdminSettingsTelegram v-show="activeTab === 'telegram'" />
	</div>
</template>

<script setup>
import { ref } from 'vue';
import AppTabs from '../AppTabs.vue';
import AdminRooms from './AdminRooms.vue';
import AdminCurrencies from './AdminCurrencies.vue';
import AdminUsers from './AdminUsers.vue';
import AdminSettingsTelegram from './AdminSettingsTelegram.vue';

const activeTab = ref('rooms');
const settingsTabs = [
	{ id: 'rooms', label: 'Румы' },
	{ id: 'currencies', label: 'Валюты' },
	{ id: 'users', label: 'Пользователи' },
	{ id: 'telegram', label: 'Telegram' },
];
</script>
