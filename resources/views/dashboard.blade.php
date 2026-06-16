<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">

<div x-data="dashboard()" x-init="init()">

    <nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
        <h1 class="text-xl font-bold text-blue-600">Yana.dev</h1>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-600" x-text="'Halo, ' + user.name"></span>
            <button
                @click="logout"
                class="text-sm bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded-lg transition"
            >
                Logout
            </button>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto py-8 px-4">

        <h2 class="text-2xl font-bold mb-6">Notes</h2>

        <template x-if="loading">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <template x-for="i in 4">
                    <div class="bg-white rounded-xl p-5 shadow animate-pulse">
                        <div class="h-4 bg-gray-200 rounded w-3/4 mb-3"></div>
                        <div class="h-3 bg-gray-200 rounded w-1/2 mb-2"></div>
                        <div class="h-3 bg-gray-200 rounded w-full"></div>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="error">
            <div class="bg-red-50 border border-red-300 text-red-700 rounded px-4 py-3" x-text="error"></div>
        </template>

        <template x-if="!loading && !error">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <template x-for="note in notes" :key="note.id">
                    <div class="bg-white rounded-xl p-5 shadow hover:shadow-md transition">
                        <span
                            class="text-xs font-medium bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full"
                            x-text="note.category?.name ?? 'Uncategorized'"
                        ></span>

                        <h3 class="font-semibold text-gray-800 mt-2 mb-1" x-text="note.title"></h3>

                        <p
                            class="text-gray-500 text-sm line-clamp-3"
                            x-text="note.body"
                        ></p>

                        <div class="flex justify-between items-center mt-4">
                            <span class="text-xs text-gray-400" x-text="note.author?.name"></span>
                            
                            <a  :href="`/notes/${note.slug}`"
                                class="text-sm text-blue-500 hover:underline font-medium"
                            >
                                Baca →
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </template>

    </main>
</div>

<script>
    function dashboard() {
        return {
            notes: [],
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
                    const res = await fetch('/api/notes', {
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

                    if (!res.ok) throw new Error('Gagal memuat notes.');

                    const data = await res.json();
                    this.notes = data.data ?? data;

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