<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { VueMonacoEditor } from '@guolao/vue-monaco-editor';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    problem: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const currentUsername = computed(() => page.props.auth?.member?.username?.toLowerCase?.() ?? '');

const tabs = [
    { value: 'details', label: 'Problem Details' },
    { value: 'solutions', label: 'Shared Solutions' },
    { value: 'submissions', label: 'Your Submissions' },
];

const languages = [
    { value: 'typescript', label: 'TypeScript' },
    { value: 'javascript', label: 'JavaScript' },
    { value: 'python', label: 'Python' },
    { value: 'java', label: 'Java' },
    { value: 'c', label: 'C' },
];

const activeTab = ref('details');
const fontSize = ref(Number(localStorage.getItem('editorFontSize')) || 14);
const appMode = ref(localStorage.getItem('authBackgroundMode') || document.documentElement.dataset.authBg || 'dark');
const customInput = ref('');
const customError = ref('');
const customCases = ref([]);
const code = ref('');
const isValidating = ref(false);
const validationMessage = ref('');
const validationError = ref('');
const validationStatus = ref('');
const validationSummary = ref(null);
const validationResults = ref([]);
let modeObserver;

const normalizeText = (value) => String(value ?? '').replace(/\r\n/g, '\n').trim();

const normalizeCase = (testCase, index) => ({
    key: testCase.testCaseId ?? testCase.id ?? `case-${index + 1}`,
    label: testCase.label ?? `Example ${index + 1}`,
    input: String(testCase.input ?? '').trim(),
    expectedOutput: normalizeText(
        testCase.expectedOutput ??
        testCase.expected_output ??
        testCase.output ??
        '',
    ),
    isCustom: false,
});

const normalizeSubmission = (submission, index) => ({
    key: submission.submissionId ?? submission.id ?? `submission-${index + 1}`,
    username: submission.username ?? '',
    language: submission.language ?? '',
    code: submission.code ?? '',
    status: submission.status ?? 'Unknown',
    createdAt: submission.createdAt ?? submission.created_at ?? '',
    stdout: submission.stdout ?? '',
    stderr: submission.stderr ?? '',
    compileOutput: submission.compileOutput ?? submission.compile_output ?? '',
});

const normalizeSharedSolution = (solution, index) => ({
    key: solution.solutionId ?? solution.id ?? `solution-${index + 1}`,
    title: solution.title ?? `Shared Solution ${index + 1}`,
    username: solution.username ?? '',
    language: solution.language ?? '',
    code: solution.code ?? '',
    explanation: solution.explanation ?? '',
    createdAt: solution.createdAt ?? solution.created_at ?? '',
});

const savedCases = computed(() => (props.problem.testCases ?? []).map(normalizeCase));
const submissions = computed(() => (props.problem.submissions ?? []).map(normalizeSubmission));
const sharedSolutions = computed(() => (props.problem.sharedSolutions ?? []).map(normalizeSharedSolution));

const ownSubmissions = computed(() => {
    if (!currentUsername.value) {
        return submissions.value;
    }

    const filtered = submissions.value.filter(
        (submission) => submission.username?.toLowerCase?.() === currentUsername.value,
    );

    return filtered.length ? filtered : submissions.value;
});

const acceptedReference = computed(() =>
    submissions.value.find(
        (submission) =>
            submission.code.trim() !== '' &&
            String(submission.status).toLowerCase() === 'accepted',
    ) ?? submissions.value.find((submission) => submission.code.trim() !== '') ?? null,
);

const initialLanguage =
    props.problem.language ||
    acceptedReference.value?.language ||
    sharedSolutions.value[0]?.language ||
    'typescript';

const selectedLanguage = ref(initialLanguage);

const languageLabel = computed(() =>
    languages.find((language) => language.value === selectedLanguage.value)?.label ?? selectedLanguage.value,
);

const monacoTheme = computed(() => (appMode.value === 'light' ? 'eclipse' : 'vs-dark'));
const referenceAvailable = computed(() => Boolean(acceptedReference.value?.code?.trim()));

const editorOptions = computed(() => ({
    fontSize: fontSize.value,
    automaticLayout: true,
    minimap: { enabled: false },
    scrollBeyondLastLine: false,
    wordWrap: 'on',
    padding: { top: 16, bottom: 16 },
}));

const allCases = computed(() => [...savedCases.value, ...customCases.value]);

const starterCode = (language) => {
    switch (language) {
        case 'python':
            return `def solution(input):\n    # write your implementation here\n    return input`;
        case 'java':
            return `public Object solution(String input) {\n  // write your implementation here\n  return input;\n}`;
        case 'c':
            return `char* solution(char* input) {\n  // write your implementation here\n  return input;\n}`;
        case 'javascript':
            return `function solution(input) {\n  // write your implementation here\n  return input;\n}`;
        default:
            return `function solution(input) {\n  // write your implementation here\n  return input\n}`;
    }
};

if (!code.value) {
    code.value = starterCode(selectedLanguage.value);
}

const syncAppMode = () => {
    appMode.value = document.documentElement.dataset.authBg || localStorage.getItem('authBackgroundMode') || 'dark';
};

const eclipseTheme = {
    base: 'vs',
    inherit: true,
    rules: [
        { token: 'keyword', foreground: '7F0055', fontStyle: 'bold' },
        { token: 'comment', foreground: '3F7F5F' },
        { token: 'string', foreground: '2A00FF' },
        { token: 'number', foreground: '000000' },
    ],
    colors: {
        'editor.background': '#f2f6f8',
        'editor.foreground': '#17303c',
        'editor.lineHighlightBackground': '#e4edf1',
        'editor.selectionBackground': '#c8e4ee',
    },
};

const handleBeforeMount = (monaco) => {
    monaco.editor.defineTheme('eclipse', eclipseTheme);
};

onMounted(() => {
    syncAppMode();
    modeObserver = new MutationObserver(syncAppMode);
    modeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-auth-bg'],
    });
});

onBeforeUnmount(() => {
    modeObserver?.disconnect();
});

const changeLanguage = () => {
    code.value = starterCode(selectedLanguage.value);
};

const increaseFontSize = () => {
    if (fontSize.value < 36) {
        fontSize.value += 2;
        localStorage.setItem('editorFontSize', String(fontSize.value));
    }
};

const decreaseFontSize = () => {
    if (fontSize.value > 10) {
        fontSize.value -= 2;
        localStorage.setItem('editorFontSize', String(fontSize.value));
    }
};

const resetEditor = () => {
    code.value = starterCode(selectedLanguage.value);
};

const addCustomCase = () => {
    if (!customInput.value.trim()) {
        customError.value = 'Enter a raw argument list such as `5`, `"text"`, or `1, 2`.';
        return;
    }

    customCases.value.push({
        key: `custom-${Date.now()}-${customCases.value.length + 1}`,
        label: `Custom ${customCases.value.length + 1}`,
        input: customInput.value.trim(),
        expectedOutput: '',
        isCustom: true,
    });

    customInput.value = '';
    customError.value = '';
};

const removeCustomCase = (caseKey) => {
    customCases.value = customCases.value.filter((testCase) => testCase.key !== caseKey);
};

const parseCReturnType = (source) => {
    const match = source.match(/([A-Za-z_][\w\s*]*?)\s+solution\s*\(/s);
    return match?.[1]?.trim() ?? '';
};

const cPrintLine = (returnType, invocation) => {
    const normalized = String(returnType ?? '').replace(/\s+/g, ' ').trim().toLowerCase();

    if (!normalized || normalized === 'void') return `  ${invocation};`;
    if (normalized.includes('char') && normalized.includes('*')) return `  printf("%s", ${invocation});`;
    if (normalized.includes('float') || normalized.includes('double')) return `  printf("%g", (double) ${invocation});`;
    if (normalized === 'char') return `  printf("%c", ${invocation});`;
    if (normalized.includes('*')) return `  printf("%p", (void*) ${invocation});`;

    return `  printf("%lld", (long long) ${invocation});`;
};

const indent = (source, spaces) =>
    source
        .split('\n')
        .map((line) => `${' '.repeat(spaces)}${line}`)
        .join('\n');

const buildExecutionSource = (language, sourceCode, input) => {
    const call = `solution(${input})`;

    switch (language) {
        case 'python':
            return `import json

${sourceCode}

__algo_result = ${call}
if isinstance(__algo_result, str):
    print(__algo_result)
elif __algo_result is None:
    print("None")
else:
    print(json.dumps(__algo_result))`;
        case 'java':
            return `public class Main {
${indent(sourceCode, 2)}

  public static void main(String[] args) {
    Main obj = new Main();
    Object result = obj.${call};
    System.out.println(String.valueOf(result));
  }
}`;
        case 'c':
            return `#include <stdio.h>
#include <stdbool.h>

${sourceCode}

int main() {
${cPrintLine(parseCReturnType(sourceCode), call)}
  return 0;
}`;
        default:
            return `${sourceCode}

const result = ${call};

if (typeof result === 'string') {
  console.log(result);
} else if (typeof result === 'undefined') {
  console.log('undefined');
} else {
  console.log(JSON.stringify(result));
}`;
    }
};

const runCode = async (language, sourceCode, input) => {
    const response = await axios.post(
        route('execute'),
        {
            language,
            code: buildExecutionSource(language, sourceCode, input),
        },
        {
            headers: {
                Accept: 'application/json',
            },
        },
    );

    return response.data.data;
};

const executeOutput = (result) =>
    normalizeText(result.stdout ?? result.stderr ?? result.compile_output ?? 'No output returned.');

const validateSolution = async () => {
    if (!code.value.trim()) {
        validationError.value = 'Write a solution before validating.';
        validationMessage.value = '';
        validationStatus.value = '';
        validationSummary.value = null;
        validationResults.value = [];
        return;
    }

    if (!allCases.value.length) {
        validationError.value = 'No testcases are available for this problem.';
        validationMessage.value = '';
        validationStatus.value = '';
        validationSummary.value = null;
        validationResults.value = [];
        return;
    }

    validationError.value = '';
    validationMessage.value = '';
    validationStatus.value = '';
    validationSummary.value = null;
    validationResults.value = [];
    isValidating.value = true;

    const results = [];
    let passed = 0;
    let status = 'Accepted';

    try {
        for (const testCase of allCases.value) {
            let expectedOutput = testCase.expectedOutput;

            if (testCase.isCustom) {
                if (!referenceAvailable.value) {
                    status = 'Reference Error';
                    results.push({
                        key: testCase.key,
                        label: testCase.label,
                        input: testCase.input || 'solution()',
                        expectedOutput: 'Unavailable',
                        actualOutput: 'Reference solution unavailable.',
                        status: 'Reference Error',
                        message: 'Custom cases need an accepted reference submission in the current payload.',
                    });
                    continue;
                }

                const referenceResult = await runCode(
                    acceptedReference.value.language || selectedLanguage.value,
                    acceptedReference.value.code,
                    testCase.input,
                );

                if (referenceResult.status !== 'Completed') {
                    status = 'Reference Error';
                    results.push({
                        key: testCase.key,
                        label: testCase.label,
                        input: testCase.input || 'solution()',
                        expectedOutput: 'Unavailable',
                        actualOutput: executeOutput(referenceResult),
                        status: 'Reference Error',
                        message: 'The reference solution failed on this custom testcase.',
                    });
                    continue;
                }

                expectedOutput = normalizeText(referenceResult.stdout);
            }

            const userResult = await runCode(selectedLanguage.value, code.value, testCase.input);

            if (userResult.status !== 'Completed') {
                if (status === 'Accepted') {
                    status = 'Failed';
                }

                results.push({
                    key: testCase.key,
                    label: testCase.label,
                    input: testCase.input || 'solution()',
                    expectedOutput: expectedOutput || 'Unavailable',
                    actualOutput: executeOutput(userResult),
                    status: 'Failed',
                    message: 'Your code failed to run for this testcase.',
                });
                continue;
            }

            const actualOutput = normalizeText(userResult.stdout);

            if (actualOutput === expectedOutput) {
                passed += 1;
                results.push({
                    key: testCase.key,
                    label: testCase.label,
                    input: testCase.input || 'solution()',
                    expectedOutput,
                    actualOutput,
                    status: 'Accepted',
                    message: testCase.isCustom
                        ? 'Matched the reference solution output.'
                        : 'Matched the saved expected output.',
                });
                continue;
            }

            if (status === 'Accepted') {
                status = 'Wrong Answer';
            }

            results.push({
                key: testCase.key,
                label: testCase.label,
                input: testCase.input || 'solution()',
                expectedOutput: expectedOutput || 'Unavailable',
                actualOutput,
                status: 'Wrong Answer',
                message: 'Your result did not match the expected output.',
            });
        }

        validationResults.value = results;
        validationSummary.value = { passed, total: results.length };
        validationStatus.value = status;
        validationMessage.value = status === 'Accepted'? 'All testcases passed.': 'Validation completed with issues.';
        if (status=='Accepted') {
            
        }

    } catch (error) {
        validationError.value = error.response?.data?.message ?? error.message ?? 'Unable to validate right now.';
    } finally {
        isValidating.value = false;
    }
};

const difficultyLabel = (difficulty) =>
    difficulty ? difficulty.charAt(0).toUpperCase() + difficulty.slice(1).toLowerCase() : 'Unknown';

const difficultyClass = (difficulty) => {
    if (difficulty === 'easy') return 'difficulty-easy';
    if (difficulty === 'hard') return 'difficulty-hard';
    return 'difficulty-medium';
};

const statusClass = (status) => {
    if (status === 'Accepted') return 'status-accepted';
    if (status === 'Wrong Answer') return 'status-wrong';
    if (status === 'Failed' || status === 'Reference Error') return 'status-failed';
    return 'status-neutral';
};
</script>

<template>
    <Head :title="problem.title || 'Problem Detail'" />

    <AuthenticatedLayout>
        <section class="detail-shell">
            <div class="workspace">
                <aside class="left-panel">
                    <div class="left-header">
                        <div class="header-pills">
                            <span :class="['difficulty-pill', difficultyClass(problem.difficulty)]">
                                {{ difficultyLabel(problem.difficulty) }}
                            </span>
                            
                        </div>

                        <h1>{{ problem.title }}</h1>
                        <p class="meta-copy">
                            {{ problem.creatorName || 'Unknown author' }}
                            <span class="meta-dot">•</span>
                            {{ problem.createdAt || 'Recently added' }}
                        </p>
                    </div>

                    <nav class="tab-bar">
                        <button
                            v-for="tab in tabs"
                            :key="tab.value"
                            type="button"
                            :class="['tab-button', activeTab === tab.value ? 'active' : '']"
                            @click="activeTab = tab.value"
                        >
                            {{ tab.label }}
                        </button>
                    </nav>

                    <div class="tab-content">
                        <div v-if="activeTab === 'details'" class="stack">
                            <section class="card">
                                <span class="eyebrow">Prompt</span>
                                <p class="copy">{{ problem.description || 'No problem statement available.' }}</p>
                            </section>

                            <section class="card">
                                <span class="eyebrow">Examples</span>
                                <div class="stack compact">
                                    <article
                                        v-for="testCase in savedCases"
                                        :key="testCase.key"
                                        class="subcard"
                                    >
                                        <strong>{{ testCase.label }}</strong>
                                        <div class="case-grid">
                                            <div>
                                                <span>Input</span>
                                                <pre>{{ testCase.input || 'solution()' }}</pre>
                                            </div>
                                            <div>
                                                <span>Expected Output</span>
                                                <pre>{{ testCase.expectedOutput || 'Unavailable' }}</pre>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            </section>
                        </div>

                        <div v-else-if="activeTab === 'solutions'" class="stack">
                            <article
                                v-for="solution in sharedSolutions"
                                :key="solution.key"
                                class="card"
                            >
                                <strong>{{ solution.title }}</strong>
                                <p class="meta-copy">
                                    {{ solution.username || 'Unknown author' }}
                                    <span class="meta-dot">•</span>
                                    {{ solution.language || 'Unknown language' }}
                                    <span class="meta-dot">•</span>
                                    {{ solution.createdAt || 'Recently shared' }}
                                </p>
                                <p v-if="solution.explanation" class="copy">{{ solution.explanation }}</p>
                                <pre class="code-block"><code>{{ solution.code }}</code></pre>
                            </article>

                            <div v-if="sharedSolutions.length === 0" class="card empty">
                                No shared solutions available.
                            </div>
                        </div>

                        <div v-else class="stack">
                            <article
                                v-for="submission in ownSubmissions"
                                :key="submission.key"
                                class="card"
                            >
                                <div class="submission-head">
                                    <div>
                                        <strong>{{ submission.language || 'Unknown language' }}</strong>
                                        <p class="meta-copy">
                                            {{ submission.createdAt || 'Recently submitted' }}
                                        </p>
                                    </div>
                                    <span :class="['status-pill', statusClass(submission.status)]">
                                        {{ submission.status }}
                                    </span>
                                </div>

                                <div v-if="submission.stdout || submission.stderr || submission.compileOutput" class="stack compact">
                                    <div v-if="submission.stdout" class="output-block">
                                        <span>Standard Output</span>
                                        <pre>{{ submission.stdout }}</pre>
                                    </div>
                                    <div v-if="submission.stderr" class="output-block">
                                        <span>Standard Error</span>
                                        <pre>{{ submission.stderr }}</pre>
                                    </div>
                                    <div v-if="submission.compileOutput" class="output-block">
                                        <span>Compiler Output</span>
                                        <pre>{{ submission.compileOutput }}</pre>
                                    </div>
                                </div>

                                <pre class="code-block"><code>{{ submission.code }}</code></pre>
                            </article>

                            <div v-if="ownSubmissions.length === 0" class="card empty">
                                No submissions available.
                            </div>
                        </div>
                    </div>
                </aside>

                <section class="right-panel">
                    <div class="toolbar">
                        <div class="toolbar-copy">
                            <span class="eyebrow">Workspace</span>
                            <strong>{{ languageLabel }}</strong>
                            <p class="copy small">
                                Saved cases use stored expected outputs. Custom cases use the accepted reference submission first.
                            </p>
                        </div>

                        <div class="toolbar-actions">
                            <div class="font-controls">
                                <button type="button" class="tool-button" @click="decreaseFontSize">-</button>
                                <span>{{ fontSize }}px</span>
                                <button type="button" class="tool-button" @click="increaseFontSize">+</button>
                            </div>
                            <select v-model="selectedLanguage" class="language-select" @change="changeLanguage">
                                <option
                                    v-for="language in languages"
                                    :key="language.value"
                                    :value="language.value"
                                >
                                    {{ language.label }}
                                </option>
                            </select>
                            <button type="button" class="tool-button secondary" @click="resetEditor">
                                Reset
                            </button>
                            <button type="button" class="tool-button primary" :disabled="isValidating" @click="validateSolution">
                                {{ isValidating ? 'Validating...' : 'Validate' }}
                            </button>
                        </div>
                    </div>

                    <div class="editor-wrapper">
                        <VueMonacoEditor
                            v-model:value="code"
                            :language="selectedLanguage"
                            :theme="monacoTheme"
                            :options="editorOptions"
                            @before-mount="handleBeforeMount"
                        />
                    </div>

                    <section class="cases">
                        <div class="case-section">
                            <div class="section-head">
                                <div>
                                    <span class="eyebrow">Saved Cases</span>
                                    <h2>Problem Testcases</h2>
                                </div>
                                <p class="meta-copy">{{ savedCases.length }} total</p>
                            </div>

                            <div class="stack compact">
                                <article
                                    v-for="testCase in savedCases"
                                    :key="testCase.key"
                                    class="subcard"
                                >
                                    <div class="case-head">
                                        <strong>{{ testCase.label }}</strong>
                                        <span class="info-pill">Saved</span>
                                    </div>
                                    <div class="case-grid">
                                        <div>
                                            <span>Input</span>
                                            <pre>{{ testCase.input || 'solution()' }}</pre>
                                        </div>
                                        <div>
                                            <span>Expected Output</span>
                                            <pre>{{ testCase.expectedOutput || 'Unavailable' }}</pre>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </div>

                        <div class="case-section">
                            <div class="section-head">
                                <div>
                                    <span class="eyebrow">Custom Cases</span>
                                    <h2>Add Your Own</h2>
                                </div>
                                <p class="meta-copy">
                                    <span v-if="referenceAvailable">Reference comparison available.</span>
                                    <span v-else>Reference comparison unavailable.</span>
                                </p>
                            </div>

                            <div class="custom-row">
                                <input
                                    v-model="customInput"
                                    type="text"
                                    class="custom-input"
                                    placeholder='Arguments, for example: 5, [1, 2, 3], "text"'
                                />
                                <button type="button" class="tool-button primary" @click="addCustomCase">
                                    Add
                                </button>
                            </div>

                            <p v-if="customError" class="error-copy">{{ customError }}</p>

                            <div v-if="customCases.length" class="stack compact">
                                <article
                                    v-for="testCase in customCases"
                                    :key="testCase.key"
                                    class="subcard"
                                >
                                    <div class="case-head">
                                        <strong>{{ testCase.label }}</strong>
                                        <button type="button" class="remove-button" @click="removeCustomCase(testCase.key)">
                                            Remove
                                        </button>
                                    </div>
                                    <div class="case-grid single">
                                        <div>
                                            <span>Input</span>
                                            <pre>{{ testCase.input || 'solution()' }}</pre>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="validationMessage || validationError || validationResults.length"
                        :class="['results', validationStatus === 'Accepted' ? 'results-success' : 'results-error']"
                    >
                        <p v-if="validationError" class="copy">{{ validationError }}</p>

                        <template v-else>
                            <div class="section-head">
                                <div>
                                    <span class="eyebrow">Validation</span>
                                    <h2>{{ validationMessage }}</h2>
                                </div>
                                <p v-if="validationSummary" class="meta-copy">
                                    {{ validationSummary.passed }}/{{ validationSummary.total }} passed
                                    <span class="meta-dot">•</span>
                                    {{ validationStatus }}
                                </p>
                            </div>

                            <div class="stack compact">
                                <article
                                    v-for="result in validationResults"
                                    :key="result.key"
                                    class="subcard"
                                >
                                    <div class="case-head">
                                        <strong>{{ result.label }}</strong>
                                        <span :class="['status-pill', statusClass(result.status)]">
                                            {{ result.status }}
                                        </span>
                                    </div>
                                    <div class="case-grid">
                                        <div>
                                            <span>Input</span>
                                            <pre>{{ result.input }}</pre>
                                        </div>
                                        <div>
                                            <span>Expected Output</span>
                                            <pre>{{ result.expectedOutput }}</pre>
                                        </div>
                                    </div>
                                    <div class="case-grid single">
                                        <div>
                                            <span>Actual Output</span>
                                            <pre>{{ result.actualOutput }}</pre>
                                        </div>
                                    </div>
                                    <p class="copy small">{{ result.message }}</p>
                                </article>
                            </div>
                        </template>
                    </section>
                </section>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<style scoped>
.detail-shell {
    width: min(1540px, calc(100% - 28px));
    margin: 18px auto 30px;
}

.workspace {
    display: grid;
    grid-template-columns: minmax(340px, 0.9fr) minmax(0, 1.1fr);
    gap: 18px;
    align-items: start;
}

.left-panel,
.right-panel {
    overflow: hidden;
    border: 1px solid var(--ab-border);
    border-radius: 8px;
    background: rgba(7, 17, 27, 0.94);
    box-shadow: var(--ab-shadow);
}

.left-panel {
    position: sticky;
    top: 94px;
    display: grid;
    grid-template-rows: auto auto minmax(0, 1fr);
    min-height: calc(100vh - 128px);
}

.right-panel {
    display: grid;
    grid-template-rows: auto minmax(460px, 56vh) auto auto;
}

.left-header,
.toolbar,
.case-section,
.results {
    padding: 18px 20px;
}

.left-header,
.toolbar,
.case-section + .case-section,
.results {
    border-top: 1px solid rgba(56, 217, 255, 0.12);
}

.left-header {
    border-top: 0;
}

.left-header h1,
.section-head h2,
.toolbar-copy strong {
    margin: 0;
    color: var(--ab-text);
}

.left-header h1 {
    margin-top: 14px;
    font-size: clamp(28px, 3vw, 40px);
    font-weight: 850;
    line-height: 1.02;
}

.header-pills,
.toolbar-actions,
.font-controls,
.case-head,
.submission-head {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    justify-content: space-between;
}

.tab-bar {
    display: flex;
    gap: 10px;
    padding: 14px 18px;
    border-top: 1px solid rgba(56, 217, 255, 0.12);
    border-bottom: 1px solid rgba(56, 217, 255, 0.12);
    background: rgba(5, 10, 16, 0.94);
}

.tab-button,
.tool-button,
.remove-button,
.custom-input {
    border: 1px solid var(--ab-border);
    border-radius: 8px;
}

.tab-button,
.tool-button,
.remove-button {
    cursor: pointer;
    transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease, color 0.2s ease;
}

.tab-button {
    flex: 1;
    min-height: 40px;
    padding: 0 14px;
    background: rgba(11, 22, 34, 0.76);
    color: var(--ab-muted);
    font-size: 13px;
    font-weight: 800;
}

.tab-button.active,
.tool-button.primary {
    border-color: transparent;
    background: linear-gradient(135deg, var(--ab-cyan), var(--ab-teal));
    color: #031016;
}

.tab-content {
    min-height: 0;
    padding: 18px;
    overflow: auto;
}

.stack {
    display: grid;
    gap: 14px;
}

.stack.compact {
    gap: 12px;
}

.card,
.subcard {
    padding: 16px;
    border: 1px solid rgba(56, 217, 255, 0.12);
    border-radius: 8px;
    background: rgba(10, 21, 32, 0.84);
}

.card.empty {
    text-align: center;
}

.eyebrow,
.case-grid span,
.output-block span {
    color: var(--ab-cyan);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.copy,
.meta-copy {
    margin: 10px 0 0;
    color: var(--ab-muted);
    font-size: 14px;
    line-height: 1.7;
    white-space: pre-wrap;
}

.copy.small {
    font-size: 13px;
}

.meta-dot {
    margin: 0 8px;
    color: rgba(137, 169, 184, 0.48);
}

.info-pill,
.difficulty-pill,
.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 28px;
    padding: 0 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.info-pill {
    border: 1px solid rgba(56, 217, 255, 0.16);
    background: rgba(56, 217, 255, 0.08);
    color: var(--ab-cyan);
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

.status-pill {
    border: 1px solid transparent;
}

.status-accepted {
    background: rgba(24, 242, 195, 0.14);
    color: #89ffe0;
}

.status-wrong {
    background: rgba(255, 207, 138, 0.14);
    color: #ffd38a;
}

.status-failed {
    background: rgba(255, 93, 122, 0.14);
    color: #ffb0bf;
}

.status-neutral {
    background: rgba(56, 217, 255, 0.12);
    color: var(--ab-cyan);
}

.toolbar {
    display: flex;
    justify-content: space-between;
    gap: 18px;
    align-items: center;
}

.toolbar-copy {
    display: grid;
    gap: 4px;
}

.font-controls {
    padding: 6px;
    color: var(--ab-text);
    font-size: 13px;
    font-weight: 700;
}

.tool-button {
    min-height: 40px;
    padding: 0 14px;
    background: rgba(11, 22, 34, 0.76);
    color: var(--ab-text);
    font-size: 13px;
    font-weight: 800;
}

.language-select {
    min-height: 40px;
    padding: 0 38px 0 14px;
    border: 1px solid var(--ab-border);
    border-radius: 8px;
    background: rgba(11, 22, 34, 0.76);
    color: var(--ab-text);
    font-size: 13px;
    font-weight: 800;
}

.language-select:focus {
    border-color: var(--ab-cyan);
    outline: none;
    box-shadow: 0 0 0 3px rgba(56, 217, 255, 0.16);
}

.tool-button.secondary:hover,
.tab-button:hover,
.remove-button:hover {
    border-color: var(--ab-border-strong);
    background: rgba(56, 217, 255, 0.1);
    color: var(--ab-cyan);
    transform: translateY(-1px);
}

.tool-button.primary:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 12px 30px rgba(24, 242, 195, 0.22);
}

.tool-button:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.editor-wrapper {
    height: 100%;
    min-height: 460px;
    border-top: 1px solid rgba(56, 217, 255, 0.12);
    border-bottom: 1px solid rgba(56, 217, 255, 0.12);
}

.editor-wrapper :deep(.monaco-editor-container),
.editor-wrapper :deep(.monaco-editor),
.editor-wrapper :deep(.overflow-guard) {
    height: 100% !important;
}

.cases {
    display: grid;
}

.section-head {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 14px;
}

.case-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-top: 12px;
}

.case-grid.single {
    grid-template-columns: 1fr;
}

pre,
code {
    font-family: Consolas, 'Courier New', monospace;
}

.case-grid pre,
.output-block pre,
.code-block {
    margin: 0;
    padding: 12px;
    border: 1px solid rgba(56, 217, 255, 0.08);
    border-radius: 8px;
    background: rgba(4, 9, 14, 0.88);
    color: #d8f4ff;
    font-size: 13px;
    line-height: 1.7;
    white-space: pre-wrap;
    word-break: break-word;
}

.code-block {
    margin-top: 14px;
    overflow-x: auto;
}

.custom-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 10px;
}

.custom-input {
    min-height: 42px;
    padding: 0 14px;
    background: rgba(4, 9, 14, 0.88);
    color: var(--ab-text);
}

.custom-input:focus {
    border-color: var(--ab-cyan);
    outline: none;
    box-shadow: 0 0 0 3px rgba(56, 217, 255, 0.16);
}

.error-copy {
    margin: 10px 0 0;
    color: #ff9cb0;
    font-size: 13px;
    font-weight: 700;
}

.remove-button {
    min-height: 34px;
    padding: 0 12px;
    background: rgba(255, 93, 122, 0.08);
    color: #ffb0bf;
    font-size: 12px;
    font-weight: 800;
}

.results {
    border-top: 1px solid rgba(56, 217, 255, 0.12);
}

.results-success {
    background: rgba(24, 242, 195, 0.08);
}

.results-error {
    background: rgba(255, 93, 122, 0.08);
}

@media (max-width: 1220px) {
    .workspace {
        grid-template-columns: 1fr;
    }

    .left-panel {
        position: static;
        min-height: 0;
    }
}

@media (max-width: 760px) {
    .detail-shell {
        width: min(100%, calc(100% - 20px));
        margin-top: 14px;
        margin-bottom: 24px;
    }

    .toolbar,
    .section-head,
    .submission-head,
    .case-head {
        display: grid;
    }

    .case-grid,
    .custom-row {
        grid-template-columns: 1fr;
    }

    .tab-bar {
        display: grid;
    }
}
</style>
