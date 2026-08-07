<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string;
        placeholder?: string;
        autoSaveStatus?: 'saved' | 'saving' | 'dirty' | 'idle';
        lastSavedAt?: string;
    }>(),
    {
        modelValue: '',
        placeholder: 'Start writing your story or article here...',
        autoSaveStatus: 'idle',
        lastSavedAt: '',
    }
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'change-json', json: any): void;
    (e: 'save-revision'): void;
}>();

const editorRef = ref<HTMLDivElement | null>(null);
const wordCount = ref(0);
const characterCount = ref(0);
const readTimeMinutes = ref(1);
const isDirty = ref(false);

watch(
    () => props.modelValue,
    (newValue) => {
        if (editorRef.value && editorRef.value.innerHTML !== newValue) {
            editorRef.value.innerHTML = newValue || '';
            calculateMetrics();
        }
    }
);

function onInput() {
    if (!editorRef.value) return;
    const html = editorRef.value.innerHTML;
    isDirty.value = true;
    calculateMetrics();
    emit('update:modelValue', html);
    emit('change-json', { type: 'doc', content: html });
}

function calculateMetrics() {
    if (!editorRef.value) return;
    const plainText = (editorRef.value.innerText || editorRef.value.textContent || '').trim();
    characterCount.value = plainText.length;
    const matches = plainText.match(/[\p{L}\p{N}]+/gu);
    wordCount.value = matches ? matches.length : 0;
    readTimeMinutes.value = Math.max(1, Math.ceil(wordCount.value / 200));
}

function execCommand(command: string, value: string | undefined = undefined) {
    document.execCommand(command, false, value);
    onInput();
}

function formatHeading(level: string) {
    execCommand('formatBlock', `<${level}>`);
}

function setLink() {
    const url = window.prompt('Enter Link URL:');
    if (url) {
        execCommand('createLink', url);
    }
}

function addImage() {
    const url = window.prompt('Enter Image URL:');
    if (url) {
        execCommand('insertImage', url);
    }
}

function handleKeydown(e: KeyboardEvent) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
        e.preventDefault();
        emit('save-revision');
    }
}

function handleBeforeUnload(e: BeforeUnloadEvent) {
    if (isDirty.value) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes.';
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    window.addEventListener('beforeunload', handleBeforeUnload);
    if (editorRef.value) {
        editorRef.value.innerHTML = props.modelValue || '';
        calculateMetrics();
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeydown);
    window.removeEventListener('beforeunload', handleBeforeUnload);
});
</script>

<template>
    <div class="border rounded-3xl overflow-hidden shadow-xs transition-colors bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 font-sans">
        <!-- Formatting Toolbar -->
        <div class="sticky top-0 z-20 flex flex-wrap items-center justify-between gap-1.5 px-4 py-3 border-b bg-slate-50/90 dark:bg-zinc-900/90 backdrop-blur-md border-slate-200 dark:border-zinc-800 text-xs font-medium">
            <!-- Left formatting tools -->
            <div class="flex items-center gap-1 flex-wrap">
                <!-- Bold -->
                <button
                    type="button"
                    @click="execCommand('bold')"
                    class="px-2.5 py-1.5 rounded-lg transition cursor-pointer font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-800"
                    title="Bold (Ctrl+B)"
                >
                    B
                </button>

                <!-- Italic -->
                <button
                    type="button"
                    @click="execCommand('italic')"
                    class="px-2.5 py-1.5 rounded-lg transition cursor-pointer italic font-serif text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-800"
                    title="Italic (Ctrl+I)"
                >
                    I
                </button>

                <!-- Strike -->
                <button
                    type="button"
                    @click="execCommand('strikeThrough')"
                    class="px-2.5 py-1.5 rounded-lg transition cursor-pointer line-through text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-800"
                    title="Strikethrough"
                >
                    S
                </button>

                <div class="w-px h-5 bg-slate-200 dark:bg-zinc-700 mx-1"></div>

                <!-- Headings -->
                <button
                    type="button"
                    @click="formatHeading('h1')"
                    class="px-2 py-1.5 rounded-lg font-bold transition cursor-pointer text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-800"
                >
                    H1
                </button>

                <button
                    type="button"
                    @click="formatHeading('h2')"
                    class="px-2 py-1.5 rounded-lg font-bold transition cursor-pointer text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-800"
                >
                    H2
                </button>

                <button
                    type="button"
                    @click="formatHeading('h3')"
                    class="px-2 py-1.5 rounded-lg font-bold transition cursor-pointer text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-800"
                >
                    H3
                </button>

                <div class="w-px h-5 bg-slate-200 dark:bg-zinc-700 mx-1"></div>

                <!-- Bullet List -->
                <button
                    type="button"
                    @click="execCommand('insertUnorderedList')"
                    class="px-2.5 py-1.5 rounded-lg transition cursor-pointer text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-800"
                    title="Bullet List"
                >
                    • List
                </button>

                <!-- Ordered List -->
                <button
                    type="button"
                    @click="execCommand('insertOrderedList')"
                    class="px-2.5 py-1.5 rounded-lg transition cursor-pointer text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-800"
                    title="Numbered List"
                >
                    1. List
                </button>

                <!-- Blockquote -->
                <button
                    type="button"
                    @click="formatHeading('blockquote')"
                    class="px-2.5 py-1.5 rounded-lg transition cursor-pointer font-serif text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-800"
                    title="Quote Block"
                >
                    “ ”
                </button>

                <div class="w-px h-5 bg-slate-200 dark:bg-zinc-700 mx-1"></div>

                <!-- Add Link -->
                <button
                    type="button"
                    @click="setLink"
                    class="px-2.5 py-1.5 rounded-lg transition cursor-pointer text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-800"
                    title="Add Hyperlink"
                >
                    🔗 Link
                </button>

                <!-- Add Image -->
                <button
                    type="button"
                    @click="addImage"
                    class="px-2.5 py-1.5 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-zinc-800 transition cursor-pointer"
                    title="Insert Image URL"
                >
                    🖼️ Image
                </button>
            </div>

            <!-- Right Save Version Action -->
            <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-zinc-400">
                <button
                    type="button"
                    @click="emit('save-revision')"
                    class="px-2.5 py-1 rounded-lg bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 hover:bg-sky-600 hover:text-white font-bold border border-sky-200 dark:border-sky-800 transition cursor-pointer flex items-center gap-1"
                >
                    💾 Save Version <span class="text-[9px] opacity-70 font-mono">(Ctrl+S)</span>
                </button>
            </div>
        </div>

        <!-- Editable Content Canvas -->
        <div
            ref="editorRef"
            contenteditable="true"
            @input="onInput"
            class="prose dark:prose-invert prose-sky max-w-none focus:outline-hidden min-h-[380px] p-6 text-slate-800 dark:text-slate-100 font-sans leading-relaxed text-sm sm:text-base border-none"
        ></div>

        <!-- Bottom Footer Metrics Bar -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 px-5 py-3 border-t bg-slate-50/60 dark:bg-zinc-900/60 border-slate-200 dark:border-zinc-800 text-xs text-slate-500 dark:text-zinc-400">
            <!-- Left: Real-time Word & Character Metrics -->
            <div class="flex items-center gap-4 font-mono text-[11px]">
                <span>Words: <strong class="text-slate-900 dark:text-white">{{ wordCount }}</strong></span>
                <span>Characters: <strong class="text-slate-900 dark:text-white">{{ characterCount }}</strong></span>
                <span class="flex items-center gap-1 text-sky-600 dark:text-sky-400 font-sans font-bold">
                    ⏱️ {{ readTimeMinutes }} min read
                </span>
            </div>

            <!-- Right: Auto-Save Status Badge -->
            <div class="flex items-center gap-2">
                <span v-if="autoSaveStatus === 'saving'" class="text-sky-600 dark:text-sky-400 font-semibold flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-sky-500 animate-ping"></span>
                    Saving draft...
                </span>
                <span v-else-if="autoSaveStatus === 'saved'" class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                    ✓ Saved to draft <span v-if="lastSavedAt" class="text-[10px] text-slate-400">({{ lastSavedAt }})</span>
                </span>
                <span v-else class="text-slate-400 text-[11px]">
                    Press Ctrl+S to save a version snapshot
                </span>
            </div>
        </div>
    </div>
</template>
