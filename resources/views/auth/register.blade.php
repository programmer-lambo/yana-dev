<x-guest-layout>
    <div class="w-full max-w-md bg-white rounded-xl shadow p-8" x-data="registerPage()">

        <h1 class="text-2xl font-bold text-center mb-6">Daftar Akun</h1>

        <template x-if="error">
            <div class="bg-red-50 border border-red-300 text-red-700 rounded px-4 py-3 mb-4 text-sm" x-text="error"></div>
        </template>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <input
                    type="text"
                    x-model="form.name"
                    placeholder="Nama lengkap"
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="errors.name ? 'border-red-400' : 'border-gray-300'"
                />
                <p class="text-red-500 text-xs mt-1" x-text="errors.name" x-show="errors.name"></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                    type="email"
                    x-model="form.email"
                    placeholder="email@example.com"
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="errors.email ? 'border-red-400' : 'border-gray-300'"
                />
                <p class="text-red-500 text-xs mt-1" x-text="errors.email" x-show="errors.email"></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input
                    type="password"
                    x-model="form.password"
                    placeholder="••••••••"
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="errors.password ? 'border-red-400' : 'border-gray-300'"
                />
                <p class="text-red-500 text-xs mt-1" x-text="errors.password" x-show="errors.password"></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                <input
                    type="password"
                    x-model="form.password_confirmation"
                    placeholder="••••••••"
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="errors.password_confirmation ? 'border-red-400' : 'border-gray-300'"
                    @keyup.enter="submit"
                />
                <p class="text-red-500 text-xs mt-1" x-text="errors.password_confirmation" x-show="errors.password_confirmation"></p>
            </div>

            <button
                @click="submit"
                :disabled="loading"
                class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-semibold py-2 rounded-lg transition"
            >
                <span x-show="!loading">Daftar</span>
                <span x-show="loading">Memproses...</span>
            </button>
        </div>

        <p class="text-center text-sm text-gray-500 mt-6">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium">Login di sini</a>
        </p>

        <x-footer />
    </div>

    <script>
        function registerPage() {
            return {
                form: {
                    name: '',
                    email: '',
                    password: '',
                    password_confirmation: ''
                },
                errors: {},
                error: null,
                loading: false,

                async submit() {
                    this.errors = {};
                    this.error = null;
                    this.loading = true;

                    try {
                        const res = await fetch('/api/register', {
                            method: 'POST',
                            credentials: 'include',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(this.form)
                        });

                        const data = await res.json();

                        if (res.status === 422) {
                            this.errors = data.errors ?? {};
                            return;
                        }

                        if (!res.ok) {
                            this.error = data.message ?? 'Registrasi gagal, coba lagi.';
                            return;
                        }

                        window.location.href = '/login';
                    } catch (err) {
                        this.error = 'Terjadi kesalahan, coba lagi.';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</x-guest-layout>