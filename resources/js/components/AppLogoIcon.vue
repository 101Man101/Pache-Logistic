<script setup lang="ts">
import { useId, type HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();

const clipId = `pache-icon-${useId()}`;
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 64 64"
        fill="none"
        :class="className"
        v-bind="$attrs"
    >
        <defs>
            <clipPath :id="clipId">
                <circle cx="32" cy="32" r="28" />
            </clipPath>
        </defs>

        <circle
            cx="32"
            cy="32"
            r="29.5"
            stroke="currentColor"
            stroke-width="2.6"
        />

        <g :clip-path="`url(#${clipId})`">
            <!-- Monochrome Sweeping Headdress Crown Plumes -->
            <path
                fill="currentColor"
                opacity="0.72"
                d="M21 18.6C23.8 9.6 31.4 4.6 39.8 3.6C36.8 9.6 33.4 15.2 29.8 20.6Z"
            />
            <path
                fill="currentColor"
                opacity="0.95"
                d="M24.8 19.2C29.4 10.8 38.2 6.8 47.2 7.2C42.6 12.4 37.8 17.2 32.8 21.8Z"
            />
            <path
                fill="currentColor"
                opacity="0.72"
                d="M28.4 20.8C34.8 13.8 44.4 11.2 53.6 13C47.6 17.4 41.6 21.2 35.6 24.4Z"
            />
            <path
                fill="currentColor"
                opacity="0.95"
                d="M31.6 23.4C39.2 18.2 48.8 17.2 57.8 20.6C51 24 44.2 26.2 37.6 28Z"
            />
            <path
                fill="currentColor"
                opacity="0.72"
                d="M33.8 27C42.2 23.8 51.4 24.2 59.4 29C52 31.2 45 32.2 38.4 32.8Z"
            />
            <path
                fill="currentColor"
                opacity="0.95"
                d="M34.8 31.2C43 29.8 51.6 31.6 58.4 37.6C51 38.4 44.4 38 38.2 37.2Z"
            />
            <path
                fill="currentColor"
                opacity="0.72"
                d="M34.4 35.8C41.8 36.2 49.2 39.4 54.6 46.2C47.8 45.6 41.8 43.8 36.8 41.4Z"
            />

            <!-- Noble Left-Facing Chief Silhouette & Hanging Plumes -->
            <path
                fill="currentColor"
                d="M19.8 20.6C25.2 20.6 31.2 24.4 33.4 30.8C34.2 33.4 34 36.8 32.6 40.2C34 45.4 37.6 53.2 41.8 61.5H15.8C19 54.6 21.6 48.2 22 44.4C22.2 42.6 21 41.4 18.6 41C16.2 40.6 15.4 39.2 15.8 38C15.9 37.6 15.8 37.2 15.4 36.9C14.7 36.5 14.8 35.8 15.6 35.3C14.6 34.8 14.8 33.8 15.8 33.4L12.6 32.8C11.5 32.5 11.5 31.5 12.3 30.6L16.2 26.4C15.8 25.5 16.5 23.8 17.5 22.3C18.2 21.3 19 20.6 19.8 20.6Z"
            />
            <path
                fill="currentColor"
                opacity="0.6"
                d="M31.6 32.6C30.8 37.8 31 43.4 32.8 48C33.4 49.4 34.4 49.2 34.8 47.6C35.6 43.8 35.4 38.8 34 33.6Z"
            />
        </g>
    </svg>
</template>
