import { createRouter, createWebHashHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
	{
		path: '/login',
		name: 'Login',
		component: () => import('../components/auth/Login.vue'),
		meta: { guest: true },
	},
	{
		path: '/register',
		name: 'Register',
		component: () => import('../components/auth/Register.vue'),
		meta: { guest: true },
	},
	{
		path: '/public/locations/:id',
		name: 'PublicLocationView',
		component: () => import('../components/locations/PublicLocationView.vue'),
		meta: { public: true },
	},
	{
		path: '/',
		component: () => import('../components/layout/AppLayout.vue'),
		meta: { requiresAuth: true },
		children: [
			{
				path: '',
				name: 'Dashboard',
				component: () => import('../components/dashboard/Dashboard.vue'),
			},
			{
				path: '/tournaments',
				name: 'Tournaments',
				component: () => import('../components/tournaments/TournamentList.vue'),
			},
			{
				path: '/tournaments/create',
				name: 'TournamentCreate',
				component: () => import('../components/tournaments/TournamentForm.vue'),
			},
			{
				path: '/tournaments/:id/edit',
				name: 'TournamentEdit',
				component: () => import('../components/tournaments/TournamentForm.vue'),
			},
			{
				path: '/packs',
				name: 'Packs',
				component: () => import('../components/packs/PackList.vue'),
			},
			{
				path: '/packs/:id',
				name: 'PackDetail',
				component: () => import('../components/packs/PackDetail.vue'),
			},
			{
				path: '/locations',
				name: 'Locations',
				component: () => import('../components/locations/LocationList.vue'),
			},
			{
				path: '/locations/:id',
				name: 'LocationDetail',
				component: () => import('../components/locations/LocationDetail.vue'),
			},
			{
				path: '/locations/:locationId/tournaments/:id',
				name: 'LocationTournamentDetail',
				component: () => import('../components/locations/LocationTournamentDetail.vue'),
			},
			{
				path: '/settings',
				name: 'Settings',
				component: () => import('../components/settings/Settings.vue'),
			},
		],
	},
	{
		path: '/admin',
		component: () => import('../components/admin/AdminLayout.vue'),
		meta: { requiresAuth: true, requiresAdmin: true },
		redirect: '/admin/settings',
		children: [
			{
				path: 'settings',
				name: 'AdminSettings',
				component: () => import('../components/admin/AdminSettings.vue'),
			},
			{
				path: 'rooms',
				redirect: '/admin/settings',
			},
			{
				path: 'currencies',
				redirect: '/admin/settings',
			},
			{
				path: 'users',
				redirect: '/admin/settings',
			},
			{
				path: 'users/:id',
				name: 'AdminUserDetail',
				component: () => import('../components/admin/UserDetail.vue'),
			},
		],
	},
];

const router = createRouter({
	history: createWebHashHistory(),
	routes,
});

const pageTitles = {
	Login: 'Вход - Poker Manager',
	Register: 'Регистрация - Poker Manager',
	Dashboard: 'Дашборд - Poker Manager',
	Tournaments: 'Турниры - Poker Manager',
	TournamentCreate: 'Создать турнир - Poker Manager',
	TournamentEdit: 'Редактировать турнир - Poker Manager',
	Packs: 'Паки турниров - Poker Manager',
	PackDetail: 'Детали пака - Poker Manager',
	Locations: 'Локации - Poker Manager',
	LocationDetail: 'Детали локации - Poker Manager',
	LocationTournamentDetail: 'Детали турнира - Poker Manager',
	Settings: 'Настройки - Poker Manager',
	PublicLocationView: 'Публичная локация - Poker Manager',
	AdminSettings: 'Настройки - Админ-панель',
	AdminUserDetail: 'Детали пользователя - Админ-панель',
};

router.beforeEach(async (to, from, next) => {
	const authStore = useAuthStore();
	const token = localStorage.getItem('auth_token');

	if (to.meta.public && to.name === 'PublicLocationView' && token) {
		if (token && !authStore.isAuthenticated) {
			try {
				await authStore.fetchUser();
			} catch (error) {
				localStorage.removeItem('auth_token');
			}
		}
		if (authStore.isAuthenticated) {
			next({ name: 'LocationDetail', params: { id: to.params.id } });
			return;
		}
	}

	if (to.meta.public) {
		next();
		return;
	}

	if (to.meta.requiresAuth && !token) {
		next({ name: 'Login' });
		return;
	}

	if (to.meta.guest && token) {
		next({ name: 'Dashboard' });
		return;
	}

	if (token && !authStore.isAuthenticated && to.meta.requiresAuth) {
		try {
			await authStore.fetchUser();
		} catch (error) {
			localStorage.removeItem('auth_token');
			if (to.meta.requiresAuth) {
				next({ name: 'Login' });
				return;
			}
		}
	}

	if (to.meta.requiresAdmin && (!authStore.user || !authStore.user.is_admin)) {
		next({ name: 'Dashboard' });
		return;
	}

	next();
});

router.afterEach((to) => {
	document.title = pageTitles[to.name] || 'Poker Manager';
});

export default router;
