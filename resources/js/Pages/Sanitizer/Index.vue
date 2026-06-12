<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 flex flex-col items-center justify-between p-6 relative overflow-hidden font-sans selection:bg-cyan-500 selection:text-slate-950 transition-colors duration-300">
        <Head>
            <title>{{ t('title') }} - Anonymous_Document_Sanitizer</title>
            <meta name="description" :content="t('description')" />
            <meta name="keywords" content="metadata cleaner, exif stripper, sanitize image, strip gps, zero storage, privacy cleaner, photo sanitizer, document cleaner" />
            <meta property="og:title" :content="t('title')" />
            <meta property="og:description" :content="t('description')" />
            <meta property="og:type" content="website" />
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="robots" content="index, follow" />
        </Head>

        <!-- Background Radial Glows -->
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[60%] rounded-full bg-cyan-500/5 dark:bg-cyan-900/10 blur-[120px] pointer-events-none transition-colors"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[50%] h-[60%] rounded-full bg-indigo-500/5 dark:bg-indigo-900/10 blur-[120px] pointer-events-none transition-colors"></div>

        <!-- Header -->
        <header class="w-full max-w-5xl flex justify-between items-center z-10 py-4">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-gradient-to-br from-cyan-400 to-indigo-600 shadow-lg shadow-cyan-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <span class="text-xl font-bold tracking-tight bg-gradient-to-r from-cyan-600 via-indigo-600 to-slate-900 dark:from-cyan-200 dark:via-indigo-200 dark:to-white bg-clip-text text-transparent font-outfit">
                    {{ t('appName') }}
                </span>
            </div>
            
            <div class="flex items-center gap-4 z-20">
                <!-- Zero-Persistence Tag -->
                <span class="hidden sm:inline px-3 py-1 rounded-full text-xs font-semibold bg-cyan-100/80 dark:bg-cyan-950/50 text-cyan-700 dark:text-cyan-400 border border-cyan-200/50 dark:border-cyan-800/50 backdrop-blur-md">
                    {{ t('tag') }}
                </span>

                <!-- Language Picker -->
                <div class="relative">
                    <select 
                        v-model="locale" 
                        class="appearance-none bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 px-3 py-1.5 pr-8 rounded-full text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-cyan-500 cursor-pointer backdrop-blur-md transition-colors"
                        id="sanitizer-lang-picker"
                    >
                        <option value="en">English</option>
                        <option value="es">Español</option>
                        <option value="fr">Français</option>
                        <option value="de">Deutsch</option>
                        <option value="zh">中文</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500 dark:text-slate-400">
                        <svg class="fill-current h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                        </svg>
                    </div>
                </div>

                <!-- Theme Toggle Button -->
                <button 
                    @click="toggleTheme" 
                    id="sanitizer-theme-toggle"
                    class="p-2 rounded-full bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer shadow-sm"
                    aria-label="Toggle Theme"
                >
                    <!-- Sun Icon (shown in dark theme) -->
                    <svg v-if="theme === 'dark'" xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 7a5 5 0 100 10 5 5 0 000-10z" />
                    </svg>
                    <!-- Moon Icon (shown in light theme) -->
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <!-- GitHub Link -->
                <a 
                    v-if="githubUrl"
                    :href="githubUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    id="sanitizer-github-link"
                    class="p-2 rounded-full bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer shadow-sm"
                    aria-label="GitHub Repository"
                >
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.17 6.839 9.49.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.464-1.11-1.464-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.137 20.167 22 16.418 22 12c0-5.523-4.477-10-10-10z" />
                    </svg>
                </a>
            </div>
        </header>

        <!-- Main Body -->
        <main class="w-full max-w-4xl flex-1 flex flex-col items-center justify-center z-10 py-12">
            <!-- App Card -->
            <div class="w-full bg-white/70 dark:bg-slate-900/40 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-8 md:p-12 shadow-[0_8px_30px_rgb(0,0,0,0.02)] dark:shadow-[0_0_50px_rgba(8,145,178,0.05)] transition-all duration-500">
                
                <div class="text-center mb-8">
                    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-3 font-outfit">
                        {{ t('title') }}
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 max-w-xl mx-auto text-sm md:text-base leading-relaxed">
                        {{ t('description') }}
                    </p>
                </div>

                <!-- Drag & Drop Area -->
                <div 
                    id="sanitizer-drag-zone"
                    class="relative border-2 border-dashed rounded-2xl p-8 md:p-12 text-center cursor-pointer transition-all duration-300 group overflow-hidden"
                    :class="[
                        isDragging 
                            ? 'border-cyan-500 bg-cyan-500/5 dark:border-cyan-400 dark:bg-cyan-950/20 text-cyan-600 dark:text-cyan-200 shadow-[0_0_30px_rgba(6,182,212,0.1)] dark:shadow-[0_0_30px_rgba(34,211,238,0.15)] scale-[1.01]' 
                            : 'border-slate-300 dark:border-slate-800 bg-slate-100/30 dark:bg-slate-950/40 text-slate-500 dark:text-slate-400 hover:border-slate-400 dark:hover:border-slate-700 hover:text-slate-600 dark:hover:text-slate-300'
                    ]"
                    @dragover="handleDragOver"
                    @dragleave="handleDragLeave"
                    @drop="handleDrop"
                    @click="triggerFileInput"
                >
                    <!-- Input element -->
                    <input 
                        type="file" 
                        id="sanitizer-file-input" 
                        class="hidden" 
                        accept="image/jpeg,image/jpg,image/png"
                        @change="handleFileSelect"
                        :disabled="isProcessing"
                    />

                    <!-- Glowing Hover Effect -->
                    <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/2 to-indigo-500/2 dark:from-cyan-500/5 dark:to-indigo-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

                    <!-- Inner content -->
                    <div class="flex flex-col items-center gap-4 relative z-10">
                        <div class="p-4 rounded-full bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 shadow-inner group-hover:scale-110 group-hover:border-slate-300 dark:group-hover:border-slate-700 transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-cyan-500 dark:text-cyan-400 group-hover:text-cyan-600 dark:group-hover:text-cyan-300 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-lg text-slate-800 dark:text-slate-200">
                                {{ t('dragText') }} <span class="text-cyan-600 dark:text-cyan-400 group-hover:text-cyan-500 group-hover:underline">{{ t('browse') }}</span>
                            </p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">
                                {{ t('subtext') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="fileError" id="sanitizer-error-message" class="mt-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-955/20 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-200 text-sm flex items-center gap-3 backdrop-blur-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-500 dark:text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ fileError }}</span>
                </div>

                <!-- Success Message -->
                <div v-if="successMessage" id="sanitizer-success-message" class="mt-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-955/20 border border-emerald-200 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-200 text-sm flex items-center gap-3 backdrop-blur-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ successMessage }}</span>
                </div>

                <!-- File Summary and Processing button -->
                <div v-if="selectedFile" id="sanitizer-file-summary" class="mt-6 p-5 rounded-2xl bg-slate-100/80 dark:bg-slate-955/80 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 transition-all duration-300">
                    <div class="flex items-center gap-4 w-full sm:w-auto">
                        <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-slate-800 dark:text-slate-200 truncate">{{ selectedFile.name }}</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500">{{ (selectedFile.size / 1024 / 1024).toFixed(2) }} MB</p>
                        </div>
                    </div>
                    
                    <button 
                        id="sanitizer-submit-button"
                        @click.stop="sanitizeFile"
                        :disabled="isProcessing"
                        class="w-full sm:w-auto px-6 py-3 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-cyan-500 to-indigo-600 hover:from-cyan-400 hover:to-indigo-500 shadow-lg shadow-cyan-500/20 active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer font-outfit"
                    >
                        <span v-if="isProcessing">{{ t('btnProcessing') }}</span>
                        <span v-else>{{ t('btnSanitize') }}</span>
                        <svg v-if="!isProcessing" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <!-- Progress Bar -->
                <div v-if="isProcessing" id="sanitizer-progress-container" class="mt-8 w-full bg-slate-200 dark:bg-slate-950 rounded-full h-2 overflow-hidden border border-slate-300 dark:border-slate-800">
                    <div 
                        id="sanitizer-progress-bar"
                        class="bg-gradient-to-r from-cyan-400 to-indigo-500 h-full rounded-full transition-all duration-300 shadow-[0_0_10px_rgba(34,211,238,0.5)]"
                        :style="{ width: progressPercent + '%' }"
                    ></div>
                </div>

            </div>
        </main>

        <!-- Compliance & Legal Copy -->
        <footer class="w-full max-w-5xl z-10 mt-12 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-t border-slate-200 dark:border-slate-900 pt-8 text-xs">
                <!-- Terms Container -->
                <div class="bg-slate-100/50 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-900 p-6 rounded-2xl backdrop-blur-md">
                    <h2 class="text-sm font-bold text-cyan-600/90 dark:text-cyan-400/80 mb-4 font-outfit uppercase tracking-wider">
                        {{ t('termsTitle') }}
                    </h2>
                    <ol class="space-y-3 list-decimal pl-4 leading-relaxed text-slate-600 dark:text-slate-400">
                        <li v-for="(item, i) in tm('terms')" :key="i">
                            <strong>{{ item.title }}:</strong> {{ item.text }}
                        </li>
                    </ol>
                </div>

                <!-- Privacy Container -->
                <div class="bg-slate-100/50 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-900 p-6 rounded-2xl backdrop-blur-md">
                    <h2 class="text-sm font-bold text-indigo-600/90 dark:text-indigo-400/80 mb-4 font-outfit uppercase tracking-wider">
                        {{ t('privacyTitle') }}
                    </h2>
                    <ul class="space-y-3 list-disc pl-4 leading-relaxed text-slate-600 dark:text-slate-400">
                        <li v-for="(item, i) in tm('privacy')" :key="i">
                            <strong>{{ item.title }}:</strong> {{ item.text }}
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="text-center text-[10px] text-slate-400 dark:text-slate-600 mt-8 flex flex-col sm:flex-row items-center justify-center gap-2">
                <span>&copy; {{ new Date().getFullYear() }} {{ t('appName') }}. {{ t('footerText') }}</span>
                <span v-if="githubUrl" class="hidden sm:inline text-slate-300 dark:text-slate-800">|</span>
                <a v-if="githubUrl" :href="githubUrl" target="_blank" rel="noopener noreferrer" class="hover:text-cyan-500 dark:hover:text-cyan-400 underline transition-colors">
                    {{ t('githubLinkText') }}
                </a>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

// i18n
const { t, tm, locale } = useI18n();

// GitHub URL from environment
const githubUrl = import.meta.env.VITE_GITHUB_URL || '';

// States
const isDragging = ref(false);
const isProcessing = ref(false);
const selectedFile = ref(null);
const fileError = ref(null);
const successMessage = ref('');
const progressPercent = ref(0);

const theme = ref('dark');

// Drag and drop handlers
const handleDragOver = (e) => {
    e.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = () => {
    isDragging.value = false;
};

const handleDrop = (e) => {
    e.preventDefault();
    isDragging.value = false;
    fileError.value = null;
    successMessage.value = '';
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        validateAndSetFile(files[0]);
    }
};

const handleFileSelect = (e) => {
    fileError.value = null;
    successMessage.value = '';
    const files = e.target.files;
    if (files.length > 0) {
        validateAndSetFile(files[0]);
    }
};

const validateAndSetFile = (file) => {
    const validMimes = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!validMimes.includes(file.type)) {
        fileError.value = t('errorUnsupportedType');
        selectedFile.value = null;
        return;
    }
    
    const maxSize = 10 * 1024 * 1024; // 10MB
    if (file.size > maxSize) {
        fileError.value = t('errorTooLarge');
        selectedFile.value = null;
        return;
    }
    
    selectedFile.value = file;
};

const sanitizeFile = async () => {
    if (!selectedFile.value) return;
    
    isProcessing.value = true;
    fileError.value = null;
    successMessage.value = '';
    progressPercent.value = 10;
    
    const formData = new FormData();
    formData.append('file', selectedFile.value);
    
    try {
        progressPercent.value = 30;
        const response = await axios.post('/sanitize', formData, {
            responseType: 'blob',
            headers: {
                'Content-Type': 'multipart/form-data'
            },
            onUploadProgress: (progressEvent) => {
                const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                progressPercent.value = Math.min(80, Math.max(10, Math.round(percentCompleted * 0.8)));
            }
        });
        
        progressPercent.value = 90;
        
        const originalName = selectedFile.value.name;
        
        // Parse raw binary blob response and trigger window download
        const blob = new Blob([response.data], { type: response.headers['content-type'] });
        const url = window.URL.createObjectURL(blob);
        
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', originalName);
        document.body.appendChild(link);
        link.click();
        
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        
        progressPercent.value = 100;
        successMessage.value = t('successMsg');
        selectedFile.value = null;
    } catch (error) {
        console.error(error);
        if (error.response && error.response.data instanceof Blob) {
            const text = await error.response.data.text();
            try {
                const json = JSON.parse(text);
                fileError.value = json.message || 'Verification or processing failed.';
            } catch (e) {
                fileError.value = 'An error occurred during file sanitization.';
            }
        } else {
            fileError.value = 'An error occurred during file sanitization.';
        }
    } finally {
        isProcessing.value = false;
        setTimeout(() => {
            progressPercent.value = 0;
        }, 1000);
    }
};

const triggerFileInput = () => {
    if (!isProcessing.value) {
        document.getElementById('sanitizer-file-input').click();
    }
};

// Theme Toggle logic
const initTheme = () => {
    if (typeof window !== 'undefined') {
        const hasDarkClass = document.documentElement.classList.contains('dark');
        const hasLightClass = document.documentElement.classList.contains('light');
        
        if (hasDarkClass) {
            theme.value = 'dark';
        } else if (hasLightClass) {
            theme.value = 'light';
        } else {
            // Default to system settings
            const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            theme.value = systemDark ? 'dark' : 'light';
            updateHtmlClass(theme.value);
        }
    }
};

const toggleTheme = () => {
    theme.value = theme.value === 'dark' ? 'light' : 'dark';
    updateHtmlClass(theme.value);
};

const updateHtmlClass = (t) => {
    if (typeof window !== 'undefined') {
        if (t === 'dark') {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
        } else {
            document.documentElement.classList.add('light');
            document.documentElement.classList.remove('dark');
        }
    }
};

onMounted(() => {
    initTheme();
});
</script>
