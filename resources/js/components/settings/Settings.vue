<template>
	<div>
		<h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Настройки</h2>

		<AppTabs
			v-model="activeTab"
			:tabs="settingsTabs"
			aria-label="Разделы настроек"
		/>

		<SettingsProfile v-show="activeTab === 'profile'" />
		<SettingsRooms v-show="activeTab === 'rooms'" />
		<SettingsTelegram v-show="activeTab === 'telegram'" />
	</div>
</template>

<script setup>
import { ref } from 'vue';
import AppTabs from '../AppTabs.vue';
import SettingsProfile from './SettingsProfile.vue';
import SettingsRooms from './SettingsRooms.vue';
import SettingsTelegram from './SettingsTelegram.vue';

const activeTab = ref('profile');
const settingsTabs = [
	{ id: 'profile', label: 'Профиль' },
	{ id: 'rooms', label: 'Румы' },
	{ id: 'telegram', label: 'Telegram' },
];
</script>
