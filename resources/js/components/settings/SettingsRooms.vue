<template>
	<section class="mb-8">
		<h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Доступные румы</h3>
		<p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
			Отключённые румы не будут показываться в выпадающих списках (например, при создании турнира). Баланс можно указать для каждого рума.
		</p>
		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
			<div v-if="roomStore.loading" class="p-8 text-center text-gray-500 dark:text-gray-400">
				Загрузка…
			</div>
			<div v-else-if="!roomStore.rooms.length" class="p-8 text-center text-gray-500 dark:text-gray-400">
				Нет доступных румов
			</div>
			<ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
				<li
					v-for="room in roomStore.rooms"
					:key="room.id"
					class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
				>
					<div
						v-if="room.image"
						class="h-12 w-12 rounded-lg overflow-hidden flex-shrink-0 bg-gray-100 dark:bg-gray-600"
					>
						<img
							:src="getRoomImageUrl(room.image)"
							:alt="room.name"
							class="h-full w-full object-cover"
						/>
					</div>
					<div
						v-else
						class="h-12 w-12 rounded-lg flex items-center justify-center flex-shrink-0 bg-gray-100 dark:bg-gray-600 text-xl"
					>
						{{ room.icon || '🃏' }}
					</div>
					<div class="min-w-0 flex-1">
						<p class="font-medium text-gray-900 dark:text-white truncate">{{ room.name }}</p>
						<p v-if="room.currency" class="text-sm text-gray-500 dark:text-gray-400 truncate">
							{{ room.currency.code }}
						</p>
						<p
							v-else-if="room.currencies?.length"
							class="text-sm text-gray-500 dark:text-gray-400 truncate"
						>
							{{ room.currencies.map((c) => c.code).join(', ') }}
						</p>
					</div>
					<div class="flex items-center gap-3 flex-shrink-0">
						<div v-if="editingRoomId === room.id" class="flex items-center gap-2">
							<div class="w-24">
								<label class="sr-only">Баланс</label>
								<input
									ref="balanceInputRef"
									:value="getRoomBalance(room.id)"
									type="number"
									step="0.01"
									min="0"
									:disabled="savingRoomId === room.id"
									@input="setRoomBalanceInput(room.id, $event.target.value)"
									class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:opacity-50"
									placeholder="0"
								/>
							</div>
							<select
								:value="getRoomCurrencyId(room.id)"
								:disabled="savingRoomId === room.id"
								@change="setRoomCurrencyId(room.id, $event.target.value)"
								class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:opacity-50 min-w-0 max-w-24"
							>
								<option value="">—</option>
								<option
									v-for="c in currencyStore.currencies"
									:key="c.id"
									:value="c.id"
								>
									{{ c.code }}
								</option>
							</select>
							<button
								type="button"
								title="Сохранить"
								:disabled="savingRoomId === room.id"
								@click="handleBalanceSave(room.id)"
								class="p-1.5 rounded-lg text-gray-400 hover:text-green-600 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-green-400 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50"
							>
								<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
									<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
								</svg>
							</button>
							<button
								type="button"
								title="Отменить"
								:disabled="savingRoomId === room.id"
								@click="cancelBalanceEdit(room.id)"
								class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-red-400 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50"
							>
								<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
									<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
								</svg>
							</button>
						</div>
						<div v-else class="flex items-center gap-2">
							<span class="text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap min-w-[7rem]">
								{{ hasRoomBalance(room.id) ? formatBalanceDisplay(room.id) : '—' }}
							</span>
							<button
								type="button"
								title="Изменить баланс"
								@click="startEditingBalance(room.id)"
								class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-indigo-400 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
							>
								<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
									<path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
								</svg>
							</button>
						</div>
						<label class="flex items-center gap-2 cursor-pointer">
							<span class="text-sm text-gray-600 dark:text-gray-400">В списках</span>
							<button
								type="button"
								role="switch"
								:aria-checked="!isRoomDisabled(room.id)"
								:disabled="togglingRoomId === room.id"
								@click="toggleRoom(room.id)"
								class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
								:class="
									isRoomDisabled(room.id)
										? 'bg-gray-200 dark:bg-gray-600'
										: 'bg-indigo-600'
								"
							>
								<span
									class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition"
									:class="isRoomDisabled(room.id) ? 'translate-x-0.5' : 'translate-x-5'"
								/>
							</button>
						</label>
					</div>
				</li>
			</ul>
		</div>
	</section>
</template>

<script setup>
import { nextTick, onMounted, ref } from 'vue';
import { useRoomStore } from '../../stores/rooms';
import { useCurrencyStore } from '../../stores/currencies';
import { roomService } from '../../services/roomService';
import { toUsd } from '../../services/moneyService';

const roomStore = useRoomStore();
const currencyStore = useCurrencyStore();
const togglingRoomId = ref(null);
const savingRoomId = ref(null);
const editingRoomId = ref(null);
const balanceInputRef = ref(null);
const roomBalances = ref({});
const roomBalanceInputs = ref({});
const roomCurrencyInputs = ref({});
const initialAttachedRoomIds = ref(new Set());

const getRoomImageUrl = (imagePath) => {
	if (!imagePath) return null;
	if (imagePath.startsWith('http')) return imagePath;
	return `/storage/${imagePath}`;
};

const getRoomBalance = (roomId) => {
	if (roomBalanceInputs.value[roomId] !== undefined) {
		return roomBalanceInputs.value[roomId];
	}
	const data = roomBalances.value[roomId];
	return data != null && data.balance !== undefined ? data.balance : '';
};

const getRoomCurrencyId = (roomId) => {
	if (roomCurrencyInputs.value[roomId] !== undefined) {
		return roomCurrencyInputs.value[roomId] === '' ? '' : roomCurrencyInputs.value[roomId];
	}
	const data = roomBalances.value[roomId];
	const id = data?.currency_id ?? data?.currency?.id;
	return id ?? '';
};

const setRoomBalanceInput = (roomId, value) => {
	roomBalanceInputs.value[roomId] = value === '' ? '' : value;
};

const setRoomCurrencyId = (roomId, value) => {
	roomCurrencyInputs.value[roomId] = value === '' ? '' : value;
};

const getCurrencyForRoom = (roomId) => {
	const id = getRoomCurrencyId(roomId);
	if (!id) return null;
	return currencyStore.currencies.find((c) => c.id === parseInt(id, 10)) ?? roomBalances.value[roomId]?.currency ?? null;
};

const hasRoomBalance = (roomId) => {
	const balance = getRoomBalance(roomId);
	if (balance === '' || balance === undefined) return false;
	const num = parseFloat(balance);
	return !Number.isNaN(num) && num >= 0;
};

const formatBalanceDisplay = (roomId) => {
	const balance = getRoomBalance(roomId);
	const num = parseFloat(balance);
	if (balance === '' || Number.isNaN(num) || num <= 0) return '';
	const currency = getCurrencyForRoom(roomId);
	const rateToUsd = currency?.rate_to_usd != null ? parseFloat(currency.rate_to_usd) : 1;
	const usd = toUsd(num, rateToUsd);
	const code = currency?.code ?? 'USD';
	return `${num.toFixed(2)} ${code} (${usd.toFixed(2)} $)`;
};

const isRoomDisabled = (roomId) => roomStore.disabledRoomIds.includes(roomId);

const startEditingBalance = (roomId) => {
	editingRoomId.value = roomId;
	nextTick(() => {
		const el = Array.isArray(balanceInputRef.value) ? balanceInputRef.value.find(Boolean) : balanceInputRef.value;
		el?.focus();
	});
};

const handleBalanceSave = async (roomId) => {
	await saveBalance(roomId);
	editingRoomId.value = null;
};

const cancelBalanceEdit = (roomId) => {
	roomBalanceInputs.value[roomId] = undefined;
	roomCurrencyInputs.value[roomId] = undefined;
	editingRoomId.value = null;
};

const toggleRoom = async (roomId) => {
	togglingRoomId.value = roomId;
	try {
		await roomStore.setRoomDisabled(roomId, !isRoomDisabled(roomId));
	} finally {
		togglingRoomId.value = null;
	}
};

const saveBalance = async (roomId) => {
	const raw = roomBalanceInputs.value[roomId] ?? roomBalances.value[roomId]?.balance ?? '';
	const value = raw === '' ? 0 : parseFloat(raw);
	if (Number.isNaN(value) || value < 0) {
		roomBalanceInputs.value[roomId] = roomBalances.value[roomId]?.balance ?? '';
		return;
	}
	const currencyId = getRoomCurrencyId(roomId);
	const currencyIdNum = currencyId === '' ? null : parseInt(currencyId, 10);
	const roomIdNum = Number(roomId);
	savingRoomId.value = roomId;
	try {
		if (initialAttachedRoomIds.value.has(roomIdNum)) {
			await roomService.updateUserRoomBalance(roomIdNum, value, currencyIdNum);
		} else {
			await roomService.attachUserRoom(roomIdNum, value, currencyIdNum);
			initialAttachedRoomIds.value.add(roomIdNum);
		}
		roomBalances.value[roomId] = {
			balance: value,
			currency_id: currencyIdNum,
			currency: getCurrencyForRoom(roomId) ?? (currencyIdNum ? currencyStore.currencies.find((c) => c.id === currencyIdNum) : null),
		};
		roomBalanceInputs.value[roomId] = undefined;
		roomCurrencyInputs.value[roomId] = undefined;
	} catch (err) {
		roomBalanceInputs.value[roomId] = getRoomBalance(roomId);
	} finally {
		savingRoomId.value = null;
	}
};

onMounted(async () => {
	await Promise.all([
		roomStore.fetchRooms(),
		roomStore.fetchDisabledRoomIds(),
		currencyStore.fetchCurrencies(),
	]);
	try {
		const userRooms = await roomService.getUserRooms();
		userRooms.forEach((ur) => {
			const rid = Number(ur.room_id);
			roomBalances.value[rid] = {
				balance: ur.balance,
				currency_id: ur.currency_id ?? ur.currency?.id ?? null,
				currency: ur.currency ?? null,
			};
			initialAttachedRoomIds.value.add(rid);
		});
	} catch (_) {}
});
</script>
