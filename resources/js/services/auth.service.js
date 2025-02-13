import axios from 'axios';

class AuthService {
    async login(credentials) {
        try {
            const response = await axios.post('/login', credentials);
            if (response.data.token) {
                localStorage.setItem('token', response.data.token);
                localStorage.setItem('user', JSON.stringify(response.data.current_user));
                axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
            }
            return response.data;
        } catch (error) {
            throw error;
        }
    }

    async logout() {
        try {
            await axios.post('/logout');
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            delete axios.defaults.headers.common['Authorization'];
        } catch (error) {
            console.error('Logout error:', error);
            // Still remove items even if API call fails
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            delete axios.defaults.headers.common['Authorization'];
        }
    }

    getCurrentUser() {
        const userStr = localStorage.getItem('user');
        if (userStr) return JSON.parse(userStr);
        return null;
    }

    getToken() {
        return localStorage.getItem('token');
    }

    isAuthenticated() {
        return !!this.getToken();
    }

    async refreshToken() {
        try {
            const response = await axios.post('/refresh');
            if (response.data.token) {
                localStorage.setItem('token', response.data.token);
                axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
            }
            return response.data;
        } catch (error) {
            throw error;
        }
    }

    setupAxiosInterceptors() {
        // Request interceptor
        axios.interceptors.request.use(
            (config) => {
                const token = this.getToken();
                if (token) {
                    config.headers.Authorization = `Bearer ${token}`;
                }
                return config;
            },
            (error) => {
                return Promise.reject(error);
            }
        );

        // Response interceptor
        axios.interceptors.response.use(
            (response) => response,
            async (error) => {
                const originalRequest = error.config;

                // If the error is 401 and we haven't retried yet
                if (error.response?.status === 401 && !originalRequest._retry) {
                    originalRequest._retry = true;

                    try {
                        // Try to refresh the token
                        const { token } = await this.refreshToken();
                        
                        // Update the authorization header
                        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
                        originalRequest.headers['Authorization'] = `Bearer ${token}`;
                        
                        // Retry the original request
                        return axios(originalRequest);
                    } catch (refreshError) {
                        // If refresh token fails, logout user
                        await this.logout();
                        window.location.href = '/login';
                        return Promise.reject(refreshError);
                    }
                }

                return Promise.reject(error);
            }
        );
    }
}

export const authService = new AuthService();
