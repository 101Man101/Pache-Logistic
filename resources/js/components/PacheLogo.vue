<script setup lang="ts">
import { useId } from 'vue';

defineProps<{
    compact?: boolean;
    inverted?: boolean;
}>();

const clipId = `pache-crest-${useId()}`;
</script>

<template>
    <span class="inline-flex items-center gap-2.5 select-none">
        <svg
            :class="compact ? 'h-9 w-9' : 'h-10 w-10'"
            viewBox="0 0 64 64"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <defs>
                <clipPath :id="clipId">
                    <circle cx="32" cy="32" r="28" />
                </clipPath>
            </defs>

            <!-- Corporate Navy Crest Badge -->
            <circle
                cx="32"
                cy="32"
                r="29.5"
                fill="#172b46"
                :stroke="inverted ? '#60a5fa' : '#2563eb'"
                stroke-width="2.2"
            />

            <g :clip-path="`url(#${clipId})`">
                <!-- Radiant Cobalt Sun Disc Halo -->
                <circle cx="23" cy="28" r="19" fill="#2563eb" />

                <!-- Aerodynamic Sweeping Headdress Crown Plumes -->
                <path
                    d="M21 18.6C23.8 9.6 31.4 4.6 39.8 3.6C36.8 9.6 33.4 15.2 29.8 20.6Z"
                    fill="#ffffff"
                />
                <path
                    d="M24.8 19.2C29.4 10.8 38.2 6.8 47.2 7.2C42.6 12.4 37.8 17.2 32.8 21.8Z"
                    fill="#93c5fd"
                />
                <path
                    d="M28.4 20.8C34.8 13.8 44.4 11.2 53.6 13C47.6 17.4 41.6 21.2 35.6 24.4Z"
                    fill="#ffffff"
                />
                <path
                    d="M31.6 23.4C39.2 18.2 48.8 17.2 57.8 20.6C51 24 44.2 26.2 37.6 28Z"
                    fill="#93c5fd"
                />
                <path
                    d="M33.8 27C42.2 23.8 51.4 24.2 59.4 29C52 31.2 45 32.2 38.4 32.8Z"
                    fill="#ffffff"
                />
                <path
                    d="M34.8 31.2C43 29.8 51.6 31.6 58.4 37.6C51 38.4 44.4 38 38.2 37.2Z"
                    fill="#93c5fd"
                />
                <path
                    d="M34.4 35.8C41.8 36.2 49.2 39.4 54.6 46.2C47.8 45.6 41.8 43.8 36.8 41.4Z"
                    fill="#ffffff"
                />
                <path
                    d="M34.2 40.4C40.2 42.2 45.8 46.4 49.4 52.8C43.8 51.2 39 48.4 35.2 44.8Z"
                    fill="#93c5fd"
                />
                <path
                    d="M33.4 44.2C38.2 47.2 42.2 51.8 44.2 58C39.6 55.6 36 51.8 33.4 47.4Z"
                    fill="#60a5fa"
                />

                <!-- Tapered Fur Crown Trim Rim -->
                <path
                    d="M18.6 19.9C24 16 32.4 18 36.6 25.4C38.6 29 38.8 33.4 37 37.6C35.2 36.8 33.4 36.4 31.6 36.6C33 32.4 32.4 27.8 29.2 24.8C26.4 22 22 21.2 18.6 19.9Z"
                    fill="#dbeafe"
                    stroke="#172b46"
                    stroke-width="0.9"
                    stroke-linejoin="round"
                />

                <!-- Noble Left-Facing Chief Profile & Corporate Bust -->
                <path
                    d="M19.8 20.6C25.2 20.6 31.2 24.4 33.4 30.8C34.2 33.4 34 36.8 32.6 40.2C34 45.4 37.6 53.2 41.8 61.5H15.8C19 54.6 21.6 48.2 22 44.4C22.2 42.6 21 41.4 18.6 41C16.2 40.6 15.4 39.2 15.8 38C15.9 37.6 15.8 37.2 15.4 36.9C14.7 36.5 14.8 35.8 15.6 35.3C14.6 34.8 14.8 33.8 15.8 33.4L12.6 32.8C11.5 32.5 11.5 31.5 12.3 30.6L16.2 26.4C15.8 25.5 16.5 23.8 17.5 22.3C18.2 21.3 19 20.6 19.8 20.6Z"
                    fill="#ffffff"
                />

                <!-- Subtle Brow & Sculpted Eye Accent -->
                <path
                    d="M16.8 26.2C18.1 25.8 19.5 26 20.5 26.7"
                    stroke="#172b46"
                    stroke-width="1.25"
                    stroke-linecap="round"
                />
                <path
                    d="M17.5 28C18.3 27.5 19.3 27.6 19.9 28.1C19.2 28.5 18.2 28.5 17.5 28Z"
                    fill="#172b46"
                />

                <!-- Flush Corporate Gold Geometric Headband -->
                <path
                    d="M17.6 22.2C22 22.6 27.2 25 31.6 29L30.2 31.6C26 27.9 21 25.7 16.5 25C16.7 23.9 17.1 23 17.6 22.2Z"
                    fill="#d3a56b"
                />
                <path
                    d="M18.6 24.3L20.1 23L21.5 24.8M22.8 25.3L24.5 24.2L25.6 26.3M26.8 27L28.6 26.3L29.2 28.5"
                    stroke="#172b46"
                    stroke-width="0.95"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    fill="none"
                />

                <!-- Flowing Soft-Tipped Temple Plumes -->
                <path
                    d="M29.4 32.4C27.2 36.8 26.2 42 26.8 46.8C27.1 48.2 28.2 48.2 28.8 46.8C30.2 43.4 31 38.8 31.2 33.6Z"
                    fill="#d3a56b"
                    stroke="#172b46"
                    stroke-width="0.8"
                    stroke-linejoin="round"
                />
                <path
                    d="M31.6 32.6C30.8 37.8 31 43.4 32.8 48C33.4 49.4 34.4 49.2 34.8 47.6C35.6 43.8 35.4 38.8 34 33.6Z"
                    fill="#93c5fd"
                    stroke="#172b46"
                    stroke-width="0.8"
                    stroke-linejoin="round"
                />
                <path
                    d="M33.6 33C34.2 38 35.6 43 38 46.8C38.8 48 39.6 47.6 39.5 46C39.2 42 37.8 37.6 35.8 33.4Z"
                    fill="#d3a56b"
                    stroke="#172b46"
                    stroke-width="0.8"
                    stroke-linejoin="round"
                />

                <!-- Temple Rosette Medallion -->
                <circle
                    cx="31.2"
                    cy="31"
                    r="2.7"
                    fill="#172b46"
                    stroke="#ffffff"
                    stroke-width="1"
                />
                <circle cx="31.2" cy="31" r="1.2" fill="#d3a56b" />
            </g>
        </svg>

        <span class="flex flex-col leading-none">
            <span
                class="text-[17.5px] font-bold tracking-[-0.03em]"
                :class="
                    inverted
                        ? 'text-white'
                        : 'text-[#18202c] dark:text-[#f4f4f5]'
                "
            >
                Pache
            </span>
            <span
                class="mt-0.5 text-[9.5px] font-bold tracking-[0.2em] uppercase"
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
