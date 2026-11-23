<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Palette, ShieldCheck, User } from '@lucide/vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profil Akun',
        href: editProfile(),
        icon: User,
    },
    {
        title: 'Keamanan & 2FA',
        href: editSecurity(),
        icon: ShieldCheck,
    },
    {
        title: 'Tampilan',
        href: editAppearance(),
        icon: Palette,
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="space-y-6">
        <div>
            <p class="pache-eyebrow">PENGATURAN AKUN</p>
            <h1 class="pache-page-title">
                Profil, Keamanan &amp; Preferensi Workspace
            </h1>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[210px_1fr]">
            <aside>
                <nav
                    class="flex flex-col space-y-1"
                    aria-label="Pengaturan Akun"
                >
                    <Link
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        :href="item.href"
                        class="pache-nav-link"
                        :class="{
                            'is-active': isCurrentOrParentUrl(item.href),
                        }"
                    >
                        <component
                            :is="item.icon"
                            class="h-[17px] w-[17px] shrink-0"
                        />
                        <span>{{ item.title }}</span>
                    </Link>
                </nav>
            </aside>

            <section class="pache-panel max-w-2xl p-6">
                <div class="space-y-10">
                    <slot />
                </div>
            </section>
        </div>
    </div>
</template>
