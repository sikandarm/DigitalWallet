const state = {
    user: null,
    token: localStorage.getItem('auth_token') || null,
    isAuthenticated: !!localStorage.getItem('auth_token'),
};

const mutations = {
    SET_USER(state, user) {
        state.user = user;
        state.isAuthenticated = true;
    },
    SET_TOKEN(state, token) {
        state.token = token;
        if (token) {
            localStorage.setItem('auth_token', token);
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        } else {
            localStorage.removeItem('auth_token');
            delete axios.defaults.headers.common['Authorization'];
        }
    },
    LOGOUT(state) {
        state.user = null;
        state.token = null;
        state.isAuthenticated = false;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        delete axios.defaults.headers.common['Authorization'];
    },
    UPDATE_USER_BALANCE(state, balance) {
        if (state.user) {
            state.user = { ...state.user, balance };
            localStorage.setItem('user', JSON.stringify(state.user));
        }
    },
};

const actions = {
    async login({ commit }, credentials) {
        try {
            const response = await axios.post('/api/login', credentials);
            
            commit('SET_TOKEN', response.data.token);
            commit('SET_USER', response.data.user);
            localStorage.setItem('user', JSON.stringify(response.data.user));
            
            return { success: true, data: response.data };
        } catch (error) {
            return {
                success: false,
                error: error.response?.data?.message || 'Login failed. Please check your credentials.',
            };
        }
    },

    async register({ commit }, userData) {
        try {
            const response = await axios.post('/api/register', userData);
            
            commit('SET_TOKEN', response.data.token);
            commit('SET_USER', response.data.user);
            localStorage.setItem('user', JSON.stringify(response.data.user));
            
            return { success: true, data: response.data };
        } catch (error) {
            return {
                success: false,
                error: error.response?.data?.message || 'Registration failed. Please try again.',
                errors: error.response?.data?.errors || {},
            };
        }
    },

    async logout({ commit, state }) {
        try {
            if (state.token) {
                await axios.post('/api/logout');
            }
        } catch (error) {
        } finally {
            commit('LOGOUT');
        }
    },

    initializeAuth({ commit }) {
        const token = localStorage.getItem('auth_token');
        const userStr = localStorage.getItem('user');
        
        if (token && userStr) {
            try {
                const user = JSON.parse(userStr);
                commit('SET_TOKEN', token);
                commit('SET_USER', user);
            } catch (error) {
                commit('LOGOUT');
            }
        }
    },
};

const getters = {
    isAuthenticated: (state) => state.isAuthenticated,
    currentUser: (state) => state.user,
    token: (state) => state.token,
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
};

