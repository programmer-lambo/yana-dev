<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Homepage</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">

    <div x-data="homepage()" x-init="init()">

        <x-navbar />

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
                            <span class="text-xs font-medium bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full"
                                x-text="note.category?.name ?? 'Uncategorized'"></span>

                            <h3 class="font-semibold text-gray-800 mt-2 mb-1" x-text="note.title"></h3>

                            <p class="text-gray-500 text-sm line-clamp-3" x-text="note.body"></p>

                            <!-- Metadata -->
                            <div class="mt-4 text-xs text-gray-400 space-y-1">
                                <div>
                                    ✍️ <span x-text="note.author?.name"></span>
                                </div>

                                <div>
                                    📅 <span x-text="note.uploaded_at"></span>
                                </div>

                                <template x-if="note.last_edited_at !== note.uploaded_at">
                                    <div>
                                        ✏️ <span x-text="note.last_edited_at"></span>
                                    </div>
                                </template>
                            </div>

                            <div class="flex justify-end items-center mt-4">
                                <a :href="`/notes/${note.slug}`"
                                    class="text-sm text-blue-500 hover:underline font-medium">
                                    Baca →
                                </a>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="meta && meta.last_page > 1">
                <div class="flex justify-center items-center gap-2 mt-8">

                    <!-- Prev -->
                    <button @click="fetchNotes(meta.current_page - 1)" :disabled="meta.current_page === 1"
                        class="px-4 py-2 rounded-lg border text-sm font-medium transition" :class="meta.current_page === 1
                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                        : 'bg-white hover:bg-blue-50 text-gray-700'">
                        ← Prev
                    </button>

                    <!-- Page Numbers -->
                    <template x-for="page in meta.last_page" :key="page">
                        <button @click="fetchNotes(page)"
                            class="w-9 h-9 rounded-lg border text-sm font-medium transition" :class="page === meta.current_page
                            ? 'bg-blue-600 text-white border-blue-600'
                            : 'bg-white hover:bg-blue-50 text-gray-700'" x-text="page"></button>
                    </template>

                    <!-- Next -->
                    <button @click="fetchNotes(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page"
                        class="px-4 py-2 rounded-lg border text-sm font-medium transition" :class="meta.current_page === meta.last_page
                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                        : 'bg-white hover:bg-blue-50 text-gray-700'">
                        Next →
                    </button>

                </div>
            </template>

            <!-- Info total data -->
            <template x-if="meta">
                <p class="text-center text-sm text-gray-400 mt-3">
                    Halaman <span x-text="meta.current_page"></span> dari <span x-text="meta.last_page"></span>
                    • Total <span x-text="meta.total_data"></span> notes
                </p>
            </template>
            

        </main>

        <x-footer />

    </div>

    <script>
        function homepage() {
            return {
                notes: [],
                meta: null,
                loading: true,
                error: null,
                user: JSON.parse(localStorage.getItem('user') ?? '{"name":""}'),

                async init() {
                    const token = localStorage.getItem('token');
                    if (!token) {
                        window.location.href = '/login';
                        return;
                    }
                    await this.fetchNotes(1);
                },

                async fetchNotes(page = 1) {
                    const token = localStorage.getItem('token');
                    this.loading = true;
                    this.error = null;

                    try {
                        const res = await fetch(`/api/notes?page=${page}`, {
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
                        this.notes = data.data.notes;
                        this.meta = data.data.meta;

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