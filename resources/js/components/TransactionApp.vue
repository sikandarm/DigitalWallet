<template>
    <div class="transaction-app">
        <div class="container mx-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Digital Wallet</h1>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">Welcome, {{ currentUser?.name || 'User' }}</span>
                    <button
                        @click="logout"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700"
                    >
                        Logout
                    </button>
                </div>
            </div>
            
            <!-- Balance Display -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-2">Current Balance</h2>
                <p class="text-3xl font-bold text-green-600">${{ formatBalance(balance) }}</p>
            </div>

            <!-- Transfer Form -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Send Money</h2>
                <form @submit.prevent="handleSendMoney" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Receiver Email</label>
                        <input
                            v-model="transferForm.receiver_email"
                            type="email"
                            required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter receiver's email"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Amount</label>
                        <input
                            v-model.number="transferForm.amount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="0.00"
                        />
                        <p class="text-sm text-gray-500 mt-1">
                            Commission fee (1.5%): ${{ formatBalance(commissionFee) }}
                        </p>
                        <p class="text-sm font-semibold mt-1">
                            Total to debit: ${{ formatBalance(totalDebit) }}
                        </p>
                    </div>
                    <button
                        type="submit"
                        :disabled="isLoading || !canTransfer"
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ isLoading ? 'Processing...' : 'Send Money' }}
                    </button>
                    <div v-if="transferError" class="text-red-600 text-sm mt-2">
                        {{ transferError }}
                    </div>
                    <div v-if="transferSuccess" class="text-green-600 text-sm mt-2">
                        {{ transferSuccess }}
                    </div>
                    <div v-if="error" class="text-red-600 text-sm mt-2">
                        {{ error }}
                    </div>
                </form>
            </div>

            <!-- Transaction History -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Transaction History</h2>
                <div v-if="isLoading" class="text-center py-8">Loading transactions...</div>
                <div v-else-if="transactions.length === 0" class="text-center py-8 text-gray-500">
                    No transactions yet
                </div>
                <div v-else class="space-y-4">
                    <div
                        v-for="transaction in transactions"
                        :key="transaction.id"
                        class="border-b pb-4 last:border-b-0"
                    >
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold">
                                    <span v-if="transaction.sender_id === currentUserId">
                                        Sent to {{ transaction.receiver.name }}
                                    </span>
                                    <span v-else>
                                        Received from {{ transaction.sender.name }}
                                    </span>
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ formatDate(transaction.created_at) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p
                                    :class="transaction.sender_id === currentUserId ? 'text-red-600' : 'text-green-600'"
                                    class="font-bold text-lg"
                                >
                                    {{ transaction.sender_id === currentUserId ? '-' : '+' }}$
                                    {{ formatBalance(transaction.amount) }}
                                </p>
                                <p v-if="transaction.sender_id === currentUserId" class="text-xs text-gray-500">
                                    Fee: ${{ formatBalance(transaction.commission_fee) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { mapState, mapGetters, mapActions } from 'vuex';

window.Pusher = Pusher;

export default {
    name: 'TransactionApp',
    data() {
        return {
            transferForm: {
                receiver_email: '',
                amount: null,
            },
            transferError: '',
            transferSuccess: '',
            echo: null,
        };
    },
    computed: {
        ...mapState('transactions', ['loading', 'error']),
        ...mapGetters('transactions', ['balance', 'transactions', 'hasTransactions']),
        ...mapGetters('auth', ['currentUser']),
        currentUserId() {
            return this.currentUser?.id || null;
        },
        isLoading() {
            return this.loading;
        },
        commissionFee() {
            if (!this.transferForm.amount || this.transferForm.amount <= 0) {
                return 0;
            }
            return parseFloat((this.transferForm.amount * 0.015).toFixed(2));
        },
        totalDebit() {
            if (!this.transferForm.amount || this.transferForm.amount <= 0) {
                return 0;
            }
            return parseFloat((this.transferForm.amount + this.commissionFee).toFixed(2));
        },
        canTransfer() {
            return this.transferForm.receiver_email && 
                   this.transferForm.amount > 0 && 
                   this.totalDebit <= this.balance;
        },
    },
    watch: {
        currentUser: {
            handler(newUser) {
                if (newUser && newUser.id && !this.echo) {
                    this.$nextTick(() => {
                        this.setupEcho();
                    });
                }
            },
            immediate: true,
        },
    },
    async mounted() {
        if (this.currentUser && this.currentUser.id) {
            await this.fetchTransactions();
            this.requestNotificationPermission();
            this.$nextTick(() => {
                this.setupEcho();
            });
        } else {
            this.$nextTick(async () => {
                await this.fetchTransactions();
                this.requestNotificationPermission();
            });
        }
    },
    methods: {
        ...mapActions('transactions', ['fetchTransactions', 'sendMoney', 'handleTransactionUpdate']),
        ...mapActions('notifications', ['showNotification']),
        async setupEcho() {
            if (this.echo) {
                return;
            }
            
            if (!this.currentUser?.id) {
                setTimeout(() => this.setupEcho(), 500);
                return;
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            const userId = this.currentUser.id;
            const pusherKey = document.querySelector('meta[name="pusher-key"]')?.content || process.env.MIX_PUSHER_APP_KEY || '';
            const pusherCluster = document.querySelector('meta[name="pusher-cluster"]')?.content || process.env.MIX_PUSHER_APP_CLUSTER || 'mt1';

            if (!token || !pusherKey) {
                return;
            }

            const authToken = this.$store.getters['auth/token'] || localStorage.getItem('auth_token');
            
            if (!authToken) {
                return;
            }
            
            if (authToken && !this.$store.getters['auth/token']) {
                this.$store.commit('auth/SET_TOKEN', authToken);
            }
            
            this.echo = new Echo({
                broadcaster: 'pusher',
                key: pusherKey,
                cluster: pusherCluster,
                forceTLS: true,
                authEndpoint: '/api/broadcasting/auth',
                auth: {
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json',
                    },
                },
                enabledTransports: ['ws', 'wss'],
            });

            const channel = this.echo.private(`user.${userId}`);
            
            channel
                .listen('.transaction.completed', (data) => {
                    this.handleRealtimeTransactionUpdate(data);
                })
                .error(() => {});
        },
        async handleRealtimeTransactionUpdate(data) {
            if (!data || !data.transaction) {
                return;
            }

            await this.$store.dispatch('transactions/handleTransactionUpdate', data);
            
            if (data.transaction && this.currentUserId) {
                const isSender = data.transaction.sender_id === this.currentUserId;
                const isReceiver = data.transaction.receiver_id === this.currentUserId;
                
                if (isSender) {
                    this.$store.dispatch('notifications/showNotification', {
                        message: `You sent $${this.formatBalance(data.transaction.amount)} to ${data.transaction.receiver.name}. Your new balance is $${this.formatBalance(data.sender_balance)}.`,
                        type: 'info',
                    });
                } else if (isReceiver) {
                    this.$store.dispatch('notifications/showNotification', {
                        message: `You received $${this.formatBalance(data.transaction.amount)} from ${data.transaction.sender.name}. Your new balance is $${this.formatBalance(data.receiver_balance)}.`,
                        type: 'success',
                    });
                }
            }
        },
        async handleSendMoney() {
            this.transferError = '';
            this.transferSuccess = '';

            const result = await this.sendMoney({
                receiverEmail: this.transferForm.receiver_email,
                amount: this.transferForm.amount,
            });

            if (result.success) {
                this.transferForm.receiver_email = '';
                this.transferForm.amount = null;
                this.transferSuccess = result.message || 'Transfer completed successfully!';
            } else {
                this.transferError = result.error || 'Transfer failed. Please try again.';
            }
        },
        formatBalance(amount) {
            return parseFloat(amount || 0).toFixed(2);
        },
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString();
        },
        async requestNotificationPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                try {
                    await Notification.requestPermission();
                } catch (error) {
                }
            }
        },
        async logout() {
            await this.$store.dispatch('auth/logout');
        },
    },
};
</script>

<style scoped>
.transaction-app {
    min-height: 100vh;
    background-color: #f3f4f6;
}
</style>

