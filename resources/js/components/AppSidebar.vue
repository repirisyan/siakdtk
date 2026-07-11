<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    BookOpen,
    LayoutGrid,
    School,
    Newspaper,
    CalendarDays,
    GraduationCap,
    UserRoundCheck,
    ClipboardCheck,
    NotebookPen,
    BadgeCheck,
    Wallet,
    UserCog,
    BookOpenCheck,
    Receipt,
    Settings,
    UserPlus,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as temaIndex } from '@/actions/App/Http/Controllers/TemaController';
import { index as siswaIndex } from '@/actions/App/Http/Controllers/SiswaController';
import { index as kelasIndex } from '@/actions/App/Http/Controllers/KelasController';
import { index as guruIndex } from '@/actions/App/Http/Controllers/GuruController';
import { index as jadwalIndex } from '@/actions/App/Http/Controllers/JadwalController';
import { index as absensiIndex } from '@/actions/App/Http/Controllers/AbsenController';
import { index as penilaianIndex } from '@/actions/App/Http/Controllers/PenilaianController';
import { index as raporIndex } from '@/actions/App/Http/Controllers/RaporController';
import { validationIndex as validasiRaporIndex } from '@/actions/App/Http/Controllers/RaporController';
import { index as userIndex } from '@/actions/App/Http/Controllers/UserController';
import { index as raporAnakIndex } from '@/actions/App/Http/Controllers/RaporAnakController';
import { index as sppIndex } from '@/actions/App/Http/Controllers/SppController';
import { index as tagihanSayaIndex } from '@/actions/App/Http/Controllers/TagihanSayaController';
import { index as kontenIndex } from '@/actions/App/Http/Controllers/KontenController';
import type { NavItem } from '@/types';

type RoleName =
    | 'Admin'
    | 'Staff Akademik'
    | 'Staff Administrasi'
    | 'Guru'
    | 'Kepsek'
    | 'Orangtua Siswa';

type SidebarMenuItem = NavItem & { roles: RoleName[] };

const page = usePage();
const currentUser = computed(
    () =>
        page.props.auth.user as {
            role?: { role_name?: string };
        } | null,
);
const menus: SidebarMenuItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
        roles: [
            'Admin',
            'Staff Akademik',
            'Staff Administrasi',
            'Guru',
            'Kepsek',
            'Orangtua Siswa',
        ],
    },
    {
        title: 'Kelola Tema',
        href: temaIndex(),
        icon: BookOpen,
        roles: ['Admin', 'Staff Akademik'],
    },
    {
        title: 'Kelola Data Kelas',
        href: kelasIndex(),
        icon: School,
        roles: ['Admin', 'Staff Akademik'],
    },
    {
        title: 'Kelola Konten',
        href: kontenIndex(),
        icon: Newspaper,
        roles: ['Admin', 'Staff Administrasi'],
    },
    {
        title: 'Kelola Jadwal',
        href: jadwalIndex(),
        icon: CalendarDays,
        roles: ['Admin', 'Staff Akademik', 'Guru'],
    },
    {
        title: 'Kelola Data Guru',
        href: guruIndex(),
        icon: UserRoundCheck,
        roles: ['Admin', 'Staff Akademik'],
    },
    {
        title: 'Kelola Data Siswa',
        href: siswaIndex(),
        icon: GraduationCap,
        roles: ['Admin', 'Staff Akademik'],
    },
    {
        title: 'Kelola Absen',
        href: absensiIndex(),
        icon: ClipboardCheck,
        roles: ['Admin', 'Staff Akademik', 'Guru'],
    },
    {
        title: 'Kelola Nilai',
        href: penilaianIndex(),
        icon: NotebookPen,
        roles: ['Admin', 'Staff Akademik', 'Guru'],
    },
    {
        title: 'Kelola Rapor',
        href: raporIndex(),
        icon: BookOpenCheck,
        roles: ['Admin', 'Staff Akademik', 'Guru'],
    },
    {
        title: 'Validasi Rapor',
        href: validasiRaporIndex(),
        icon: BadgeCheck,
        roles: ['Kepsek'],
    },
    {
        title: 'Kelola Pembayaran',
        href: sppIndex(),
        icon: Wallet,
        roles: ['Admin', 'Staff Administrasi', 'Kepsek'],
    },
    {
        title: 'Kelola User',
        href: userIndex(),
        icon: UserCog,
        roles: ['Admin'],
    },
    {
        title: 'Pengaturan Sekolah',
        href: '/pengaturan-sekolah',
        icon: Settings,
        roles: ['Admin'],
    },
    {
        title: 'Rapor Anak',
        href: raporAnakIndex(),
        icon: BookOpenCheck,
        roles: ['Orangtua Siswa'],
    },
    {
        title: 'Tagihan Saya',
        href: tagihanSayaIndex(),
        icon: Receipt,
        roles: ['Orangtua Siswa'],
    },
    {
        title: 'Tambah Anak',
        href: '/tambah-anak',
        icon: UserPlus,
        roles: ['Orangtua Siswa'],
    },
];

const mainNavItems = computed<NavItem[]>(() => {
    const role = currentUser.value?.role?.role_name as RoleName | undefined;

    if (!role) {
        return [];
    }

    return menus
        .filter((menu) => menu.roles.includes(role))
        .map(({ roles: _roles, ...menu }) => menu);
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader class="border-b border-sidebar-border/70 px-3 py-4">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter class="border-t border-sidebar-border/70 p-3">
            <!-- <NavFooter :items="footerNavItems" /> -->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
