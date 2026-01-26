<template>
	<div>
		<div class="flex justify-between items-center mb-6">
			<h2 class="text-2xl font-bold text-gray-900 dark:text-white">Управление пользователями</h2>
		</div>

		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6 border border-gray-100 dark:border-gray-700">
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
						Поиск
					</label>
					<input
						v-model="filters.search"
						type="text"
						placeholder="Имя или email"
						class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
					/>
				</div>
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
						Статус
					</label>
					<select
						v-model="filters.banned"
						class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
					>
						<option :value="null">Все</option>
						<option :value="true">Забаненные</option>
						<option :value="false">Активные</option>
					</select>
				</div>
			</div>
		</div>

		<div v-if="loading" class="flex items-center justify-center py-20">
			<div class="text-center">
				<svg class="animate-spin h-12 w-12 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
				</svg>
				<div class="text-gray-600 dark:text-gray-400">Загрузка...</div>
			</div>
		</div>

		<div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
			<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
				<thead class="bg-gray-50 dark:bg-gray-700">
					<tr>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
							Имя
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
							Email
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
							Баланс
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
							Статус
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
							Админ
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
							Дата регистрации
						</th>
						<th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
							Действия
						</th>
					</tr>
				</thead>
				<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
					<tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
						<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
							{{ user.name }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
							{{ user.email }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
							{{ formatCurrency(user.balance) }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<span
								class="px-2 py-1 text-xs font-semibold rounded-full"
								:class="user.banned_at 
									? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' 
									: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'"
							>
								{{ user.banned_at ? 'Забанен' : 'Активен' }}
							</span>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<span
								class="px-2 py-1 text-xs font-semibold rounded-full"
								:class="user.is_admin 
									? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' 
									: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
							>
								{{ user.is_admin ? 'Админ' : 'Пользователь' }}
							</span>
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
							{{ formatDate(user.created_at) }}
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
							<button
								v-if="!user.banned_at"
								@click="banUser(user)"
								class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 mr-4"
							>
								Забанить
							</button>
							<button
								v-else
								@click="unbanUser(user)"
								class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-4"
							>
								Разбанить
							</button>
							<button
								v-if="!user.is_admin"
								@click="makeAdmin(user)"
								class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 mr-4"
							>
								Сделать админом
							</button>
							<button
								v-else
								@click="removeAdmin(user)"
								class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300 mr-4"
							>
								Убрать админа
							</button>
							<button
								@click="deleteUser(user)"
								class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
							>
								Удалить
							</button>
						</td>
					</tr>
					<tr v-if="users.data.length === 0">
						<td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
							Нет пользователей
						</td>
					</tr>
				</tbody>
			</table>

			<div v-if="users.last_page > 1" class="bg-gray-50 dark:bg-gray-700 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-600">
				<div class="flex-1 flex justify-between sm:hidden">
					<button
						@click="changePage(users.current_page - 1)"
						:disabled="users.current_page === 1"
						class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
					>
						Назад
					</button>
					<button
						@click="changePage(users.current_page + 1)"
						:disabled="users.current_page === users.last_page"
						class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
					>
						Вперед
					</button>
				</div>
				<div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
					<div>
						<p class="text-sm text-gray-700 dark:text-gray-300">
							Показано <span class="font-medium">{{ users.from }}</span> - <span class="font-medium">{{ users.to }}</span> из <span class="font-medium">{{ users.total }}</span>
						</p>
					</div>
					<div>
						<nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
							<button
								@click="changePage(users.current_page - 1)"
								:disabled="users.current_page === 1"
								class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
							>
								Назад
							</button>
							<button
								v-for="page in pages"
								:key="page"
								@click="changePage(page)"
								:class="[
									'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
									page === users.current_page
										? 'z-10 bg-indigo-50 dark:bg-indigo-900 border-indigo-500 text-indigo-600 dark:text-indigo-300'
										: 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
								]"
							>
								{{ page }}
							</button>
							<button
								@click="changePage(users.current_page + 1)"
								:disabled="users.current_page === users.last_page"
								class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
							>
								Вперед
							</button>
						</nav>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import api from '../../services/api';

const users = ref({ data: [], current_page: 1, last_page: 1, from: 0, to: 0, total: 0 });
const loading = ref(false);
const filters = ref({
	search: '',
	banned: null,
});

const pages = computed(() => {
	const current = users.value.current_page;
	const last = users.value.last_page;
	const pagesArray = [];

	if (last <= 7) {
		for (let i = 1; i <= last; i++) {
			pagesArray.push(i);
		}
	} else {
		if (current <= 4) {
			for (let i = 1; i <= 5; i++) {
				pagesArray.push(i);
			}
			pagesArray.push('...');
			pagesArray.push(last);
		} else if (current >= last - 3) {
			pagesArray.push(1);
			pagesArray.push('...');
			for (let i = last - 4; i <= last; i++) {
				pagesArray.push(i);
			}
		} else {
			pagesArray.push(1);
			pagesArray.push('...');
			for (let i = current - 1; i <= current + 1; i++) {
				pagesArray.push(i);
			}
			pagesArray.push('...');
			pagesArray.push(last);
		}
	}

	return pagesArray;
});

const fetchUsers = async (page = 1) => {
	loading.value = true;
	try {
		const params = {
			page,
			per_page: 15,
		};

		if (filters.value.search) {
			params.search = filters.value.search;
		}

		if (filters.value.banned !== null) {
			params.banned = filters.value.banned;
		}

		const response = await api.get('/admin/users', { params });
		users.value = response.data;
	} catch (error) {
		console.error('Error fetching users:', error);
	} finally {
		loading.value = false;
	}
};

const changePage = (page) => {
	if (page < 1 || page > users.value.last_page || page === '...') {
		return;
	}
	fetchUsers(page);
};

const banUser = async (user) => {
	if (!confirm(`Вы уверены, что хотите забанить пользователя "${user.name}"?`)) {
		return;
	}

	try {
		await api.post(`/admin/users/${user.id}/ban`);
		await fetchUsers(users.value.current_page);
	} catch (error) {
		console.error('Error banning user:', error);
		const message = error.response?.data?.message || 'Ошибка при бане пользователя';
		alert(message);
	}
};

const unbanUser = async (user) => {
	if (!confirm(`Вы уверены, что хотите разбанить пользователя "${user.name}"?`)) {
		return;
	}

	try {
		await api.post(`/admin/users/${user.id}/unban`);
		await fetchUsers(users.value.current_page);
	} catch (error) {
		console.error('Error unbanning user:', error);
		const message = error.response?.data?.message || 'Ошибка при разбане пользователя';
		alert(message);
	}
};

const makeAdmin = async (user) => {
	if (!confirm(`Вы уверены, что хотите сделать пользователя "${user.name}" администратором?`)) {
		return;
	}

	try {
		await api.post(`/admin/users/${user.id}/make-admin`);
		await fetchUsers(users.value.current_page);
	} catch (error) {
		console.error('Error making admin:', error);
		const message = error.response?.data?.message || 'Ошибка при назначении администратора';
		alert(message);
	}
};

const removeAdmin = async (user) => {
	if (!confirm(`Вы уверены, что хотите убрать права администратора у пользователя "${user.name}"?`)) {
		return;
	}

	try {
		await api.post(`/admin/users/${user.id}/remove-admin`);
		await fetchUsers(users.value.current_page);
	} catch (error) {
		console.error('Error removing admin:', error);
		const message = error.response?.data?.message || 'Ошибка при снятии прав администратора';
		alert(message);
	}
};

const deleteUser = async (user) => {
	if (!confirm(`Вы уверены, что хотите удалить пользователя "${user.name}"? Это действие нельзя отменить.`)) {
		return;
	}

	try {
		await api.delete(`/admin/users/${user.id}`);
		await fetchUsers(users.value.current_page);
	} catch (error) {
		console.error('Error deleting user:', error);
		const message = error.response?.data?.message || 'Ошибка при удалении пользователя';
		alert(message);
	}
};

const formatCurrency = (value) => {
	return new Intl.NumberFormat('ru-RU', {
		style: 'currency',
		currency: 'USD',
	}).format(value);
};

const formatDate = (dateString) => {
	const date = new Date(dateString);
	return date.toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

let searchTimeout = null;

watch([() => filters.value.search, () => filters.value.banned], () => {
	if (searchTimeout) {
		clearTimeout(searchTimeout);
	}
	searchTimeout = setTimeout(() => {
		fetchUsers(1);
	}, 500);
});

onMounted(() => {
	fetchUsers();
});
</script>
