<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Detail Note</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">

    <div x-data="notesShow()" x-init="init()">

        <x-navbar />

        <!-- Main Content -->
        <main class="max-w-3xl mx-auto py-10 px-4">

            <!-- Loading Skeleton -->
            <template x-if="loading">
                <div class="bg-white rounded-xl p-8 shadow animate-pulse">
                    <div class="h-3 bg-gray-200 rounded w-1/4 mb-4"></div>
                    <div class="h-6 bg-gray-200 rounded w-3/4 mb-3"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/3 mb-8"></div>
                    <div class="space-y-2">
                        <div class="h-3 bg-gray-200 rounded w-full"></div>
                        <div class="h-3 bg-gray-200 rounded w-full"></div>
                        <div class="h-3 bg-gray-200 rounded w-5/6"></div>
                    </div>
                </div>
            </template>

            <!-- Error -->
            <template x-if="error">
                <div class="bg-red-50 border border-red-300 text-red-700 rounded px-4 py-3">
                    <p x-text="error"></p>
                    <a href="/home" class="text-sm underline mt-2 inline-block">← Kembali ke Homepage</a>
                </div>
            </template>

            <template x-if="!loading && !error && note">
                <article class="bg-white rounded-xl p-8 shadow">

                    <div class="flex justify-between items-center mb-4">
                        <a :href="`/categories/${note.category?.id}/notes`" class="text-xs font-medium bg-blue-100 text-blue-600 px-3 py-1 rounded-full"
                            x-text="note.category?.name ?? 'Uncategorized'"></a>
                        <a href="/home" class="text-sm text-gray-400 hover:text-gray-600">← Kembali</a>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-800 mb-3" x-text="note.title"></h1>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-500 mb-8 pb-6 border-b">
                        <div class="flex items-center gap-1">
                            <a                        
                                :href="`/authors/${note.author?.id}/notes`"
                                class="hover:text-blue-500 transition"
                            >
                                ✍️ <span x-text="note.author?.name"></span>
                            </a>
                        </div>

                        <div class="flex items-center gap-1">
                            <span>📅</span>
                            <span x-text="note.uploaded_at"></span>
                        </div>

                        <template x-if="note.last_edited_at !== note.uploaded_at">
                            <div class="flex items-center gap-1">
                                <span>✏️</span>
                                <span x-text="note.last_edited_at"></span>
                            </div>
                        </template>
                    </div>
                    <div class="text-gray-700 leading-relaxed whitespace-pre-line" x-text="note.body"></div>

                </article>
            </template>

        </main>
        <x-footer />

    </div>

    <script>
        function notesShow() {
            return {
                note: null,
                loading: true,
                error: null,
                user: JSON.parse(localStorage.getItem('user') ?? '{"name":""}'),

                async init() {
                    const token = localStorage.getItem('token');

                    if (!token) {
                        window.location.href = '/login';
                        return;
                    }

                    // Ambil slug dari URL
                    const slug = window.location.pathname.split('/notes/')[1];

                    try {
                        const res = await fetch(`/api/notes/${slug}`, {
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

                        if (!res.ok) throw new Error('Note tidak ditemukan.');

                        const data = await res.json();
                        this.note = data.data;

                    } catch (err) {
                        this.error = err.message;
                    } finally {
                        this.loading = false;
                    }
                },

                formatDate(dateStr) {
                    return new Date(dateStr).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });
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