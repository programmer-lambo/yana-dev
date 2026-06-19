<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Kategori</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">

<div x-data="categoriesPage()" x-init="init()">

    <x-navbar />
    <!-- Main Content -->
    <main class="max-w-5xl mx-auto py-8 px-4">

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold">Kategori</h2>
            <a href="/home" class="text-sm text-gray-400 hover:text-gray-600">← Homepage</a>
        </div>

        <!-- Loading Skeleton -->
        <template x-if="loading">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <template x-for="i in 6">
                    <div class="bg-white rounded-xl p-6 shadow animate-pulse">
                        <div class="h-4 bg-gray-200 rounded w-1/2 mb-3"></div>
                        <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                    </div>
                </template>
            </div>
        </template>

        <!-- Error -->
        <template x-if="error">
            <div class="bg-red-50 border border-red-300 text-red-700 rounded px-4 py-3" x-text="error"></div>
        </template>

        <!-- Categories Grid -->
        <template x-if="!loading && !error">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <template x-for="category in categories" :key="category.id">
                    <a
                        :href="`/categories/${category.id}/notes`"
                        class="bg-white rounded-xl p-6 shadow hover:shadow-md hover:border-blue-300 border border-transparent transition group"
                    >
                        <!-- Icon -->
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-4 group-hover:bg-blue-200 transition">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>

                        <!-- Name -->
                        <h3 class="font-semibold text-gray-800 group-hover:text-blue-600 transition" x-text="category.name"></h3>

                        <!-- Notes count -->
                        <p class="text-sm text-gray-400 mt-1">
                            <span x-text="category.notes_count"></span> notes
                        </p>
                    </a>
                </template>
            </div>
        </template>

        <!-- Empty state -->
        <template x-if="!loading && !error && categories.length === 0">
            <div class="text-center py-16 text-gray-400">
                <p class="text-lg">Belum ada kategori.</p>
            </div>
        </template>

    </main>

    <x-footer />

</div>

<script>
    function categoriesPage() {
        return {
            categories: [],
            loading: true,
            error: null,
            user: JSON.parse(localStorage.getItem('user') ?? '{"name":""}'),

            async init() {
                const token = localStorage.getItem('token');
                if (!token) {
                    window.location.href = '/login';
                    return;
                }

                try {
                    const res = await fetch('/api/categories', {
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + token,
                        }
                    });

                    if (res.status === 401) {
                        localStorage.removeItem('token');
                        localStorage.removeItem('user');
                        window.location.href = '/login';
                        return;
                    }

                    if (!res.ok) throw new Error('Gagal memuat kategori.');

                    const data = await res.json();
                    this.categories = data.data;

                } catch (err) {
                    this.error = err.message;
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