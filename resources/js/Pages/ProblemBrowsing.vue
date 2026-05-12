<script setup>
import { computed, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    problems: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            difficulty: 'all',
        }),
    },
});

const difficultyOptions = [
    { value: 'all', label: 'All levels' },
    { value: 'easy', label: 'Easy' },
    { value: 'medium', label: 'Medium' },
    { value: 'hard', label: 'Hard' },
];

const filterForm = reactive({
    search: props.filters.search ?? '',
    difficulty: props.filters.difficulty ?? 'all',
});

const problemItems = computed(() => props.problems.data ?? []);
const paginationLinks = computed(() => props.problems.links ?? []);
const hasActiveFilters = computed(() => filterForm.search.trim() !== '' || filterForm.difficulty !== 'all');
const hasResults = computed(() => problemItems.value.length > 0);

const applyFilters = () => {
    const search = filterForm.search.trim();

    router.get(
        route('browse-problems.index'),
        {
            search: search || undefined,
            difficulty: filterForm.difficulty !== 'all' ? filterForm.difficulty : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const resetFilters = () => {
    filterForm.search = '';
    filterForm.difficulty = 'all';
    applyFilters();
};

const selectDifficulty = (difficulty) => {
    if (filterForm.difficulty === difficulty) {
        return;
    }

    filterForm.difficulty = difficulty;
    applyFilters();
};

const difficultyLabel = (difficulty) => {
    if (!difficulty) {
        return 'Unknown';
    }

    return difficulty.charAt(0).toUpperCase() + difficulty.slice(1).toLowerCase();
};

const difficultyClass = (difficulty) => {
    if (difficulty === 'easy') {
        return 'difficulty-easy';
    }

    if (difficulty === 'hard') {
        return 'difficulty-hard';
    }

    return 'difficulty-medium';
};

const formatDate = (value) => {
    if (!value) {
        return 'Recently added';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return 'Recently added';
    }

    return new Intl.DateTimeFormat(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(date);
};

const descriptionPreview = (description) => {
    const plainText = String(description ?? '')
        .replace(/[`*_>#-]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

    if (!plainText) {
        return 'No description has been added for this problem yet.';
    }

    if (plainText.length <= 180) {
        return plainText;
    }

    return `${plainText.slice(0, 177).trimEnd()}...`;
};

const problemHref = (problem) => route('browse-problems.show', problem.problemId);

const resultSummary = computed(() => {
    if (!props.problems.total) {
        return 'No public problems available right now.';
    }

    const start = props.problems.from ?? 0;
    const end = props.problems.to ?? 0;

    if (hasActiveFilters.value) {
        return `Showing ${start}-${end} of ${props.problems.total} matching problems`;
    }

    return `Showing ${start}-${end} of ${props.problems.total} public problems`;
});

const cleanPaginationLabel = (label) =>
    String(label)
        .replace('&laquo; Previous', 'Previous')
        .replace('Next &raquo;', 'Next');
</script>

<template>
    <Head title="Solve Problems" />

    <AuthenticatedLayout>
        <section class="browse-shell">
            <div class="hero-panel">
                <div class="hero-copy">
                    <p class="eyebrow">Shared problem set</p>
                    <h1>Find the next problem to solve.</h1>
                    <p>
                        Browse public challenges, narrow them by difficulty, and jump into a
                        problem page when you are ready to start.
                    </p>
                </div>

                
            </div>

            <section class="filter-panel">
                <form class="search-row" @submit.prevent="applyFilters">
                    <label class="search-field">
                        <span>Search by title</span>
                        <input
                            v-model="filterForm.search"
                            type="search"
                            placeholder="Binary search, graphs, dynamic programming..."
                        />
                    </label>

                    <div class="search-actions">
                        <button type="submit" class="primary-action">
                            Search
                        </button>
                        <button
                            v-if="hasActiveFilters"
                            type="button"
                            class="secondary-action"
                            @click="resetFilters"
                        >
                            Clear
                        </button>
                    </div>
                </form>

                <div class="difficulty-row">
                    <span class="difficulty-label">Difficulty</span>

                    <div class="difficulty-group">
                        <button
                            v-for="option in difficultyOptions"
                            :key="option.value"
                            type="button"
                            :class="[
                                'difficulty-filter',
                                filterForm.difficulty === option.value ? 'active' : '',
                            ]"
                            @click="selectDifficulty(option.value)"
                        >
                            {{ option.label }}
                        </button>
                    </div>
                </div>
            </section>

            <section class="results-panel">
                <div class="results-header">
                    <div>
                        <p class="results-eyebrow">Browse</p>
                        <h2>Problem catalog</h2>
                    </div>
                    <p class="results-summary">
                        {{ resultSummary }}
                    </p>
                </div>

                <div v-if="hasResults" class="results-grid">
                    <Link
                        v-for="problem in problemItems"
                        :key="problem.problemId"
                        :href="problemHref(problem)"
                        class="problem-card"
                    >
                        <div class="problem-topline">
                            <span :class="['difficulty-pill', difficultyClass(problem.difficulty)]">
                                {{ difficultyLabel(problem.difficulty) }}
                            </span>
                        </div>

                        <div class="problem-body">
                            <h3>{{ problem.title }}</h3>
                            <p>{{ descriptionPreview(problem.description) }}</p>
                        </div>

                        <div class="problem-footer">
                            <div class="meta-row">
                                <span>{{ formatDate(problem.createdAt) }}</span>
                                <span class="open-link">Open problem</span>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-else class="empty-state">
                    <p class="empty-title">No matching problems</p>
                    <p class="empty-copy">
                        Try a different title search or switch to another difficulty level.
                    </p>
                    <button type="button" class="secondary-action" @click="resetFilters">
                        Reset filters
                    </button>
                </div>

                <nav v-if="paginationLinks.length > 3" class="pagination">
                    <Link
                        v-for="link in paginationLinks"
                        :key="`${link.label}-${link.url}`"
                        :href="link.url || '#'"
                        :class="[
                            'pagination-link',
                            link.active ? 'active' : '',
                            !link.url ? 'disabled' : '',
                        ]"
                        preserve-scroll
                    >
                        <span v-html="cleanPaginationLabel(link.label)"></span>
                    </Link>
                </nav>
            </section>
        </section>
    </AuthenticatedLayout>
</template>

<style scoped>
.browse-shell {
    width: min(1220px, calc(100% - 32px));
    margin: 28px auto 40px;
    display: grid;
    gap: 22px;
}

.hero-panel,
.filter-panel,
.results-panel {
    border: 1px solid var(--ab-border);
    border-radius: 8px;
    background: rgba(8, 18, 29, 0.82);
    box-shadow: var(--ab-shadow);
}

.hero-panel {
    display: grid;
    grid-template-columns: minmax(0, 1.3fr) minmax(260px, 0.8fr);
    gap: 24px;
    padding: 28px;
    background:
        radial-gradient(circle at top right, rgba(24, 242, 195, 0.15), transparent 34%),
        linear-gradient(180deg, rgba(11, 22, 34, 0.96), rgba(5, 10, 16, 0.92));
}

.eyebrow,
.results-eyebrow {
    margin: 0 0 12px;
    color: var(--ab-teal);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}

.hero-copy h1,
.results-header h2 {
    margin: 0;
    color: var(--ab-text);
    font-size: clamp(30px, 4vw, 52px);
    font-weight: 850;
    line-height: 1;
}

.results-header h2 {
    font-size: clamp(24px, 3vw, 34px);
}

.hero-copy p:last-child {
    max-width: 680px;
    margin: 18px 0 0;
    color: var(--ab-muted);
    font-size: 16px;
    line-height: 1.8;
}

.hero-stats {
    display: grid;
    gap: 14px;
}

.metric-card {
    padding: 18px 20px;
    border: 1px solid rgba(56, 217, 255, 0.14);
    border-radius: 8px;
    background: rgba(5, 10, 16, 0.78);
}

.metric-card span,
.metric-card small {
    display: block;
}

.metric-card span {
    color: var(--ab-muted);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.metric-card strong {
    display: block;
    margin-top: 10px;
    color: var(--ab-text);
    font-size: 34px;
    font-weight: 850;
    line-height: 1;
}

.metric-card small {
    margin-top: 8px;
    color: var(--ab-cyan);
    font-size: 13px;
    font-weight: 700;
}

.filter-panel {
    display: grid;
    gap: 18px;
    padding: 22px;
    background: rgba(7, 17, 27, 0.94);
}

.search-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 16px;
    align-items: end;
}

.search-field {
    display: grid;
    gap: 10px;
}

.search-field span,
.difficulty-label {
    color: var(--ab-muted);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.search-field input {
    min-height: 50px;
    padding: 0 16px;
    border: 1px solid var(--ab-border);
    border-radius: 8px;
    background: rgba(5, 10, 16, 0.92);
    color: var(--ab-text);
    font-size: 15px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.search-field input::placeholder {
    color: var(--site-placeholder);
}

.search-field input:focus {
    border-color: var(--ab-cyan);
    outline: none;
    box-shadow: 0 0 0 3px rgba(56, 217, 255, 0.16);
    background: rgba(7, 17, 27, 0.98);
}

.search-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.primary-action,
.secondary-action,
.difficulty-filter {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    padding: 0 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none;
    transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease, color 0.2s ease;
}

.primary-action {
    border: 1px solid transparent;
    background: linear-gradient(135deg, var(--ab-cyan), var(--ab-teal));
    color: #031016;
}

.secondary-action,
.difficulty-filter {
    border: 1px solid var(--ab-border);
    background: rgba(11, 22, 34, 0.72);
    color: var(--ab-text);
}

.primary-action:hover,
.secondary-action:hover,
.difficulty-filter:hover,
.problem-card:hover,
.pagination-link:hover {
    transform: translateY(-2px);
}

.secondary-action:hover,
.difficulty-filter:hover,
.pagination-link:hover {
    border-color: var(--ab-border-strong);
    background: rgba(56, 217, 255, 0.1);
}

.difficulty-row {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    align-items: center;
    justify-content: space-between;
}

.difficulty-group {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.difficulty-filter.active {
    border-color: transparent;
    background: linear-gradient(135deg, rgba(56, 217, 255, 0.18), rgba(24, 242, 195, 0.2));
    color: var(--ab-cyan);
    box-shadow: inset 0 0 0 1px rgba(56, 217, 255, 0.22);
}

.results-panel {
    padding: 24px;
    background: rgba(5, 10, 16, 0.92);
}

.results-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 16px;
    align-items: end;
}

.results-summary {
    margin: 0;
    color: var(--ab-muted);
    font-size: 14px;
    font-weight: 700;
}

.results-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
    margin-top: 22px;
}

.problem-card {
    display: grid;
    gap: 18px;
    min-height: 280px;
    padding: 22px;
    border: 1px solid var(--ab-border);
    border-radius: 8px;
    background:
        radial-gradient(circle at top right, rgba(56, 217, 255, 0.12), transparent 34%),
        linear-gradient(180deg, rgba(11, 22, 34, 0.96), rgba(5, 10, 16, 0.96));
    color: inherit;
    text-decoration: none;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}

.problem-card:hover {
    border-color: rgba(56, 217, 255, 0.34);
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.3);
}

.problem-topline,
.meta-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.problem-body {
    display: grid;
    gap: 12px;
}

.problem-body h3 {
    margin: 0;
    color: var(--ab-text);
    font-size: 24px;
    font-weight: 800;
    line-height: 1.15;
}

.problem-body p {
    margin: 0;
    color: var(--ab-muted);
    font-size: 14px;
    line-height: 1.75;
}

.problem-footer {
    margin-top: auto;
    display: grid;
    gap: 10px;
    padding-top: 16px;
    border-top: 1px solid rgba(56, 217, 255, 0.12);
}

.meta-row {
    color: var(--ab-muted);
    font-size: 12px;
    font-weight: 700;
}

.open-link {
    color: var(--ab-cyan);
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.difficulty-pill {
    display: inline-flex;
    align-items: center;
    min-height: 30px;
    padding: 0 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.difficulty-pill {
    border: 1px solid transparent;
}

.difficulty-easy {
    border-color: rgba(84, 214, 146, 0.28);
    background: rgba(84, 214, 146, 0.12);
    color: #68f2ab;
}

.difficulty-medium {
    border-color: rgba(255, 207, 138, 0.28);
    background: rgba(255, 207, 138, 0.12);
    color: #ffd38a;
}

.difficulty-hard {
    border-color: rgba(255, 93, 122, 0.24);
    background: rgba(255, 93, 122, 0.12);
    color: #ff8aa0;
}

.empty-state {
    margin-top: 22px;
    padding: 42px 24px;
    border: 1px dashed rgba(56, 217, 255, 0.22);
    border-radius: 8px;
    background: rgba(7, 17, 27, 0.86);
    text-align: center;
}

.empty-title {
    margin: 0;
    color: var(--ab-text);
    font-size: 22px;
    font-weight: 800;
}

.empty-copy {
    max-width: 480px;
    margin: 12px auto 20px;
    color: var(--ab-muted);
    font-size: 15px;
    line-height: 1.7;
}

.pagination {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 24px;
}

.pagination-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
    min-height: 44px;
    padding: 0 16px;
    border: 1px solid var(--ab-border);
    border-radius: 8px;
    background: rgba(11, 22, 34, 0.72);
    color: var(--ab-text);
    font-size: 13px;
    font-weight: 800;
    text-decoration: none;
    transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
}

.pagination-link.active {
    border-color: transparent;
    background: linear-gradient(135deg, var(--ab-cyan), var(--ab-teal));
    color: #031016;
}

.pagination-link.disabled {
    opacity: 0.45;
    pointer-events: none;
}

@media (max-width: 1060px) {
    .results-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 860px) {
    .browse-shell {
        width: min(100%, calc(100% - 24px));
    }

    .hero-panel,
    .search-row {
        grid-template-columns: 1fr;
    }

    .search-actions {
        justify-content: flex-start;
    }
}

@media (max-width: 680px) {
    .browse-shell {
        margin-top: 18px;
        margin-bottom: 28px;
    }

    .hero-panel,
    .filter-panel,
    .results-panel {
        padding: 18px;
    }

    .results-grid {
        grid-template-columns: 1fr;
    }

    .difficulty-row,
    .results-header {
        align-items: flex-start;
    }

    .problem-card {
        min-height: 0;
    }
}
</style>
