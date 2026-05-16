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
    comments: {
        type: Array,
        required: false,
        default: () => [],
    },
});

const page = usePage();
const currentUsername = computed(() => page.props.auth?.member?.username?.toLowerCase?.() ?? '');

const tabs = [
    { value: 'details', label: 'Problem Details' },
    { value: 'solutions', label: 'Shared Solutions' },
    { value: 'submissions', label: 'Your Submissions' },
    {value:'discussions', label:'Discussions'},
];
const displayComments = (discussion) => {
    comments.value = discussion.comments;
    currentDiscussion.value = discussion;
    activeTab.value = 'comments';
};

const upvoteComment = async (commentId) => {
    const comment = comments.value.find((c) => c.key === commentId);
    if (!comment) return;

    try {
        comment.likes += 1;
        comment.isLikedByCurrentUser = true;
        await axios.post(route('comments.upvote', { comment: comment.key }), {}, {
            headers: {
                Accept: 'application/json',
            },
        });
        
    } catch (error) {
        console.error('Failed to upvote comment:', error);
    }
};

const downvoteComment = async (commentId) => {
    const comment = comments.value.find((c) => c.key === commentId);
    if (!comment) return;

    try {
        comment.likes -= 1;
        comment.isLikedByCurrentUser = false;
        await axios.post(route('comments.downvote', { comment: comment.key }), {}, {
            headers: {
                Accept: 'application/json',
            },
        });
        
    } catch (error) {
        console.error('Failed to downvote comment:', error);
    }
};

const upvote = async (id) => {
    const discussion = discussionItems.value.find((d) => d.key === id);
    if (!discussion) return;

    try {
        discussion.likes += 1;
        discussion.isLikedByCurrentUser = true;
        await axios.post(route('discussions.upvote', { discussion: discussion.key }), {}, {
            headers: {
                Accept: 'application/json',
            },
        });
        
    } catch (error) {
        console.error('Failed to upvote:', error);
    }
};

const downvote = async (id) => {
    const discussion = discussionItems.value.find((d) => d.key === id);
    if (!discussion) return;

    try {
        discussion.likes -= 1;
        discussion.isLikedByCurrentUser = false;
        await axios.post(route('discussions.downvote', { discussion: discussion.key }), {}, {
            headers: {
                Accept: 'application/json',
            },
        });
        
    } catch (error) {
        console.error('Failed to downvote:', error);
    }
};
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
const comments = ref([]);
const currentDiscussion = ref(null);
const code = ref('');
const isValidating = ref(false);
const validationMessage = ref('');
const validationError = ref('');
const validationStatus = ref('');
const validationSummary = ref(null);
const validationResults = ref([]);
const lastValidatedSignature = ref('');
const saveSubmissionMessage = ref('');
const saveSubmissionError = ref('');
const isShareDialogOpen = ref(false);
const shareSolutionTitle = ref('');
const shareSolutionTitleError = ref('');
const isSharingSolution = ref(false);
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

const normalizeComment = (comment, index) => ({
    key: comment.commentId ?? comment.id ?? `comment-${index + 1}`,
    username: comment.username ?? '',
    content: comment.content ?? '',
    createdAt: comment.createdAt ?? comment.created_at ?? '',
    likes: comment.likes ?? 0,
    isLikedByCurrentUser: comment.isLikedByCurrentUser ?? false,
});

const normalizeDiscussion = (discussion, index) => ({
    key: discussion.discussionId ?? discussion.id ?? `discussion-${index + 1}`,
    username: discussion.username ?? '',
    title: discussion.title ?? `Discussion ${index + 1}`,
    content: discussion.content ?? '',
    createdAt: discussion.createdAt ?? discussion.created_at ?? '',
    comments: (discussion.comments ?? []).map(normalizeComment),
    likes: discussion.likes ?? 0,
    isLikedByCurrentUser: discussion.isLikedByCurrentUser ?? false,
});
const savedCases = computed(() => (props.problem.testCases ?? []).map(normalizeCase));
const submissionItems = ref((props.problem.submissions ?? []).map(normalizeSubmission));
const submissions = computed(() => submissionItems.value);
const sharedSolutionItems = ref((props.problem.sharedSolutions ?? []).map(normalizeSharedSolution));
const sharedSolutions = computed(() => sharedSolutionItems.value);
const discussionItems = ref((props.problem.discussions ?? []).map(normalizeDiscussion));
const discussions = computed(() => discussionItems.value);
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
const validationSignature = computed(() =>
    JSON.stringify({
        language: selectedLanguage.value,
        code: code.value,
        customCases: customCases.value.map((testCase) => ({
            label: testCase.label,
            input: testCase.input,
        })),
    }),
);
const hasCurrentValidation = computed(
    () => lastValidatedSignature.value !== '' && lastValidatedSignature.value === validationSignature.value,
);
const canSaveSubmission = computed(() => hasCurrentValidation.value && !isValidating.value);
const canShareSolution = computed(
    () => hasCurrentValidation.value && validationStatus.value === 'Accepted' && !isValidating.value,
);

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

const saveSubmission = async () => {
    if (!canSaveSubmission.value) {
        return;
    }

    saveSubmissionMessage.value = '';
    saveSubmissionError.value = '';

    try {
        const response = await axios.post(
            route('submissions.store', { problem: props.problem.problemId }),
            {
                code: code.value,
                language: selectedLanguage.value,
                status: validationStatus.value,
                stdout: validationResults.value.map((result) => result.actualOutput).join('\n\n---\n\n'),
                stderr: validationError.value,
                compileOutput: validationMessage.value,
                compile_output: validationMessage.value,
                exit_code: validationStatus.value === 'Accepted' ? 0 : 1,
            },
            {
                headers: {
                    Accept: 'application/json',
                },
            },
        );

        const savedSubmission = normalizeSubmission(response.data?.data ?? {}, 0);
        submissionItems.value = [savedSubmission, ...submissionItems.value];
        saveSubmissionMessage.value = response.data?.message ?? 'Submission saved successfully.';
        activeTab.value = 'submissions';
    } catch (error) {
        saveSubmissionError.value =
            error.response?.data?.message ??
            Object.values(error.response?.data?.errors ?? {}).flat().join(' ') ??
            error.message ??
            'Failed to save submission.';
        console.error('Failed to save submission:', error.response?.data ?? error.message ?? error);
    }
};

const openShareSolutionDialog = () => {
    if (!canShareSolution.value) {
        return;
    }

    shareSolutionTitle.value = '';
    shareSolutionTitleError.value = '';
    saveSubmissionMessage.value = '';
    saveSubmissionError.value = '';
    isShareDialogOpen.value = true;
};

const closeShareSolutionDialog = (force = false) => {
    if (isSharingSolution.value && !force) {
        return;
    }

    isShareDialogOpen.value = false;
    shareSolutionTitle.value = '';
    shareSolutionTitleError.value = '';
};

const shareSolution = async () => {
    if (!canShareSolution.value || isSharingSolution.value) {
        return;
    }

    const normalizedTitle = shareSolutionTitle.value.trim();

    if (!normalizedTitle) {
        shareSolutionTitleError.value = 'Enter a title for the shared solution.';
        return;
    }

    saveSubmissionMessage.value = '';
    saveSubmissionError.value = '';
    shareSolutionTitleError.value = '';
    isSharingSolution.value = true;

    try {
        const response = await axios.post(
            route('solutions.store', { problem: props.problem.problemId }),
            {
                title: normalizedTitle,
                code: code.value,
                language: selectedLanguage.value,
                status: validationStatus.value,
                stdout: validationResults.value.map((result) => result.actualOutput).join('\n\n---\n\n'),
                stderr: validationError.value,
                compileOutput: validationMessage.value,
                compile_output: validationMessage.value,
                exit_code: validationStatus.value === 'Accepted' ? 0 : 1,
            },
            {
                headers: {
                    Accept: 'application/json',
                },
            },
        );

        const savedSolution = normalizeSharedSolution(response.data?.data ?? {}, 0);
        sharedSolutionItems.value = [savedSolution, ...sharedSolutionItems.value];
        saveSubmissionMessage.value = response.data?.message ?? 'Solution shared successfully.';
        activeTab.value = 'solutions';
        closeShareSolutionDialog(true);
    } catch (error) {
        saveSubmissionError.value =
            error.response?.data?.message ??
            Object.values(error.response?.data?.errors ?? {}).flat().join(' ') ??
            error.message ??
            'Failed to share solution.';
        shareSolutionTitleError.value = error.response?.data?.errors?.title?.[0] ?? '';
        console.error('Failed to share solution:', error.response?.data ?? error.message ?? error);
    } finally {
        isSharingSolution.value = false;
    }
};

const validateSolution = async () => {
    if (!code.value.trim()) {
        validationError.value = 'Write a solution before validating.';
        validationMessage.value = '';
        validationStatus.value = '';
        validationSummary.value = null;
        validationResults.value = [];
        lastValidatedSignature.value = '';
        return;
    }

    if (!allCases.value.length) {
        validationError.value = 'No testcases are available for this problem.';
        validationMessage.value = '';
        validationStatus.value = '';
        validationSummary.value = null;
        validationResults.value = [];
        lastValidatedSignature.value = '';
        return;
    }

    validationError.value = '';
    validationMessage.value = '';
    validationStatus.value = '';
    validationSummary.value = null;
    validationResults.value = [];
    lastValidatedSignature.value = '';
    saveSubmissionMessage.value = '';
    saveSubmissionError.value = '';
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
        validationMessage.value = status === 'Accepted' ? 'All testcases passed.' : 'Validation completed with issues.';
        lastValidatedSignature.value = validationSignature.value;
    } catch (error) {
        validationError.value = error.response?.data?.message ?? error.message ?? 'Unable to validate right now.';
        lastValidatedSignature.value = '';
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

                        <div v-else-if="activeTab==='submissions'" class="stack">
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
                        <div v-else-if="activeTab==='discussions'" class="stack" style="overflow: scroll;">
                            <article
                                v-for="discussion in discussions"
                                :key="discussion.key"   
                                class="card"
                            >
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;"> 
                                    <div style="flex: 1;cursor:pointer" @click="displayComments(discussion)">
                                        <h4>By: <strong style="color: darkcyan;">{{ discussion.username }}</strong><span class="meta-dot">•</span>
                                            {{ discussion.createdAt || 'Recently posted' }}  </h4><br>
                                        <h1 style="font-size: larger;"><strong>{{ discussion.title }}</strong></h1>
                                        <p class="meta-copy">
                                            {{ discussion.content || 'No content available.' }} 
                                        </p>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: center; margin-left: 16px;">
                                        <button v-if="!discussion.isLikedByCurrentUser"
                                            @click="upvote(discussion.key)"
                                            type="button"
                                            :style="{
                                                fontSize: '20px',
                                         
                                                border: '1px solid #ccc',
                                                borderRadius: '8px',
                                                cursor: 'pointer',
                                                padding: '8px',
                                            }"
                                            title="Like this discussion"
                                        >

                                            <svg  xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-hand-thumbs-up" viewBox="0 0 16 16">
  <path d="M8.864.046C7.908-.193 7.02.53 6.956 1.466c-.072 1.051-.23 2.016-.428 2.59-.125.36-.479 1.013-1.04 1.639-.557.623-1.282 1.178-2.131 1.41C2.685 7.288 2 7.87 2 8.72v4.001c0 .845.682 1.464 1.448 1.545 1.07.114 1.564.415 2.068.723l.048.03c.272.165.578.348.97.484.397.136.861.217 1.466.217h3.5c.937 0 1.599-.477 1.934-1.064a1.86 1.86 0 0 0 .254-.912c0-.152-.023-.312-.077-.464.201-.263.38-.578.488-.901.11-.33.172-.762.004-1.149.069-.13.12-.269.159-.403.077-.27.113-.568.113-.857 0-.288-.036-.585-.113-.856a2 2 0 0 0-.138-.362 1.9 1.9 0 0 0 .234-1.734c-.206-.592-.682-1.1-1.2-1.272-.847-.282-1.803-.276-2.516-.211a10 10 0 0 0-.443.05 9.4 9.4 0 0 0-.062-4.509A1.38 1.38 0 0 0 9.125.111zM11.5 14.721H8c-.51 0-.863-.069-1.14-.164-.281-.097-.506-.228-.776-.393l-.04-.024c-.555-.339-1.198-.731-2.49-.868-.333-.036-.554-.29-.554-.55V8.72c0-.254.226-.543.62-.65 1.095-.3 1.977-.996 2.614-1.708.635-.71 1.064-1.475 1.238-1.978.243-.7.407-1.768.482-2.85.025-.362.36-.594.667-.518l.262.066c.16.04.258.143.288.255a8.34 8.34 0 0 1-.145 4.725.5.5 0 0 0 .595.644l.003-.001.014-.003.058-.014a9 9 0 0 1 1.036-.157c.663-.06 1.457-.054 2.11.164.175.058.45.3.57.65.107.308.087.67-.266 1.022l-.353.353.353.354c.043.043.105.141.154.315.048.167.075.37.075.581 0 .212-.027.414-.075.582-.05.174-.111.272-.154.315l-.353.353.353.354c.047.047.109.177.005.488a2.2 2.2 0 0 1-.505.805l-.353.353.353.354c.006.005.041.05.041.17a.9.9 0 0 1-.121.416c-.165.288-.503.56-1.066.56z"/>
</svg>
                                            
                                        </button>
                                        <button v-else
                                            type="button"
                                            @click="downvote(discussion.key)"
                                            :style="{
                                                fontSize: '20px',
                                         
                                                border: '1px solid #ccc',
                                                borderRadius: '8px',
                                                cursor: 'pointer',
                                                padding: '8px',
                                            }"
                                            title="Unlike this discussion"
                                        >

                                            
<svg  xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-hand-thumbs-up-fill" viewBox="0 0 16 16">
  <path d="M6.956 1.745C7.021.81 7.908.087 8.864.325l.261.066c.463.116.874.456 1.012.965.22.816.533 2.511.062 4.51a10 10 0 0 1 .443-.051c.713-.065 1.669-.072 2.516.21.518.173.994.681 1.2 1.273.184.532.16 1.162-.234 1.733q.086.18.138.363c.077.27.113.567.113.856s-.036.586-.113.856c-.039.135-.09.273-.16.404.169.387.107.819-.003 1.148a3.2 3.2 0 0 1-.488.901c.054.152.076.312.076.465 0 .305-.089.625-.253.912C13.1 15.522 12.437 16 11.5 16H8c-.605 0-1.07-.081-1.466-.218a4.8 4.8 0 0 1-.97-.484l-.048-.03c-.504-.307-.999-.609-2.068-.722C2.682 14.464 2 13.846 2 13V9c0-.85.685-1.432 1.357-1.615.849-.232 1.574-.787 2.132-1.41.56-.627.914-1.28 1.039-1.639.199-.575.356-1.539.428-2.59z"/>
</svg>
                                            
                                        </button>
                                        <span style="margin-top: 4px; font-size: 14px; color: #aaa;">{{ discussion.likes }}</span>
                                    </div>
                                </div>
                            </article>

                            <div v-if="discussions.length === 0" class="card empty">
                                Be the first to start a discussion on this problem !
                            </div>
                        </div>

                        <div v-else-if="activeTab==='comments'" class="stack" style="overflow: scroll;">
                            <h1 style="font-size: larger;"><strong>{{ currentDiscussion?.title }}</strong></h1>
                            <article
                                v-for="comment in comments"
                                :key="comment.key"
                                class="card"
                            >
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div style="flex: 1;">
                                        <h4>By: <strong style="color: darkcyan;">{{ comment.username }}</strong><span class="meta-dot">•</span>
                                            {{ comment.createdAt || 'Recently posted' }}</h4><br>

                                        <p class="meta-copy">
                                            {{ comment.content || 'No content available.' }}
                                        </p>
                                    </div>

                                    <div style="display: flex; flex-direction: column; align-items: center; margin-left: 16px;">
                                        <button v-if="!comment.isLikedByCurrentUser"
                                            @click="upvoteComment(comment.key)"
                                            type="button"
                                            :style="{
                                                fontSize: '20px',

                                                border: '1px solid #ccc',
                                                borderRadius: '8px',
                                                cursor: 'pointer',
                                                padding: '8px',
                                            }"
                                            title="Like this Comment"
                                        >

                                            <svg  xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-hand-thumbs-up" viewBox="0 0 16 16">
  <path d="M8.864.046C7.908-.193 7.02.53 6.956 1.466c-.072 1.051-.23 2.016-.428 2.59-.125.36-.479 1.013-1.04 1.639-.557.623-1.282 1.178-2.131 1.41C2.685 7.288 2 7.87 2 8.72v4.001c0 .845.682 1.464 1.448 1.545 1.07.114 1.564.415 2.068.723l.048.03c.272.165.578.348.97.484.397.136.861.217 1.466.217h3.5c.937 0 1.599-.477 1.934-1.064a1.86 1.86 0 0 0 .254-.912c0-.152-.023-.312-.077-.464.201-.263.38-.578.488-.901.11-.33.172-.762.004-1.149.069-.13.12-.269.159-.403.077-.27.113-.568.113-.857 0-.288-.036-.585-.113-.856a2 2 0 0 0-.138-.362 1.9 1.9 0 0 0 .234-1.734c-.206-.592-.682-1.1-1.2-1.272-.847-.282-1.803-.276-2.516-.211a10 10 0 0 0-.443.05 9.4 9.4 0 0 0-.062-4.509A1.38 1.38 0 0 0 9.125.111zM11.5 14.721H8c-.51 0-.863-.069-1.14-.164-.281-.097-.506-.228-.776-.393l-.04-.024c-.555-.339-1.198-.731-2.49-.868-.333-.036-.554-.29-.554-.55V8.72c0-.254.226-.543.62-.65 1.095-.3 1.977-.996 2.614-1.708.635-.71 1.064-1.475 1.238-1.978.243-.7.407-1.768.482-2.85.025-.362.36-.594.667-.518l.262.066c.16.04.258.143.288.255a8.34 8.34 0 0 1-.145 4.725.5.5 0 0 0 .595.644l.003-.001.014-.003.058-.014a9 9 0 0 1 1.036-.157c.663-.06 1.457-.054 2.11.164.175.058.45.3.57.65.107.308.087.67-.266 1.022l-.353.353.353.354c.043.043.105.141.154.315.048.167.075.37.075.581 0 .212-.027.414-.075.582-.05.174-.111.272-.154.315l-.353.353.353.354c.047.047.109.177.005.488a2.2 2.2 0 0 1-.505.805l-.353.353.353.354c.006.005.041.05.041.17a.9.9 0 0 1-.121.416c-.165.288-.503.56-1.066.56z"/>
</svg>

                                        </button>
                                        <button v-else
                                            type="button"
                                            @click="downvoteComment(comment.key)"
                                            :style="{
                                                fontSize: '20px',

                                                border: '1px solid #ccc',
                                                borderRadius: '8px',
                                                cursor: 'pointer',
                                                padding: '8px',
                                            }"
                                            title="Unlike this comment"
                                        >


<svg  xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-hand-thumbs-up-fill" viewBox="0 0 16 16">
  <path d="M6.956 1.745C7.021.81 7.908.087 8.864.325l.261.066c.463.116.874.456 1.012.965.22.816.533 2.511.062 4.51a10 10 0 0 1 .443-.051c.713-.065 1.669-.072 2.516.21.518.173.994.681 1.2 1.273.184.532.16 1.162-.234 1.733q.086.18.138.363c.077.27.113.567.113.856s-.036.586-.113.856c-.039.135-.09.273-.16.404.169.387.107.819-.003 1.148a3.2 3.2 0 0 1-.488.901c.054.152.076.312.076.465 0 .305-.089.625-.253.912C13.1 15.522 12.437 16 11.5 16H8c-.605 0-1.07-.081-1.466-.218a4.8 4.8 0 0 1-.97-.484l-.048-.03c-.504-.307-.999-.609-2.068-.722C2.682 14.464 2 13.846 2 13V9c0-.85.685-1.432 1.357-1.615.849-.232 1.574-.787 2.132-1.41.56-.627.914-1.28 1.039-1.639.199-.575.356-1.539.428-2.59z"/>
</svg>

                                        </button>
                                        <span style="margin-top: 4px; font-size: 14px; color: #aaa;">{{ comment.likes }}</span>
                                    </div>
                                </div>
                            </article>
                            <div v-if="comments.length === 0" class="card empty">
                                No comments available.
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
                            <p v-if="saveSubmissionMessage" class="copy small success-copy">
                                {{ saveSubmissionMessage }}
                            </p>
                            <p v-if="saveSubmissionError" class="error-copy">
                                {{ saveSubmissionError }}
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
                            <button type="button" class="tool-button secondary" :disabled="!canSaveSubmission" @click="saveSubmission">
                                Save Submission
                            </button>
                            <button type="button" class="tool-button secondary" :disabled="!canShareSolution || isSharingSolution" @click="openShareSolutionDialog">
                                Share Solution
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

        <div v-if="isShareDialogOpen" class="dialog-backdrop" @click.self="closeShareSolutionDialog">
            <form class="dialog-card" @submit.prevent="shareSolution">
                <div class="dialog-head">
                    <span class="eyebrow">Share Solution</span>
                    <h2>Name this solution</h2>
                </div>

                <p class="copy">
                    Add a short title before publishing this accepted solution to the shared solutions tab.
                </p>

                <label class="dialog-field">
                    <span class="eyebrow">Solution Title</span>
                    <input
                        v-model="shareSolutionTitle"
                        type="text"
                        class="dialog-input"
                        maxlength="255"
                        placeholder="Example: Sliding window walkthrough"
                        @input="shareSolutionTitleError = ''"
                        autofocus
                    />
                </label>

                <p v-if="shareSolutionTitleError" class="error-copy">{{ shareSolutionTitleError }}</p>

                <div class="dialog-actions">
                    <button type="button" class="tool-button secondary" :disabled="isSharingSolution" @click="closeShareSolutionDialog">
                        Cancel
                    </button>
                    <button type="submit" class="tool-button primary" :disabled="isSharingSolution">
                        {{ isSharingSolution ? 'Sharing...' : 'OK' }}
                    </button>
                </div>
            </form>
        </div>
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
    max-height:420px;
    padding: 18px;
    overflow: scroll;
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

.dialog-backdrop {
    position: fixed;
    inset: 0;
    z-index: 80;
    display: grid;
    place-items: center;
    padding: 20px;
    background: rgba(3, 10, 16, 0.78);
    backdrop-filter: blur(8px);
}

.dialog-card {
    width: min(520px, 100%);
    padding: 22px;
    border: 1px solid var(--ab-border-strong);
    border-radius: 16px;
    background:
        linear-gradient(180deg, rgba(10, 24, 36, 0.98), rgba(7, 17, 27, 0.98));
    box-shadow: 0 28px 70px rgba(0, 0, 0, 0.42);
}

.dialog-head {
    display: grid;
    gap: 6px;
}

.dialog-head h2 {
    margin: 0;
    color: var(--ab-text);
}

.dialog-field {
    display: grid;
    gap: 8px;
    margin-top: 18px;
}

.dialog-input {
    min-height: 44px;
    padding: 0 14px;
    border: 1px solid var(--ab-border);
    border-radius: 8px;
    background: rgba(4, 9, 14, 0.88);
    color: var(--ab-text);
    font-size: 14px;
}

.dialog-input:focus {
    border-color: var(--ab-cyan);
    outline: none;
    box-shadow: 0 0 0 3px rgba(56, 217, 255, 0.16);
}

.dialog-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 18px;
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

.success-copy {
    color: #89ffe0;
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

    .dialog-actions {
        display: grid;
    }

    .tab-bar {
        display: grid;
    }
}
</style>
