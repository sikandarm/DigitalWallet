import Vue from 'vue';
import Vuex from 'vuex';
import auth from './modules/auth';
import transactions from './modules/transactions';
import notifications from './modules/notifications';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        auth,
        transactions,
        notifications,
    },
});

