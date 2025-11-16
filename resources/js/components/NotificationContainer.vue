<template>
    <div class="notification-container">
        <notification-toast
            v-for="notification in notifications"
            :key="notification.id"
            :message="notification.message"
            :type="notification.type"
            :duration="notification.duration"
            @close="removeNotification(notification.id)"
        />
    </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';
import NotificationToast from './NotificationToast.vue';

export default {
    name: 'NotificationContainer',
    components: {
        NotificationToast,
    },
    computed: {
        ...mapGetters('notifications', ['notifications']),
    },
    methods: {
        ...mapActions('notifications', ['removeNotification']),
    },
};
</script>

<style scoped>
.notification-container {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 9999;
    pointer-events: none;
}

.notification-container > * {
    pointer-events: auto;
    margin-bottom: 0.5rem;
}
</style>

