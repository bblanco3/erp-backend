import axios from 'axios';

// Set default base URL
axios.defaults.baseURL = process.env.REACT_APP_API_URL || window.location.origin;

// Set default headers
axios.defaults.headers.common['Content-Type'] = 'application/json';
axios.defaults.headers.common['Accept'] = 'application/json';

// Add CSRF token to every request if it exists
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Add existing auth token if it exists
const authToken = localStorage.getItem('token');
if (authToken) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
}

export default axios;
