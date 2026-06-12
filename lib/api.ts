import axios from 'axios';

const getBaseUrl = () => {
  if (typeof window !== 'undefined') {
    // If running on default HTTP/HTTPS ports (XAMPP), always prepend /cp
    if (window.location.port === '' || window.location.port === '80' || window.location.port === '443') {
      return window.location.origin + '/cp';
    }
    return window.location.origin;
  }
  return 'http://localhost:3000';
};

const api = axios.create({
  baseURL: getBaseUrl(),
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// Add request interceptor to attach token
api.interceptors.request.use(
  (config) => {
    if (typeof window !== 'undefined') {
      const token = localStorage.getItem('nb_token');
      if (token) {
        config.headers['X-Api-Token'] = token;
      }
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

export default api;
