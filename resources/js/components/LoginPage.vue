<template>
    <div class="login-page min-h-screen flex items-center justify-center bg-gray-100">
        <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-center mb-8">Digital Wallet</h1>
            
            <div v-if="showRegister" class="space-y-4">
                <h2 class="text-2xl font-semibold text-center mb-6">Register</h2>
                <form @submit.prevent="register" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Name</label>
                        <input
                            v-model="registerForm.name"
                            type="text"
                            required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter your name"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Email</label>
                        <input
                            v-model="registerForm.email"
                            type="email"
                            required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter your email"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Password</label>
                        <input
                            v-model="registerForm.password"
                            type="password"
                            required
                            minlength="8"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter your password"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Confirm Password</label>
                        <input
                            v-model="registerForm.password_confirmation"
                            type="password"
                            required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Confirm your password"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ isLoading ? 'Registering...' : 'Register' }}
                    </button>
                    <div v-if="error" class="text-red-600 text-sm mt-2 text-center">
                        {{ error }}
                    </div>
                    <p class="text-center text-sm mt-4">
                        Already have an account?
                        <a @click="showRegister = false" class="text-blue-600 cursor-pointer hover:underline">
                            Login
                        </a>
                    </p>
                </form>
            </div>

            <div v-else class="space-y-4">
                <h2 class="text-2xl font-semibold text-center mb-6">Login</h2>
                <form @submit.prevent="login" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Email</label>
                        <input
                            v-model="loginForm.email"
                            type="email"
                            required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter your email"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Password</label>
                        <input
                            v-model="loginForm.password"
                            type="password"
                            required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter your password"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ isLoading ? 'Logging in...' : 'Login' }}
                    </button>
                    <div v-if="error" class="text-red-600 text-sm mt-2 text-center">
                        {{ error }}
                    </div>
                    <p class="text-center text-sm mt-4">
                        Don't have an account?
                        <a @click="showRegister = true" class="text-blue-600 cursor-pointer hover:underline">
                            Register
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'LoginPage',
    data() {
        return {
            showRegister: false,
            isLoading: false,
            error: '',
            loginForm: {
                email: '',
                password: '',
            },
            registerForm: {
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
            },
        };
    },
    methods: {
        async login() {
            this.isLoading = true;
            this.error = '';

            const result = await this.$store.dispatch('auth/login', this.loginForm);
            
            if (result.success) {
            } else {
                this.error = result.error || 'Login failed. Please check your credentials.';
            }
            
            this.isLoading = false;
        },
        async register() {
            this.isLoading = true;
            this.error = '';

            if (this.registerForm.password !== this.registerForm.password_confirmation) {
                this.error = 'Passwords do not match.';
                this.isLoading = false;
                return;
            }

            const result = await this.$store.dispatch('auth/register', this.registerForm);
            
            if (result.success) {
            } else {
                if (result.errors) {
                    this.error = Object.values(result.errors).flat().join(', ');
                } else {
                    this.error = result.error || 'Registration failed. Please try again.';
                }
            }
            
            this.isLoading = false;
        },
    },
};
</script>

<style scoped>
.login-page {
    font-family: 'Instrument Sans', sans-serif;
}
</style>

