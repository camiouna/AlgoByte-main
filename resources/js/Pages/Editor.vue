<script setup>
import { computed, ref, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { VueMonacoEditor } from '@guolao/vue-monaco-editor';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const functionName = 'solution';
const outputDataType = 'string';
const numberOfInputs = ref(1);
const language = ref('typescript');
const theme = ref(localStorage.getItem('editorTheme') || 'vs-dark');
const fontSize = ref(Number(localStorage.getItem('editorFontSize')) || 14);
const code = ref('');
const listOfTestCases = ref([
    {
        id: Date.now(),
        inputs: ['hello'],
        output: 'hello',
        status: 'not executed',
    },
]);
const draftInputs = ref(['']);
const draftOutput = ref('');
const isSubmitting = ref(false);
const statusMessage = ref('');
const errorMessage = ref('');
const lastSubmission = ref(null);
const failedTestCase = ref(null);
const submissionList = ref([]);
const matchedOutput = ref(true);
let runId = 0;

const languages = [
    { value: 'typescript', label: 'TypeScript' },
    { value: 'python', label: 'Python' },
    { value: 'java', label: 'Java' },
    { value: 'c', label: 'C' },
];

const themes = [
    { value: 'vs-dark', label: 'Dark' },
    { value: 'vs', label: 'Light' },
    { value: 'hc-black', label: 'High Contrast' },
    { value: 'eclipse', label: 'Eclipse' },
];

const inputsDataType = computed(() => Array(numberOfInputs.value).fill('string'));
const inputsNames = computed(() =>
    Array.from({ length: numberOfInputs.value }, (_, index) => `input${index + 1}`),
);
const isDarkTheme = computed(() => !['vs', 'eclipse'].includes(theme.value));
const numberOfTestCases = computed(() => listOfTestCases.value.length);

const fullFunction = computed(() => {
    switch (language.value) {
        case 'python':
            return `def ${functionName}(${inputsNames.value.join(', ')}):\n    # your code here\n    return ${inputsNames.value[0]}`;
        case 'java':
            return `public String ${functionName}(${inputsNames.value.map((name) => `String ${name}`).join(', ')}) {\n  // your code here\n  return ${inputsNames.value[0]};\n}`;
        case 'c':
            return `char* ${functionName}(${inputsNames.value.map((name) => `char* ${name}`).join(', ')}) {\n  // your code here\n  return ${inputsNames.value[0]};\n}`;
        default:
            return `function ${functionName}(${inputsNames.value.join(', ')}) {\n  // your code here\n  return ${inputsNames.value[0]}\n}`;
    }
});

code.value = fullFunction.value;

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
        'editor.background': '#ffffff',
        'editor.foreground': '#000000',
    },
};

const handleBeforeMount = (monaco) => {
    monaco.editor.defineTheme('eclipse', eclipseTheme);
};

const editorOptions = computed(() => ({
    fontSize: fontSize.value,
    automaticLayout: true,
    minimap: { enabled: true },
    scrollBeyondLastLine: false,
    wordWrap: 'on',
}));

const resultPanelClass = computed(() =>
    errorMessage.value || !matchedOutput.value || ['compile_error', 'runtime_error', 'runner_unavailable','Failed'].includes(lastSubmission.value?.status)
        ? 'submission-panel-error'
        : 'submission-panel-success',
);

const formatExecutionStatus = (status) => (status ? status.replaceAll('_', ' ') : 'unknown');

const formatRuntimeLabel = (submission) => {
    if (!submission?.runtime) {
        return '';
    }

    return submission.runtime_version
        ? ` using ${submission.runtime} ${submission.runtime_version}`
        : ` using ${submission.runtime}`;
};

const formatInputsForLanguage = (inputs) =>
    inputs
        .map((input) => {
            if (language.value === 'python') {
                return JSON.stringify(input);
            }

            return JSON.stringify(input);
        })
        .join(', ');

const formatCodeForExecution = (testCase, sourceCode = code.value) => {
    const inputs = formatInputsForLanguage(testCase.inputs);

    switch (language.value) {
        case 'python':
            return `${sourceCode}\n\nprint(${functionName}(${inputs}))`;
        case 'java':
            return `public class Main {\n  ${sourceCode}\n\n  public static void main(String[] args) {\n    Main obj = new Main();\n    System.out.println(obj.${functionName}(${inputs}));\n  }\n}`;
        case 'c':
            return `#include <stdio.h>\n${sourceCode}\nint main() {\n  printf("%s", ${functionName}(${inputs}));\n  return 0;\n}`;
        default:
            return `${sourceCode}\n\nconsole.log(${functionName}(${inputs}))`;
    }
};

const transformValue = (value, dataType) => {
    const text = String(value ?? '').trim();

    switch (dataType.toLowerCase()) {
        case 'int':
            return parseInt(text, 10);
        case 'double':
        case 'float':
            return parseFloat(text);
        case 'boolean':
            return text.toLowerCase() === 'true';
        default:
            return text;
    }
};

const compareOutput = (pistonOutput, expectedOutput) => {
    try {
        return transformValue(pistonOutput, outputDataType) === transformValue(expectedOutput, outputDataType);
    } catch {
        return false;
    }
};

const submitToPiston = async (sourceCode) => {
    const response = await axios.post(
        route('execute'),
        {
            language: language.value,
            code: sourceCode,
        },
        {
            headers: {
                Accept: 'application/json',
            },
        },
    );

    return response.data.data;
};

const resetExecutionState = () => {
    errorMessage.value = '';
    statusMessage.value = '';
    lastSubmission.value = null;
    failedTestCase.value = null;
    matchedOutput.value = true;
    submissionList.value = [];
};

const runCode = async () => {
    const currentRunId = ++runId;
    const initialCode = code.value;

    if (!initialCode.trim()) {
        errorMessage.value = 'Write some code before sending it to Piston.';
        return;
    }

    resetExecutionState();
    isSubmitting.value = true;
    statusMessage.value = 'Running code on Piston...';

    try {
        const casesToRun = listOfTestCases.value.length
            ? listOfTestCases.value
            : [{ id: 'compile-only', inputs: Array(numberOfInputs.value).fill(''), output: '', status: 'not executed' }];

        for (const testCase of casesToRun) {
            testCase.status = 'not executed';
            const wrappedCode = formatCodeForExecution(testCase, initialCode);
            const submission = await submitToPiston(wrappedCode);

            if (currentRunId !== runId) {
                return;
            }

            lastSubmission.value = submission;

            if (submission.status !== 'Completed') {
                statusMessage.value = submission.status === 'runner_unavailable'
                    ? 'Piston is unavailable.'
                    : 'Code executed with errors.';
                testCase.status = 'Failed';
                failedTestCase.value = testCase.id === 'compile-only' ? null : testCase;
                matchedOutput.value = false;
                break;
            }

            if (compareOutput(submission.stdout, testCase.output)) {
                submission.status = 'Accepted';
                testCase.status = 'Accepted';
                submissionList.value.push(submission);
                statusMessage.value = 'Code executed successfully.';
                lastSubmission.value = submission;
                continue;
            }

            submission.status = 'Wrong Answer';
            testCase.status = 'Wrong Answer';
            failedTestCase.value = testCase;
            matchedOutput.value = false;
            statusMessage.value = 'Code executed but the output did not match the expected output.';
            lastSubmission.value = submission;
            break;
        }
    } catch (error) {
        if (currentRunId === runId) {
            errorMessage.value = error.response?.data?.message || error.message || 'Unable to reach Piston.';
        }
    } finally {
        if (currentRunId === runId) {
            isSubmitting.value = false;
        }
    }
};

const addTestCase = () => {
    const inputs = draftInputs.value.map((input) => input || 'Null');

    listOfTestCases.value.push({
        id: Date.now(),
        inputs,
        output: draftOutput.value || 'Null',
        status: 'not executed',
    });

    draftInputs.value = Array(numberOfInputs.value).fill('');
    draftOutput.value = '';
};

const removeTestCase = (testCase) => {
    listOfTestCases.value = listOfTestCases.value.filter((item) => item.id !== testCase.id);
    if (failedTestCase.value?.id === testCase.id) {
        failedTestCase.value = null;
        matchedOutput.value = true;
    }
};

const increaseFontSize = () => {
    if (fontSize.value < 36) {
        fontSize.value += 2;
    }
};

const decreaseFontSize = () => {
    if (fontSize.value > 10) {
        fontSize.value -= 2;
    }
};

watch(language, () => {
    code.value = fullFunction.value;
});

watch(numberOfInputs, () => {
    draftInputs.value = Array(numberOfInputs.value).fill('');
    code.value = fullFunction.value;
});

watch(theme, (value) => localStorage.setItem('editorTheme', value));
watch(fontSize, (value) => localStorage.setItem('editorFontSize', value));
</script>

<template>
    <Head title="Editor" />

    <AuthenticatedLayout>
        <div :class="['editor-container', isDarkTheme ? 'dark-theme' : 'light-theme']">
            <div :class="['toolbar d-flex align-items-center justify-content-between p-2', isDarkTheme ? 'text-white' : 'text-dark']">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <label for="theme-select" class="mb-0 fw-bold small d-none d-sm-block">Theme</label>
                        <select id="theme-select" v-model="theme" class="form-select form-select-sm">
                            <option v-for="item in themes" :key="item.value" :value="item.value">
                                {{ item.label }}
                            </option>
                        </select>
                    </div>

                    <div class="vr mx-1 d-none d-sm-block"></div>

                    <div class="d-flex align-items-center gap-2">
                        <label for="language-select" class="mb-0 fw-bold small d-none d-sm-block">Language</label>
                        <select id="language-select" v-model="language" class="form-select form-select-sm">
                            <option v-for="item in languages" :key="item.value" :value="item.value">
                                {{ item.label }}
                            </option>
                        </select>
                    </div>

                    <div class="vr mx-1 d-none d-sm-block"></div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="small fw-bold d-none d-sm-block">Font size</span>
                        <div class="btn-group shadow-sm">
                            <button @click="decreaseFontSize" class="btn btn-sm btn-outline-light" title="Decrease Font Size">
                                -
                            </button>
                            <div class="font-size-display">
                                {{ fontSize }}px
                            </div>
                            <button @click="increaseFontSize" class="btn btn-sm btn-outline-light" title="Increase Font Size">
                                +
                            </button>
                        </div>
                    </div>
                </div>

                <button @click="runCode()" :disabled="isSubmitting" class="btn btn-success btn-sm px-4 py-1 fw-bold d-flex align-items-center gap-2 shadow run-btn rounded-pill border-0">
                    <span v-if="isSubmitting">Running...</span>
                    <template v-else>
                        <span class="d-none d-sm-inline">Run Code</span>
                        <span class="d-inline d-sm-none">Run</span>
                    </template>
                </button>
            </div>

            <div class="editor-wrapper">
                <VueMonacoEditor
                    v-model:value="code"
                    :language="language"
                    :theme="theme"
                    :options="editorOptions"
                    @before-mount="handleBeforeMount"
                />
            </div>

            <section class="testcase-bar">
                <div class="testcase-count">
                    Number of test cases: {{ numberOfTestCases }}
                </div>

                <div class="testcase-list">
                    <button
                        v-for="(testCase, index) in listOfTestCases"
                        :key="testCase.id"
                        type="button"
                        :class="['testcase-card', testCase.status]"
                    >
                        <span>testcase {{ index + 1 }}: {{ testCase.inputs.join(', ') }} => output: {{ testCase.output }}</span>
                        <span class="testcase-remove" @click.stop="removeTestCase(testCase)">x</span>
                    </button>
                </div>

                <form class="testcase-form" @submit.prevent="addTestCase">
                    <input
                        v-for="(_, index) in draftInputs"
                        :key="index"
                        v-model="draftInputs[index]"
                        type="text"
                        :placeholder="`${inputsNames[index]} (${inputsDataType[index]})`"
                    />
                    <input v-model="draftOutput" type="text" :placeholder="`output (${outputDataType})`" />
                    <button type="submit">Add test case</button>
                </form>
            </section>

            <div v-if="isSubmitting || statusMessage || errorMessage || lastSubmission" :class="['submission-panel px-3 py-2 small border-top', resultPanelClass]">
                <div v-if="isSubmitting">Running code on Piston...</div>
                <div v-else-if="errorMessage">{{ errorMessage }}</div>
                <div v-else-if="lastSubmission" class="result-stack">
                    <div class="result-summary">
                        {{ statusMessage }}
                        Status: "{{ formatExecutionStatus(lastSubmission.status) }}"{{ formatRuntimeLabel(lastSubmission) }}.
                        <span v-if="lastSubmission.execution_time_ms">Time: {{ lastSubmission.execution_time_ms }}ms</span>
                        <p>Number of test cases passed {{ submissionList.length }}</p>
                    </div>

                    <div v-if="!matchedOutput && failedTestCase" class="result-summary">
                        Failed: {{ failedTestCase.inputs.join(', ') }} => expected {{ failedTestCase.output }}
                    </div>

                    <div class="result-meta">
                        <span>Engine: Piston</span>
                        <span>Language: {{ lastSubmission.runtime ?? 'n/a' }}</span>
                        <span>Version: {{ lastSubmission.runtime_version ?? 'n/a' }}</span>
                        <span>Exit code: {{ lastSubmission.exit_code ?? 'n/a' }}</span>
                        <span>Signal: {{ lastSubmission.signal ?? 'none' }}</span>
                    </div>

                    <div v-if="lastSubmission.compile_output" class="result-block">
                        <div class="result-label">Compiler Output</div>
                        <pre>{{ lastSubmission.compile_output }}</pre>
                    </div>

                    <div v-if="lastSubmission.stdout" class="result-block">
                        <div class="result-label">Standard Output</div>
                        <pre>{{ lastSubmission.stdout }}</pre>
                    </div>

                    <div v-if="lastSubmission.stderr" class="result-block">
                        <div class="result-label">Standard Error</div>
                        <pre>{{ lastSubmission.stderr }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.editor-container {
    width: min(1440px, calc(100% - 32px));
    margin: 18px auto 28px;
    overflow: hidden;
    border: 1px solid var(--ab-border);
    border-radius: 8px;
    box-shadow: var(--ab-shadow);
    transition: background-color 0.3s ease;
}

.editor-container.dark-theme {
    background-color: rgba(5, 10, 16, 0.94);
}

.editor-container.light-theme {
    background-color: #edf7fb;
}

.toolbar {
    gap: 1rem;
    border-bottom: 1px solid var(--ab-border);
    background: rgba(11, 22, 34, 0.96);
    transition: all 0.3s ease;
}

.d-flex {
    display: flex;
}

.align-items-center {
    align-items: center;
}

.justify-content-between {
    justify-content: space-between;
}

.gap-2 {
    gap: 0.5rem;
}

.gap-3 {
    gap: 0.75rem;
}

.flex-wrap {
    flex-wrap: wrap;
}

.p-2 {
    padding: 0.5rem;
}

.my-3 {
    margin-top: 1rem;
    margin-bottom: 1rem;
}

.mx-2 {
    margin-left: 0.5rem;
    margin-right: 0.5rem;
}

.border-top {
    border-width: 1px 0 0;
    border-color: var(--ab-border);
}

.rounded {
    border-radius: 0.375rem;
}

.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.overflow-hidden {
    overflow: hidden;
}

.text-white {
    color: var(--ab-text);
}

.text-dark {
    color: #0b1622;
}

.small {
    font-size: 0.875rem;
}

.fw-bold {
    font-weight: 700;
}

.form-select-sm,
.testcase-form input {
    border: 1px solid var(--ab-border);
    border-radius: 8px;
    padding: 0.25rem 1.75rem 0.25rem 0.5rem;
    background: rgba(5, 10, 16, 0.95);
    color: var(--ab-text);
    font-size: 0.875rem;
}

.form-select-sm:focus,
.testcase-form input:focus {
    border-color: var(--ab-cyan);
    outline: none;
    box-shadow: 0 0 0 3px rgba(56, 217, 255, 0.16);
}

.editor-wrapper {
    width: 100%;
    height: 75vh;
    min-height: 400px;
}

.btn,
.testcase-form button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 1px solid transparent;
    cursor: pointer;
    font-weight: 600;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.btn-outline-light {
    border-color: var(--ab-border);
    color: var(--ab-text);
    background: rgba(5, 10, 16, 0.65);
}

.btn-outline-light:hover {
    border-color: var(--ab-border-strong);
    background-color: rgba(56, 217, 255, 0.12);
    color: var(--ab-cyan);
}

.btn-success,
.testcase-form button {
    color: #031016;
    background: linear-gradient(135deg, var(--ab-cyan), var(--ab-teal));
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-group {
    display: inline-flex;
}

.font-size-display {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 45px;
    padding: 0 0.5rem;
    border-top: 1px solid var(--ab-border);
    border-bottom: 1px solid var(--ab-border);
    background: rgba(5, 10, 16, 0.65);
    color: inherit;
    font-size: 0.875rem;
}

.run-btn {
    background: linear-gradient(135deg, var(--ab-cyan), var(--ab-teal));
    transition: all 0.2s ease-in-out;
}

.run-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(24, 242, 195, 0.22);
    background: linear-gradient(135deg, #60e4ff, #45ffd7);
}

.testcase-bar {
    display: grid;
    grid-template-columns: minmax(120px, 0.18fr) 1fr;
    gap: 0.75rem;
    padding: 0.75rem;
    border-top: 1px solid var(--ab-border);
    background: rgba(7, 17, 27, 0.96);
}

.testcase-count {
    color: var(--ab-muted);
    font-size: 0.875rem;
    font-weight: 700;
}

.testcase-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.testcase-card {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    max-width: 360px;
    padding: 0.5rem 0.75rem;
    border: 1px solid transparent;
    border-color: var(--ab-border);
    border-radius: 8px;
    background: rgba(5, 10, 16, 0.9);
    color: var(--ab-text);
    text-align: left;
}

.testcase-card.passed {
    border-color: var(--ab-teal);
    box-shadow: inset 0 0 0 1px rgba(24, 242, 195, 0.18);
}

.testcase-card.failed {
    border-color: var(--ab-danger);
}

.testcase-remove {
    color: var(--ab-danger);
    font-weight: 800;
}

.testcase-form {
    grid-column: 2;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.testcase-form button {
    padding: 0.35rem 0.75rem;
    font-weight: 800;
}

.submission-panel {
    border-color: var(--ab-border);
    transition: background-color 0.3s ease;
}

.result-stack {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.result-summary {
    font-weight: 600;
}

.result-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    opacity: 0.9;
}

.result-block {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.result-label {
    font-weight: 700;
}

.result-block pre {
    margin: 0;
    padding: 0.75rem;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.24);
    color: inherit;
    font-family: Consolas, 'Courier New', monospace;
    white-space: pre-wrap;
    word-break: break-word;
}

.submission-panel-success {
    background-color: rgba(24, 242, 195, 0.1);
    color: #b9fff0;
}

.submission-panel-error {
    background-color: rgba(255, 93, 122, 0.13);
    color: #ffd4dc;
}

@media (max-width: 720px) {
    .toolbar {
        align-items: stretch;
        flex-direction: column;
    }

    .testcase-bar {
        grid-template-columns: 1fr;
    }

    .testcase-form {
        grid-column: 1;
    }
}
</style>
