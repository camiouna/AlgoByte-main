<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  userId: { type: Number, required: true }
});

const currentTab = ref('solved');
const problems = ref([]);
const meta = ref({
  current_page: 1,
  last_page: 1,
  from: null,
  to: null,
  total: 0,
});
const links = ref({
  prev: null,
  next: null,
});
const loading = ref(false);

// Match your Figma tab styling
const tabClass = (isActive) => [
  'pb-4 text-xs font-black uppercase tracking-[0.15em] transition-all duration-300 border-b-2',
  isActive
    ? 'text-[#38d9ff] border-[#38d9ff] drop-shadow-[0_0_10px_rgba(56,217,255,0.5)]'
    : 'text-gray-500 border-transparent hover:text-gray-300'
];

// Difficulty badges from the Problem Dashboard
const difficultyClass = (difficulty) => {
  const base = 'px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-wider border ';
  if (difficulty === 'Easy') return base + 'bg-emerald-500/5 text-emerald-400 border-emerald-500/20';
  if (difficulty === 'Medium') return base + 'bg-amber-500/5 text-amber-400 border-amber-500/20';
  return base + 'bg-rose-500/5 text-rose-400 border-rose-500/20';
};

const normalizeProblems = (payload) => {
  const items = Array.isArray(payload) ? payload : [];

  return items.map((problem, index) => ({
    id: problem.id ?? problem.problemId ?? `${currentTab.value}-${index + 1}`,
    title: problem.title ?? 'Untitled problem',
    difficulty: problem.difficulty ?? 'Unknown',
    creator: {
      username: problem.creator?.username ?? 'Unknown',
    },
    created_at: problem.created_at ?? '',
  }));
};

const fetchProblems = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/v1/users/${props.userId}/history/${currentTab.value}?page=${page}`);
    problems.value = normalizeProblems(response.data?.data);
    meta.value = {
      current_page: response.data?.meta?.current_page ?? 1,
      last_page: response.data?.meta?.last_page ?? 1,
      from: response.data?.meta?.from ?? null,
      to: response.data?.meta?.to ?? null,
      total: response.data?.meta?.total ?? problems.value.length,
    };
    links.value = {
      prev: response.data?.links?.prev ?? null,
      next: response.data?.links?.next ?? null,
    };
  } catch (error) {
    problems.value = [];
    meta.value = {
      current_page: 1,
      last_page: 1,
      from: null,
      to: null,
      total: 0,
    };
    links.value = {
      prev: null,
      next: null,
    };
    console.error("History fetch failed", error);
  } finally {
    loading.value = false;
  }
};

const goToPreviousPage = () => {
  if (loading.value || !links.value.prev || meta.value.current_page <= 1) {
    return;
  }

  fetchProblems(meta.value.current_page - 1);
};

const goToNextPage = () => {
  if (loading.value || !links.value.next || meta.value.current_page >= meta.value.last_page) {
    return;
  }

  fetchProblems(meta.value.current_page + 1);
};

watch(currentTab, () => fetchProblems(1));
onMounted(() => fetchProblems());
</script>

<template>
    <Head title="Activity History" />

    <AuthenticatedLayout>

        <div class="py-10 px-6">
            <div class="max-w-6xl mx-auto">

                <div class="flex space-x-10 border-b border-[#1a2b3c] mb-10" id="selectOptions" >
                    <button @click="currentTab = 'solved'" :class="tabClass(currentTab === 'solved')">
                        Solved
                    </button>
                    <button @click="currentTab = 'created'" :class="tabClass(currentTab === 'created')">
                        Authored
                    </button>
                    <button @click="currentTab = 'attempted'" :class="tabClass(currentTab === 'attempted')">
                        Attempts
                    </button>
                </div>

                <div class="grid gap-4">
                    <div v-if="loading" class="flex justify-center py-32">
                        <div class="w-8 h-8 border-2 border-[#38d9ff]/20 border-t-[#38d9ff] rounded-full animate-spin"></div>
                    </div>

                    <div v-else-if="problems.length === 0" class="glass-card p-20 text-center border border-[#1a2b3c] rounded-2xl">
                        <span class="text-gray-600 font-black uppercase tracking-widest">No Records Found</span>
                    </div>

                    <div
                        v-for="problem in problems"
                        :key="`${currentTab}-${problem.id}`"
                        class="glass-card group flex items-center justify-between p-6 rounded-xl border border-[#1a2b3c] hover:border-[#38d9ff]/30 transition-all duration-500"
                    >
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center gap-3">
                                <span :class="difficultyClass(problem.difficulty)">{{ problem.difficulty }}</span>
                                <h3 class="text-lg font-extrabold text-white group-hover:text-[#38d9ff] transition-colors">
                                    {{ problem.title }}
                                </h3>
                            </div>
                            <div class="flex items-center gap-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest">
                                <span class="text-gray-400">ID: #{{ problem.id }}</span>
                                <span class="w-1 h-1 bg-gray-700 rounded-full"></span>
                                <span>Author: {{ problem.creator?.username || 'Unknown' }}</span>
                                <span class="w-1 h-1 bg-gray-700 rounded-full"></span>
                                <span>Solved on {{ problem.created_at }}</span>
                            </div>
                        </div>

                        <a
                            :href="route('browse-problems.show', problem.id)"
                            class="solve-btn"
                        >
                            Open Details
                        </a>
                    </div>

                    <div
                        v-if="!loading && problems.length > 0"
                        class="flex items-center justify-between gap-4 border-t border-[#1a2b3c] pt-6 text-xs font-bold uppercase tracking-[0.18em] text-gray-500"
                    >
                        <span>
                            Showing {{ meta.from ?? 1 }}-{{ meta.to ?? problems.length }} of {{ meta.total }}
                        </span>

                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                class="nav-btn"
                                :disabled="loading || !links.prev || meta.current_page <= 1"
                                @click="goToPreviousPage"
                            >
                                Previous
                            </button>
                            <span class="text-[10px] text-gray-400">
                                Page {{ meta.current_page }} / {{ meta.last_page }}
                            </span>
                            <button
                                type="button"
                                class="nav-btn"
                                :disabled="loading || !links.next || meta.current_page >= meta.last_page"
                                @click="goToNextPage"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.glass-card {
    background: rgba(11, 22, 34, 0.6);
    backdrop-filter: blur(12px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
}

.solve-btn {
    padding: 10px 24px;
    background: rgba(56, 217, 255, 0.05);
    border: 1px solid rgba(56, 217, 255, 0.2);
    border-radius: 8px;
    color: #38d9ff;
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.solve-btn:hover {
    background: #38d9ff;
    color: #05080d;
    box-shadow: 0 0 20px rgba(56, 217, 255, 0.4);
    transform: translateY(-2px);
}

.nav-btn {
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #4b5563;
    transition: color 0.2s;
}

.nav-btn:hover:not(:disabled) {
    color: #38d9ff;
}

.nav-btn:disabled {
    opacity: 0.2;
    cursor: not-allowed;
}
#selectOptions button {
    padding: 8px 18px;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: all 0.2s ease;
}
</style>
