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
		const token = localStorage.getItem('auth_token');
		if (token) {
			config.headers.Authorization = `Bearer ${token}`;
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
			if (window.location.pathname !== '/login' && window.location.pathname !== '/register') {
				window.location.href = '/#/login';
			}
		}
		return Promise.reject(error);
	}
);

export default api;
