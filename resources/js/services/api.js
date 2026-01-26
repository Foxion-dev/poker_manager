import axios from 'axios';

const api = axios.create({
	baseURL: '/api',
	headers: {
		'Content-Type': 'application/json',
		'Accept': 'application/json',
	},
});

api.interceptors.request.use(
	(config) => {
		const isPublicRoute = config.url?.includes('/public/');
		if (!isPublicRoute) {
			const token = localStorage.getItem('auth_token');
			if (token) {
				config.headers.Authorization = `Bearer ${token}`;
			}
		}
		return config;
	},
	(error) => {
		return Promise.reject(error);
	}
);

api.interceptors.response.use(
	(response) => response,
	(error) => {
		const isPublicRoute = error.config?.url?.includes('/public/');
		
		if (error.response?.status === 401 && !isPublicRoute) {
			localStorage.removeItem('auth_token');
			const currentHash = window.location.hash || '';
			const currentPathname = window.location.pathname || '';
			const fullPath = (currentHash + currentPathname).toLowerCase();
			
			const isPublicPage = fullPath.includes('/public/') || fullPath.includes('publiclocationview');
			const isAuthPage = fullPath.includes('/login') || fullPath.includes('/register');
			
			if (!isAuthPage && !isPublicPage) {
				window.location.hash = '/login';
			}
		}
		return Promise.reject(error);
	}
);

export default api;
