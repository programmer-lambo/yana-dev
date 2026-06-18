<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Notes Author</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">

<div x-data="authorNotes()" x-init="init()">

    <!-- Navbar -->
    <x-navbar />
    <!-- Main Content -->
    <main class="max-w-5xl mx-auto py-8 px-4">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <a href="/dashboard" class="text-sm text-gray-400 hover:text-gray-600">← Dashboard</a>
                <span class="text-gray-300">|</span>
                <h2 class="text-2xl font-bold" x-text="authorName ? 'Notes oleh ' + authorName : 'Memuat...'"></h2>
            </div>
            <template x-if="meta">
                <span class="text-sm text-gray-400" x-text="meta.total_data + ' notes'"></span>
            </template>
        </div>

        <!-- Loading Skeleton -->
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

        <!-- Error -->
        <template x-if="error">
            <div class="bg-red-50 border border-red-300 text-red-700 rounded px-4 py-3" x-text="error"></div>
        </template>

        <!-- Notes Grid -->
        <template x-if="!loading && !error">
            <div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="note in notes" :key="note.id">
                        <div class="bg-white rounded-xl p-5 shadow hover:shadow-md transition">
                            <span
                                class="text-xs font-medium bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full"
                                x-text="note.category?.name ?? 'Uncategorized'"
                            ></span>
                            <h3 class="font-semibold text-gray-800 mt-2 mb-1" x-text="note.title"></h3>
                            <p class="text-gray-500 text-sm line-clamp-3" x-text="note.body"></p>
                            <div class="flex justify-between items-center mt-4">
                                <span class="text-xs text-gray-400" x-text="note.uploaded_at"></span>
                                <a
                                    :href="`/notes/${note.slug}`"
                                    class="text-sm text-blue-500 hover:underline font-medium"
                                >
                                    Baca →
                                </a>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty state -->
                <template x-if="notes.length === 0">
                    <div class="text-center py-16 text-gray-400">
                        <p class="text-lg">Author ini belum punya notes.</p>
                    </div>
                </template>

                <!-- Pagination -->
                <template x-if="meta && meta.last_page > 1">
                    <div class="flex justify-center items-center gap-2 mt-8">
                        <button
                            @click="fetchNotes(meta.current_page - 1)"
                            :disabled="meta.current_page === 1"
                            class="px-4 py-2 rounded-lg border text-sm font-medium transition"
                            :class="meta.current_page === 1
                                ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                : 'bg-white hover:bg-blue-50 text-gray-700'"
                        >
                            ← Prev
                        </button>

                        <template x-for="page in meta.last_page" :key="page">
                            <button
                                @click="fetchNotes(page)"
                                class="w-9 h-9 rounded-lg border text-sm font-medium transition"
                                :class="page === meta.current_page
                                    ? 'bg-blue-600 text-white border-blue-600'
                                    : 'bg-white hover:bg-blue-50 text-gray-700'"
                                x-text="page"
                            ></button>
                        </template>

                        <button
                            @click="fetchNotes(meta.current_page + 1)"
                            :disabled="meta.current_page === meta.last_page"
                            class="px-4 py-2 rounded-lg border text-sm font-medium transition"
                            :class="meta.current_page === meta.last_page
                                ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                : 'bg-white hover:bg-blue-50 text-gray-700'"
                        >
                            Next →
                        </button>
                    </div>
                </template>

                <!-- Info -->
                <template x-if="meta">
                    <p class="text-center text-sm text-gray-400 mt-3">
                        Halaman <span x-text="meta.current_page"></span> dari <span x-text="meta.last_page"></span>
                        • Total <span x-text="meta.total_data"></span> notes
                    </p>
                </template>

            </div>
        </template>

    </main>
    <x-footer />
</div>

<script>
    function authorNotes() {
        return {
            notes: [],
            meta: null,
            authorName: null,
            authorId: null,
            loading: true,
            error: null,
            user: JSON.parse(localStorage.getItem('user') ?? '{"name":""}'),

            async init() {
                const token = localStorage.getItem('token');
                if (!token) {
                    window.location.href = '/login';
                    return;
                }

                // Ambil author ID dari URL: /authors/1/notes
                const parts = window.location.pathname.split('/');
                this.authorId = parts[2];

                await this.fetchNotes(1);
            },

            async fetchNotes(page = 1) {
                const token = localStorage.getItem('token');
                this.loading = true;
                this.error = null;

                try {
                    const res = await fetch(`/api/authors/${this.authorId}/notes?page=${page}`, {
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

                    // Ambil nama author dari note pertama
                    if (this.notes.length > 0) {
                        this.authorName = this.notes[0].author?.name;
                    }

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