<script setup lang="ts">
defineProps<{
    compact?: boolean;
    inverted?: boolean;
}>();
</script>

<template>
    <span class="inline-flex items-center gap-2.5 select-none">
        <svg
            :class="compact ? 'h-8 w-8' : 'h-9 w-9'"
            viewBox="0 0 40 40"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <rect
                width="40"
                height="40"
                rx="9"
                :fill="inverted ? '#ffffff' : '#2563eb'"
            />
            <path
                d="M11 15.5L20 10.5L29 15.5V24.5L20 29.5L11 24.5V15.5Z"
                :stroke="inverted ? '#172b46' : '#ffffff'"
                stroke-width="2"
                stroke-linejoin="round"
            />
            <path
                d="M11 15.5L20 20.5L29 15.5"
                :stroke="inverted ? '#2563eb' : '#93c5fd'"
                stroke-width="2"
                stroke-linejoin="round"
            />
            <path
                d="M20 20.5V29.5"
                :stroke="inverted ? '#2563eb' : '#93c5fd'"
                stroke-width="2"
                stroke-linecap="round"
            />
            <path
                d="M24.5 13L15.5 18"
                :stroke="inverted ? '#172b46' : '#ffffff'"
                stroke-width="1.8"
                stroke-linecap="round"
            />
        </svg>
        <span class="flex flex-col leading-none">
            <span
                class="text-[17px] font-bold tracking-[-0.03em]"
                :class="
                    inverted
                        ? 'text-white'
                        : 'text-[#18202c] dark:text-[#f4f4f5]'
                "
            >
                Pache
            </span>
            <span
                class="mt-0.5 text-[9.5px] font-bold tracking-[0.18em] uppercase"
                :class="
                    inverted
                        ? 'text-[#93c5fd]'
                        : 'text-[#2563eb] dark:text-[#60a5fa]'
                "
            >
                LOGISTIC
            </span>
        </span>
    </span>
</template>
