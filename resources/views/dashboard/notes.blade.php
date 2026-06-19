<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Notes Saya</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">

<div x-data="myNotes()" x-init="init()">

    <!-- Navbar -->
    <x-navbar />

    <main class="max-w-5xl mx-auto py-8 px-4">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold">Notes Saya</h2>
            <a
                href="/dashboard/notes/create"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition"
            >
                + Buat Note
            </a>
        </div>

        <!-- Toast Notifikasi -->
        <template x-if="toast">
            <div
                class="mb-4 px-4 py-3 rounded-lg text-sm font-medium"
                :class="toast.type === 'success' ? 'bg-green-50 border border-green-300 text-green-700' : 'bg-red-50 border border-red-300 text-red-700'"
                x-text="toast.message"
            ></div>
        </template>

        <!-- Loading Skeleton -->
        <template x-if="loading">
            <div class="space-y-3">
                <template x-for="i in 4">
                    <div class="bg-white rounded-xl p-5 shadow animate-pulse flex justify-between">
                        <div class="flex-1">
                            <div class="h-4 bg-gray-200 rounded w-1/2 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded w-1/4"></div>
                        </div>
                        <div class="flex gap-2">
                            <div class="h-8 w-16 bg-gray-200 rounded-lg"></div>
                            <div class="h-8 w-16 bg-gray-200 rounded-lg"></div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <!-- Error -->
        <template x-if="error">
            <div class="bg-red-50 border border-red-300 text-red-700 rounded px-4 py-3" x-text="error"></div>
        </template>

        <!-- Notes List -->
        <template x-if="!loading && !error">
            <div>
                <!-- Empty state -->
                <template x-if="notes.length === 0">
                    <div class="text-center py-16 text-gray-400">
                        <p class="text-lg mb-3">Kamu belum punya notes.</p>
                        <a href="/dashboard/notes/create" class="text-blue-500 hover:underline text-sm">Buat note pertamamu →</a>
                    </div>
                </template>

                <div class="space-y-3">
                    <template x-for="note in notes" :key="note.id">
                        <div class="bg-white rounded-xl p-5 shadow hover:shadow-md transition flex items-center justify-between gap-4">

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        class="text-xs font-medium bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full"
                                        x-text="note.category?.name ?? 'Uncategorized'"
                                    ></span>
                                    <!-- Badge indexed/draft -->
                                    <span
                                        class="text-xs font-medium px-2 py-0.5 rounded-full"
                                        :class="note.is_indexed ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600'"
                                        x-text="note.is_indexed ? 'Published' : 'Draft'"
                                    ></span>
                                </div>
                                <h3 class="font-semibold text-gray-800 truncate" x-text="note.title"></h3>
                                <p class="text-xs text-gray-400 mt-1" x-text="'Diedit: ' + note.last_edited_at"></p>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 shrink-0">
                                <a
                                    :href="`/notes/${note.slug}`"
                                    class="text-sm px-3 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-gray-600 transition"
                                >
                                    Lihat
                                </a>
                                <a
                                    :href="`/dashboard/notes/${note.slug}/edit`"
                                    class="text-sm px-3 py-1.5 rounded-lg border border-blue-300 hover:bg-blue-50 text-blue-600 transition"
                                >
                                    Edit
                                </a>
                                <button
                                    @click="confirmDelete(note)"
                                    class="text-sm px-3 py-1.5 rounded-lg border border-red-300 hover:bg-red-50 text-red-500 transition"
                                >
                                    Hapus
                                </button>
                            </div>

                        </div>
                    </template>
                </div>

                <!-- Pagination -->
                <template x-if="meta && meta.last_page > 1">
                    <div class="flex justify-center items-center gap-2 mt-8">
                        <button
                            @click="fetchNotes(meta.current_page - 1)"
                            :disabled="meta.current_page === 1"
                            class="px-4 py-2 rounded-lg border text-sm font-medium transition"
                            :class="meta.current_page === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-blue-50 text-gray-700'"
                        >← Prev</button>

                        <template x-for="page in meta.last_page" :key="page">
                            <button
                                @click="fetchNotes(page)"
                                class="w-9 h-9 rounded-lg border text-sm font-medium transition"
                                :class="page === meta.current_page ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-blue-50 text-gray-700'"
                                x-text="page"
                            ></button>
                        </template>

                        <button
                            @click="fetchNotes(meta.current_page + 1)"
                            :disabled="meta.current_page === meta.last_page"
                            class="px-4 py-2 rounded-lg border text-sm font-medium transition"
                            :class="meta.current_page === meta.last_page ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-blue-50 text-gray-700'"
                        >Next →</button>
                    </div>
                </template>

                <template x-if="meta">
                    <p class="text-center text-sm text-gray-400 mt-3">
                        Halaman <span x-text="meta.current_page"></span> dari <span x-text="meta.last_page"></span>
                        • Total <span x-text="meta.total_data"></span> notes
                    </p>
                </template>
            </div>
        </template>

    </main>

    <!-- Modal Konfirmasi Hapus -->
    <template x-if="deleteTarget">
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 shadow-xl max-w-sm w-full mx-4">
                <h3 class="font-bold text-lg mb-2">Hapus Note?</h3>
                <p class="text-gray-500 text-sm mb-6">
                    Note "<span x-text="deleteTarget.title" class="font-medium text-gray-700"></span>" akan dihapus permanen dan tidak bisa dikembalikan.
                </p>
                <div class="flex gap-3 justify-end">
                    <button
                        @click="deleteTarget = null"
                        class="px-4 py-2 rounded-lg border text-sm text-gray-600 hover:bg-gray-50 transition"
                    >
                        Batal
                    </button>
                    <button
                        @click="deleteNote()"
                        :disabled="deleteLoading"
                        class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white text-sm font-semibold transition"
                    >
                        <span x-show="!deleteLoading">Hapus</span>
                        <span x-show="deleteLoading">Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    <x-footer />

</div>

<script>
    function myNotes() {
        return {
            notes: [],
            meta: null,
            loading: true,
            error: null,
            toast: null,
            deleteTarget: null,
            deleteLoading: false,
            user: JSON.parse(localStorage.getItem('user') ?? '{"name":""}'),

            async init() {
                const token = localStorage.getItem('token');
                if (!token) { window.location.href = '/login'; return; }
                await this.fetchNotes(1);
            },

            async fetchNotes(page = 1) {
                const token = localStorage.getItem('token');
                this.loading = true;
                this.error = null;

                try {
                    const res = await fetch(`/api/my/notes?page=${page}`, {
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

            confirmDelete(note) {
                this.deleteTarget = note;
            },

            async deleteNote() {
                const token = localStorage.getItem('token');
                this.deleteLoading = true;

                try {
                    const res = await fetch(`/api/notes/${this.deleteTarget.slug}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + token,
                        }
                    });

                    if (!res.ok) throw new Error('Gagal menghapus note.');

                    this.notes = this.notes.filter(n => n.id !== this.deleteTarget.id);
                    this.meta.total_data--;

                    this.showToast('success', 'Note berhasil dihapus!');

                } catch (err) {
                    this.showToast('error', err.message);
                } finally {
                    this.deleteLoading = false;
                    this.deleteTarget = null;
                }
            },

            showToast(type, message) {
                this.toast = { type, message };
                setTimeout(() => this.toast = null, 3000);
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