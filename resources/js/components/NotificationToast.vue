<template>
    <transition name="slide-fade">
        <div
            v-if="show"
            :class="[
                'fixed top-4 right-4 z-50 max-w-sm w-full shadow-lg rounded-lg pointer-events-auto',
                type === 'success' ? 'bg-green-500' : type === 'info' ? 'bg-blue-500' : 'bg-gray-500',
            ]"
        >
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg
                            v-if="type === 'success'"
                            class="h-6 w-6 text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <svg
                            v-else
                            class="h-6 w-6 text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                    <div class="ml-3 w-0 flex-1">
                        <p class="text-sm font-medium text-white">
                            {{ message }}
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button
                            @click="close"
                            class="inline-flex text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    name: 'NotificationToast',
    props: {
        message: {
            type: String,
            required: true,
        },
        type: {
            type: String,
            default: 'success',
            validator: (value) => ['success', 'info', 'error'].includes(value),
        },
        duration: {
            type: Number,
            default: 5000,
        },
    },
    data() {
        return {
            show: false,
            timeout: null,
        };
    },
    mounted() {
        this.show = true;
        if (this.duration > 0) {
            this.timeout = setTimeout(() => {
                this.close();
            }, this.duration);
        }
    },
    beforeDestroy() {
        if (this.timeout) {
            clearTimeout(this.timeout);
        }
    },
    methods: {
        close() {
            this.show = false;
            this.$emit('close');
        },
    },
};
</script>

<style scoped>
.slide-fade-enter-active {
    transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
    transition: all 0.3s ease-in;
}

.slide-fade-enter,
.slide-fade-leave-to {
    transform: translateX(100%);
    opacity: 0;
}
</style>

