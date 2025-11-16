const state = {
    notifications: [],
};

const mutations = {
    ADD_NOTIFICATION(state, notification) {
        state.notifications.push(notification);
    },
    REMOVE_NOTIFICATION(state, id) {
        const index = state.notifications.findIndex(n => n.id === id);
        if (index > -1) {
            state.notifications.splice(index, 1);
        }
    },
    CLEAR_ALL(state) {
        state.notifications = [];
    },
};

const actions = {
    showNotification({ commit }, { message, type = 'success', duration = 5000 }) {
        const id = Date.now() + Math.random();
        const notification = { id, message, type, duration };
        commit('ADD_NOTIFICATION', notification);

        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('Digital Wallet', {
                body: message,
                icon: '/favicon.ico',
            });
        }
    },
    removeNotification({ commit }, id) {
        commit('REMOVE_NOTIFICATION', id);
    },
    clearAll({ commit }) {
        commit('CLEAR_ALL');
    },
};

const getters = {
    notifications: (state) => state.notifications,
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
};

