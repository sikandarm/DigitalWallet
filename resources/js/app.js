import './bootstrap';
import Vue from 'vue';
import store from './store';
import TransactionApp from './components/TransactionApp.vue';
import LoginPage from './components/LoginPage.vue';
import NotificationContainer from './components/NotificationContainer.vue';

store.dispatch('auth/initializeAuth');

const app = new Vue({
    el: '#app',
    store,
    components: {
        TransactionApp,
        LoginPage,
        NotificationContainer,
    },
    computed: {
        isAuthenticated() {
            return this.$store.getters['auth/isAuthenticated'];
        },
        currentUser() {
            return this.$store.getters['auth/currentUser'];
        },
    },
});
