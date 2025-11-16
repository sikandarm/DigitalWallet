const state = {
    balance: 0,
    transactions: [],
    loading: false,
    error: null,
};

const mutations = {
    SET_BALANCE(state, balance) {
        state.balance = parseFloat(balance) || 0;
    },
    SET_TRANSACTIONS(state, transactions) {
        state.transactions = Array.isArray(transactions) ? transactions : [];
    },
    ADD_TRANSACTION(state, transaction) {
        const exists = state.transactions.some(t => t.id === transaction.id);
        if (!exists) {
            state.transactions.unshift(transaction);
        }
    },
    SET_LOADING(state, loading) {
        state.loading = loading;
    },
    SET_ERROR(state, error) {
        state.error = error;
    },
    CLEAR_ERROR(state) {
        state.error = null;
    },
};

const actions = {
    async fetchTransactions({ commit }) {
        commit('SET_LOADING', true);
        commit('CLEAR_ERROR');
        
        try {
            const response = await axios.get('/api/transactions');
            
            if (!response || !response.data) {
                throw new Error('Invalid response from server');
            }
            const responseData = response.data.data || response.data;
            
            const balance = parseFloat(responseData.balance) || 0;
            commit('SET_BALANCE', balance);
            const transactionsData = responseData.transactions;
            const transactions = (transactionsData && transactionsData.data && Array.isArray(transactionsData.data)) 
                ? transactionsData.data 
                : [];
            
            commit('SET_TRANSACTIONS', transactions);
            
            return { success: true, data: responseData };
        } catch (error) {
            const errorMessage = error.response?.data?.message || error.message || 'Failed to load transactions';
            commit('SET_ERROR', errorMessage);
            
            if (error.response?.status === 401) {
                return { success: false, unauthorized: true };
            }
            
            return { success: false, error: errorMessage };
        } finally {
            commit('SET_LOADING', false);
        }
    },

    async sendMoney({ commit, dispatch }, { receiverEmail, amount }) {
        commit('SET_LOADING', true);
        commit('CLEAR_ERROR');
        
        try {
            const receiverResponse = await axios.get(`/api/users/email/${receiverEmail}`);
            const receiverId = receiverResponse.data.id;

            const response = await axios.post('/api/transactions', {
                receiver_id: receiverId,
                amount: amount,
            });

            commit('SET_BALANCE', response.data.new_balance);
            
            const transaction = {
                id: response.data.transaction.id,
                sender_id: response.data.transaction.sender_id,
                receiver_id: response.data.transaction.receiver_id,
                amount: response.data.transaction.amount,
                commission_fee: response.data.transaction.commission_fee,
                total_debited: response.data.transaction.total_debited,
                created_at: response.data.transaction.created_at,
                sender: response.data.transaction.sender,
                receiver: response.data.transaction.receiver,
            };
            
            commit('ADD_TRANSACTION', transaction);
            commit('auth/UPDATE_USER_BALANCE', response.data.new_balance, { root: true });

            return {
                success: true,
                data: response.data,
                message: 'Transfer completed successfully!',
            };
        } catch (error) {
            let errorMessage = 'Transfer failed. Please try again.';
            
            if (error.response?.status === 422) {
                const errors = error.response.data.errors;
                errorMessage = Object.values(errors).flat().join(', ');
            } else if (error.response?.status === 404) {
                errorMessage = 'Receiver not found';
            } else if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }
            
            commit('SET_ERROR', errorMessage);
            
            return { success: false, error: errorMessage };
        } finally {
            commit('SET_LOADING', false);
        }
    },

    handleTransactionUpdate({ commit, state, rootState }, data) {
        if (data && data.transaction && rootState.auth && rootState.auth.user) {
            const currentUserId = rootState.auth.user.id;
            
            if (data.transaction.sender_id === currentUserId) {
                commit('SET_BALANCE', data.sender_balance);
                commit('auth/UPDATE_USER_BALANCE', data.sender_balance, { root: true });
            } else if (data.transaction.receiver_id === currentUserId) {
                commit('SET_BALANCE', data.receiver_balance);
                commit('auth/UPDATE_USER_BALANCE', data.receiver_balance, { root: true });
            }
            
            commit('ADD_TRANSACTION', data.transaction);
        }
    },
};

const getters = {
    balance: (state) => state.balance,
    transactions: (state) => state.transactions,
    loading: (state) => state.loading,
    error: (state) => state.error,
    hasTransactions: (state) => state.transactions.length > 0,
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
};

