<template>
	<div>
		<LocationDetailHeader
			v-if="location"
			:location="location"
			:current-user="currentUser"
			:copied="copied"
			@edit="editLocation"
			@show-admin-form="showAdminForm = true"
			@show-users-form="showUsersForm = true"
			@delete="deleteLocation"
			@copy-link="copyPublicLink"
		/>

		<LocationPasswordModal
			v-model="showPasswordForm"
			:password="locationPassword"
			:error="passwordError"
			:checking="checkingPassword"
			@update:password="locationPassword = $event"
			@submit="submitPassword"
			@cancel="$router.push('/locations')"
		/>

		<div v-if="loading && !showPasswordForm" class="flex items-center justify-center py-20">
			<div class="text-center">
				<svg class="animate-spin h-12 w-12 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
					<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
				</svg>
				<div class="text-gray-600 dark:text-gray-400">Загрузка...</div>
			</div>
		</div>

		<div v-else-if="location && !showPasswordForm" class="space-y-6">
			<LocationStatsCards :location="location" />
			<LocationTopPlayers :location="location" />
			<LocationTournamentsSection
				:location="location"
				:tournaments="tournaments"
				:location-id="route.params.id"
				@create-tournament="openTournamentForm"
				@edit-tournament="editTournament"
				@delete-tournament="deleteTournament"
			/>
		</div>

		<LocationFormModal
			v-model="showLocationForm"
			:form="locationForm"
			:currencies="allCurrencies"
			:saving="savingLocation"
			@close="closeLocationForm"
			@save="saveLocation"
		/>

		<LocationTournamentFormModal
			v-model="showTournamentForm"
			:form="tournamentForm"
			:date-value="locationTournamentDate"
			:editing-tournament="editingTournament"
			:currencies="availableCurrencies"
			:search-query="participantSearchQuery"
			:filtered-users="filteredLocationUsers"
			:new-participant-name="newParticipantName"
			:adding-new-user="addingNewUser"
			:saving="savingTournament"
			:location-users="locationUsers"
			@close="closeTournamentForm"
			@save="saveTournament"
			@update:date="tournamentForm.date = $event && $event instanceof Date ? $event.toISOString().split('T')[0] : ''"
			@update:searchQuery="participantSearchQuery = $event"
			@update:newParticipantName="newParticipantName = $event"
			@add-participant="addParticipant"
			@add-new-user="addNewUserToLocationAndTournament"
			@remove-participant="removeParticipant"
		/>

		<LocationAdminModal
			v-model="showAdminForm"
			:location="location"
			:available-users="availableUsers"
			:new-admin-user-id="newAdminUserId"
			:adding="addingAdmin"
			@close="showAdminForm = false"
			@add-admin="addAdmin"
			@remove-admin="removeAdmin"
			@update:newAdminUserId="newAdminUserId = $event"
		/>

		<LocationUsersModal
			v-model="showUsersForm"
			:location="location"
			:available-users-for-location="availableUsersForLocation"
			:new-user-id="newUserId"
			:new-user-name="newUserName"
			:adding="addingUser"
			@close="showUsersForm = false"
			@add-user="addUser"
			@remove-user="removeUser"
			@update:newUserId="newUserId = $event"
			@update:newUserName="newUserName = $event"
		/>
	</div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { locationService } from '../../services/locationService';
import { useAuthStore } from '../../stores/auth';
import { storeToRefs } from 'pinia';
import api from '../../services/api';
import LocationDetailHeader from './LocationDetailHeader.vue';
import LocationPasswordModal from './LocationPasswordModal.vue';
import LocationStatsCards from './LocationStatsCards.vue';
import LocationTopPlayers from './LocationTopPlayers.vue';
import LocationTournamentsSection from './LocationTournamentsSection.vue';
import LocationFormModal from './LocationFormModal.vue';
import LocationTournamentFormModal from './LocationTournamentFormModal.vue';
import LocationAdminModal from './LocationAdminModal.vue';
import LocationUsersModal from './LocationUsersModal.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const { user: currentUser } = storeToRefs(authStore);

const location = ref(null);
const tournaments = ref([]);
const allUsers = ref([]);
const allCurrencies = ref([]);
const participantSearchQuery = ref('');
const newParticipantName = ref('');
const addingNewUser = ref(false);
const loading = ref(false);
const showLocationForm = ref(false);
const showTournamentForm = ref(false);
const showAdminForm = ref(false);
const showUsersForm = ref(false);
const showPasswordForm = ref(false);
const locationPassword = ref('');
const checkingPassword = ref(false);
const passwordError = ref('');
const editingTournament = ref(null);
const savingLocation = ref(false);
const savingTournament = ref(false);
const addingAdmin = ref(false);
const newAdminUserId = ref('');
const copied = ref(false);
const addingUser = ref(false);
const newUserId = ref('');
const newUserName = ref('');

const locationForm = ref({
	name: '',
	description: '',
	is_public: false,
	password: '',
	selected_currencies: [],
});

const tournamentForm = ref({
	name: '',
	date: '',
	buyin: 0,
	bounty: null,
	currency_id: null,
	format: 'classic',
	itm_percentage: 15,
	rake: 30,
	rake_type: 'fixed',
	participants: [{ user_id: '', name: '', prize: null }],
});

const locationTournamentDate = computed({
	get: () => {
		const d = tournamentForm.value.date;
		if (!d) return null;
		return new Date(typeof d === 'string' ? d.split('T')[0] : d);
	},
	set: (v) => {
		tournamentForm.value.date = v && v instanceof Date ? v.toISOString().split('T')[0] : '';
	}
});

const locationUsers = computed(() => location.value?.users || []);

const filteredLocationUsers = computed(() => {
	const query = (participantSearchQuery.value || '').trim().toLowerCase();
	const participantUserIds = new Set(
		tournamentForm.value.participants
			.filter(p => p.user_id)
			.map(p => String(p.user_id))
	);
	const participantNames = new Set(
		tournamentForm.value.participants
			.filter(p => !p.user_id && p.name && p.name !== 'Без имени')
			.map(p => (p.name || '').trim().toLowerCase())
	);
	let users = locationUsers.value.filter(u => {
		const userId = u.user_id ?? null;
		const name = (u.display_name || u.name || '').trim().toLowerCase();
		if (userId && participantUserIds.has(String(userId))) return false;
		if (name && participantNames.has(name)) return false;
		return true;
	});
	if (!query) return users;
	return users.filter(u => {
		const name = (u.display_name || u.name || '').toLowerCase();
		return name.includes(query);
	});
});

const availableUsers = computed(() => {
	if (!location.value || !allUsers.value.length) return [];
	const adminIds = [location.value.user_id, ...(location.value.admins?.map(a => a.id) || [])];
	return allUsers.value.filter(user => !adminIds.includes(user.id));
});

const availableUsersForLocation = computed(() => {
	if (!location.value || !allUsers.value.length) return [];
	const existingUserIds = [
		location.value.user_id,
		...(location.value.admins?.map(a => a.id) || []),
		...(location.value.users?.filter(u => u.user_id).map(u => u.user_id) || [])
	];
	return allUsers.value.filter(user => !existingUserIds.includes(user.id));
});

const availableCurrencies = computed(() => {
	if (!location.value || !location.value.currencies?.length) {
		return allCurrencies.value;
	}
	return location.value.currencies;
});

const fetchLocation = async (password = null) => {
	loading.value = true;
	try {
		const params = password ? { password } : {};
		location.value = await locationService.getById(route.params.id, params);
		locationForm.value = {
			name: location.value.name,
			description: location.value.description || '',
			is_public: location.value.is_public,
			password: '',
			selected_currencies: location.value.currencies?.map(c => c.id) || [],
		};
		await fetchTournaments(password);
		await fetchUsers();
		await fetchCurrencies();
	} catch (error) {
		if (error.response?.status === 403 && error.response?.data?.requires_password) {
			showPasswordForm.value = true;
			passwordError.value = '';
			loading.value = false;
		} else {
			console.error('Error fetching location:', error);
			alert('Ошибка при загрузке локации');
			loading.value = false;
		}
		return;
	}
	loading.value = false;
};

const submitPassword = async () => {
	if (!locationPassword.value.trim()) {
		passwordError.value = 'Введите пароль';
		return;
	}

	passwordError.value = '';
	checkingPassword.value = true;
	try {
		await fetchLocation(locationPassword.value);
		showPasswordForm.value = false;
		locationPassword.value = '';
		passwordError.value = '';
	} catch (error) {
		if (error.response?.status === 403) {
			passwordError.value = error.response?.data?.message || 'Неверный пароль';
			locationPassword.value = '';
		} else {
			passwordError.value = error.response?.data?.message || 'Ошибка при проверке пароля';
		}
	} finally {
		checkingPassword.value = false;
	}
};

const fetchTournaments = async (password = null) => {
	try {
		const params = password ? { password } : {};
		tournaments.value = await locationService.getTournaments(route.params.id, params);
	} catch (error) {
		console.error('Error fetching tournaments:', error);
	}
};

const fetchUsers = async () => {
	try {
		const response = await api.get('/users/list');
		allUsers.value = response.data || [];
	} catch (error) {
		console.error('Error fetching users:', error);
	}
};

const fetchCurrencies = async () => {
	try {
		const response = await api.get('/currencies');
		allCurrencies.value = response.data || [];
	} catch (error) {
		console.error('Error fetching currencies:', error);
	}
};

const editLocation = () => {
	showLocationForm.value = true;
};

const closeLocationForm = () => {
	showLocationForm.value = false;
};

const saveLocation = async () => {
	savingLocation.value = true;
	try {
		const data = { ...locationForm.value };
		const currencyIds = data.selected_currencies || [];
		delete data.selected_currencies;
		if (!data.password) delete data.password;

		await locationService.update(route.params.id, data);
		await locationService.syncCurrencies(route.params.id, { currency_ids: currencyIds });
		closeLocationForm();
		await fetchLocation();
	} catch (error) {
		console.error('Error saving location:', error);
		alert('Ошибка при сохранении локации');
	} finally {
		savingLocation.value = false;
	}
};

const deleteLocation = async () => {
	if (!confirm('Вы уверены, что хотите удалить эту локацию? Все турниры в ней также будут удалены.')) return;
	try {
		await locationService.delete(route.params.id);
		router.push('/locations');
	} catch (error) {
		console.error('Error deleting location:', error);
		alert('Ошибка при удалении локации');
	}
};

const copyPublicLink = async () => {
	if (!location.value) return;
	const publicUrl = `${window.location.origin}/#/public/locations/${location.value.id}`;
	try {
		await navigator.clipboard.writeText(publicUrl);
		copied.value = true;
		setTimeout(() => { copied.value = false; }, 2000);
	} catch (error) {
		const textArea = document.createElement('textarea');
		textArea.value = publicUrl;
		textArea.style.position = 'fixed';
		textArea.style.left = '-999999px';
		document.body.appendChild(textArea);
		textArea.select();
		try {
			document.execCommand('copy');
			copied.value = true;
			setTimeout(() => { copied.value = false; }, 2000);
		} catch (err) {
			alert(`Ссылка: ${publicUrl}`);
		}
		document.body.removeChild(textArea);
	}
};

const openTournamentForm = () => {
	editingTournament.value = null;
	const today = new Date().toISOString().split('T')[0];
	tournamentForm.value = {
		name: '',
		date: today,
		buyin: 0,
		bounty: null,
		currency_id: availableCurrencies.value.length === 1 ? availableCurrencies.value[0].id : null,
		format: 'classic',
		itm_percentage: 15,
		rake: 30,
		rake_type: 'fixed',
		participants: [{ user_id: '', name: '', prize: null }],
	};
	showTournamentForm.value = true;
};

const editTournament = (tournament) => {
	editingTournament.value = tournament;
	tournamentForm.value = {
		name: tournament.name,
		date: tournament.date,
		buyin: tournament.buyin,
		bounty: tournament.bounty ?? null,
		currency_id: tournament.currency_id,
		format: tournament.format,
		itm_percentage: tournament.itm_percentage ?? 15,
		rake: tournament.rake ?? 30,
		rake_type: tournament.rake_type ?? 'fixed',
		participants: tournament.participants.map(p => ({
			user_id: p.user_id ? String(p.user_id) : '',
			name: p.name || '',
			prize: null,
		})),
	};
	showTournamentForm.value = true;
};

const closeTournamentForm = () => {
	showTournamentForm.value = false;
	editingTournament.value = null;
	participantSearchQuery.value = '';
};

const addParticipant = (locationUser) => {
	const isDuplicate = tournamentForm.value.participants.some(p => {
		if (locationUser.user_id) {
			return p.user_id && (p.user_id == locationUser.user_id || p.user_id === locationUser.user_id);
		}
		return !p.user_id && p.name === locationUser.name;
	});
	if (isDuplicate) return;
	const displayName = locationUser.display_name || locationUser.name || '';
	if (!displayName || displayName === 'Без имени') return;
	tournamentForm.value.participants.push({
		user_id: locationUser.user_id ? String(locationUser.user_id) : '',
		name: locationUser.user_id ? '' : displayName,
		prize: null,
	});
};

const removeParticipant = (participant) => {
	const idx = tournamentForm.value.participants.findIndex(p =>
		(p.user_id != null && p.user_id !== '' && p.user_id == participant.user_id) ||
		((!p.user_id || p.user_id === '') && (!participant.user_id || participant.user_id === '') && p.name === participant.name)
	);
	if (idx >= 0) tournamentForm.value.participants.splice(idx, 1);
};

const addNewUserToLocationAndTournament = async () => {
	if (!newParticipantName.value.trim()) return;
	addingNewUser.value = true;
	try {
		await locationService.addUser(route.params.id, { name: newParticipantName.value.trim() });
		await fetchLocation();
		const newUser = location.value.users.find(u => !u.user_id && u.name === newParticipantName.value.trim());
		if (newUser) {
			tournamentForm.value.participants.push({
				user_id: '',
				name: newUser.display_name || newUser.name,
				prize: null,
			});
		}
		newParticipantName.value = '';
	} catch (error) {
		alert(error.response?.data?.message || 'Ошибка при добавлении пользователя');
	} finally {
		addingNewUser.value = false;
	}
};

const saveTournament = async () => {
	const validParticipants = tournamentForm.value.participants.filter(p => {
		const hasUserId = p.user_id !== null && p.user_id !== undefined && p.user_id !== '';
		const hasName = p.name && p.name.trim() !== '' && p.name.trim() !== 'Без имени';
		return hasUserId || hasName;
	});

	if (validParticipants.length === 0) {
		alert('Необходимо добавить хотя бы одного участника.');
		return;
	}

	const uniqueUserIds = validParticipants.map(p => p.user_id).filter(id => id != null && id !== '');
	if (new Set(uniqueUserIds).size !== uniqueUserIds.length) {
		alert('Каждый пользователь может быть добавлен только один раз.');
		return;
	}

	savingTournament.value = true;
	try {
		const data = {
			...tournamentForm.value,
			participants: validParticipants.map((p, i) => ({
				user_id: p.user_id && p.user_id !== '' ? (typeof p.user_id === 'string' ? parseInt(p.user_id) : p.user_id) : null,
				name: p.name && p.name.trim() !== '' && p.name.trim() !== 'Без имени' ? p.name.trim() : null,
				place: i + 1,
			})).filter(p => p.user_id || p.name),
		};

		if (editingTournament.value) {
			await locationService.updateTournament(route.params.id, editingTournament.value.id, data);
		} else {
			await locationService.createTournament(route.params.id, data);
		}
		closeTournamentForm();
		await fetchLocation();
	} catch (error) {
		const errorMessage = error.response?.data?.message || error.response?.data?.errors?.participants?.[0] || 'Ошибка при сохранении турнира';
		alert(errorMessage);
	} finally {
		savingTournament.value = false;
	}
};

const deleteTournament = async (id) => {
	if (!confirm('Вы уверены, что хотите удалить этот турнир?')) return;
	try {
		await locationService.deleteTournament(route.params.id, id);
		await fetchLocation();
	} catch (error) {
		alert('Ошибка при удалении турнира');
	}
};

const addAdmin = async () => {
	if (!newAdminUserId.value) return;
	addingAdmin.value = true;
	try {
		await locationService.addAdmin(route.params.id, { user_id: newAdminUserId.value });
		newAdminUserId.value = '';
		await fetchLocation();
	} catch (error) {
		alert(error.response?.data?.message || 'Ошибка при добавлении админа');
	} finally {
		addingAdmin.value = false;
	}
};

const removeAdmin = async (adminId) => {
	if (!confirm('Вы уверены, что хотите удалить этого админа?')) return;
	try {
		await locationService.removeAdmin(route.params.id, adminId);
		await fetchLocation();
	} catch (error) {
		alert(error.response?.data?.message || 'Ошибка при удалении админа');
	}
};

const addUser = async () => {
	if (!newUserId.value && !newUserName.value) return;
	addingUser.value = true;
	try {
		const data = {};
		if (newUserId.value) data.user_id = newUserId.value;
		if (newUserName.value) data.name = newUserName.value.trim();
		await locationService.addUser(route.params.id, data);
		newUserId.value = '';
		newUserName.value = '';
		await fetchLocation();
	} catch (error) {
		alert(error.response?.data?.message || 'Ошибка при добавлении пользователя');
	} finally {
		addingUser.value = false;
	}
};

const removeUser = async (userId) => {
	if (!confirm('Вы уверены, что хотите удалить этого пользователя из локации?')) return;
	try {
		await locationService.removeUser(route.params.id, userId);
		await fetchLocation();
	} catch (error) {
		alert('Ошибка при удалении пользователя');
	}
};

onMounted(() => {
	fetchLocation();
});
</script>
