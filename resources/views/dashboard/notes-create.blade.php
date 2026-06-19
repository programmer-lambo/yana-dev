<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Buat Note</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">

<div x-data="noteForm()" x-init="init()">

    <!-- Navbar -->
    <x-navbar />

    <main class="max-w-3xl mx-auto py-8 px-4">

        <div class="flex items-center gap-3 mb-6">
            <a href="/dashboard/notes" class="text-sm text-gray-400 hover:text-gray-600">← Notes Saya</a>
            <span class="text-gray-300">|</span>
            <h2 class="text-2xl font-bold">Buat Note Baru</h2>
        </div>

        <!-- Error -->
        <template x-if="error">
            <div class="bg-red-50 border border-red-300 text-red-700 rounded px-4 py-3 mb-4 text-sm" x-text="error"></div>
        </template>

        <div class="bg-white rounded-xl p-6 shadow space-y-5">

            <!-- Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                <input
                    type="text"
                    x-model="form.title"
                    placeholder="Judul note..."
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="errors.title ? 'border-red-400' : 'border-gray-300'"
                />
                <p class="text-red-500 text-xs mt-1" x-text="errors.title" x-show="errors.title"></p>
            </div>

            <!-- Category -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select
                    x-model="form.category_id"
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="errors.category_id ? 'border-red-400' : 'border-gray-300'"
                >
                    <option value="">-- Pilih Kategori --</option>
                    <template x-for="cat in categories" :key="cat.id">
                        <option :value="cat.id" x-text="cat.name"></option>
                    </template>
                </select>
                <p class="text-red-500 text-xs mt-1" x-text="errors.category_id" x-show="errors.category_id"></p>
            </div>

            <!-- Body -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konten</label>
                <textarea
                    x-model="form.body"
                    rows="10"
                    placeholder="Tulis isi note di sini..."
                    class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y"
                    :class="errors.body ? 'border-red-400' : 'border-gray-300'"
                ></textarea>
                <p class="text-red-500 text-xs mt-1" x-text="errors.body" x-show="errors.body"></p>
            </div>

            <!-- is_indexed toggle -->
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                    <p class="text-sm font-medium text-gray-700">Publikasikan Note</p>
                    <p class="text-xs text-gray-400">Note akan tampil di halaman publik</p>
                </div>
                <button
                    type="button"
                    @click="form.is_indexed = !form.is_indexed"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition"
                    :class="form.is_indexed ? 'bg-blue-600' : 'bg-gray-300'"
                >
                    <span
                        class="inline-block h-4 w-4 transform rounded-full bg-white transition"
                        :class="form.is_indexed ? 'translate-x-6' : 'translate-x-1'"
                    ></span>
                </button>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-2">
                <a href="/dashboard/notes" class="px-4 py-2 rounded-lg border text-sm text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button
                    @click="submit"
                    :disabled="loading"
                    class="px-6 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-semibold transition"
                >
                    <span x-show="!loading">Simpan Note</span>
                    <span x-show="loading">Menyimpan...</span>
                </button>
            </div>

        </div>
    </main>

    <x-footer />
</div>

<script>
    function noteForm() {
        return {
            form: {
                title: '',
                body: '',
                category_id: '',
                is_indexed: false,
            },
            categories: [],
            errors: {},
            error: null,
            loading: false,
            user: JSON.parse(localStorage.getItem('user') ?? '{"name":""}'),

            async init() {
                const token = localStorage.getItem('token');
                if (!token) { window.location.href = '/login'; return; }
                await this.fetchCategories();
            },

            async fetchCategories() {
                const token = localStorage.getItem('token');
                try {
                    const res = await fetch('/api/categories', {
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + token,
                        }
                    });
                    const data = await res.json();
                    this.categories = data.data;
                } catch (err) {
                    console.error('Gagal fetch kategori:', err);
                }
            },

            async submit() {
                this.errors = {};
                this.error = null;
                this.loading = true;
                const token = localStorage.getItem('token');

                try {
                    const res = await fetch('/api/notes', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + token,
                        },
                        body: JSON.stringify(this.form)
                    });

                    const data = await res.json();

                    if (res.status === 422) {
                        this.errors = data.errors ?? {};
                        return;
                    }

                    if (!res.ok) {
                        this.error = data.message ?? 'Gagal menyimpan note.';
                        return;
                    }

                    // Redirect ke list setelah berhasil
                    window.location.href = '/dashboard/notes';

                } catch (err) {
                    this.error = 'Terjadi kesalahan, coba lagi.';
                } finally {
                    this.loading = false;
                }
            },

            logout() {
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                window.location.href = '/login';
            }
        }
    }
</script>

</body>
</html>