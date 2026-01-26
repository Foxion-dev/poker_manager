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
		],
	},
];

const router = createRouter({
	history: createWebHashHistory(),
	routes,
});

router.beforeEach((to, from, next) => {
	const authStore = useAuthStore();
	const token = localStorage.getItem('auth_token');

	if (to.meta.requiresAuth && !token) {
		next({ name: 'Login' });
	} else if (to.meta.guest && token) {
		next({ name: 'Dashboard' });
	} else {
		if (token && !authStore.isAuthenticated) {
			authStore.fetchUser().then(() => {
				next();
			}).catch(() => {
				next({ name: 'Login' });
			});
		} else {
			next();
		}
	}
});

export default router;
