<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    CalendarDays,
    CheckCircle2,
    GraduationCap,
    Images,
    MapPin,
    Menu,
    Moon,
    Newspaper,
    School,
    Sun,
    Users,
    X,
} from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Button from '@/components/ui/button/Button.vue';
import { useAppearance } from '@/composables/useAppearance';
import { dashboard, login, register } from '@/routes';

interface Konten {
    id: number;
    judul: string;
    slug: string;
    ringkasan: string | null;
    konten?: string | null;
    thumbnail?: string | null;
    tanggal_publish?: string | null;
    tanggal_event?: string | null;
    jam_mulai?: string | null;
    lokasi?: string | null;
}
interface GalleryItem {
    id: number;
    gambar: string;
    caption: string | null;
}
interface Statistics {
    siswa: number;
    guru: number;
    kelas: number;
    tema: number;
}
interface SchoolSetting {
    nama_sekolah: string;
    logo_url: string | null;
    tagline: string | null;
    alamat: string | null;
    nomor_telepon: string | null;
    email: string | null;
    visi: string | null;
    misi: string | null;
    tentang: string | null;
    sejarah_singkat: string | null;
    pendaftaran_dibuka: boolean;
}

const page = usePage();
const hero = computed(
    () => page.props.hero as Pick<Konten, 'judul' | 'thumbnail'> | null,
);
const profile = computed(() => page.props.profile as Konten | null);
const announcements = computed(() => page.props.announcements as Konten[]);
const news = computed(() => page.props.news as Konten[]);
const events = computed(() => page.props.events as Konten[]);
const galleryItems = computed(() => page.props.galleryItems as GalleryItem[]);
const statistics = computed(() => page.props.statistics as Statistics);
const school = computed(() => page.props.school as SchoolSetting);
const authUser = computed(() => page.props.auth.user as { id: number } | null);
const currentPath = computed(() => page.url.split('?')[0]);
const mobileOpen = ref(false);
const selectedImage = ref<GalleryItem | null>(null);
const activeSection = ref('beranda');
const isThemeReady = ref(false);
const { resolvedAppearance, updateAppearance } = useAppearance();
const displayedAppearance = computed(() =>
    isThemeReady.value ? resolvedAppearance.value : 'light',
);
let sectionObserver: IntersectionObserver | undefined;
const storageUrl = (path: string | null | undefined) =>
    path ? `/storage/${path}` : null;
const date = (value: string | null | undefined) =>
    value
        ? new Date(value).toLocaleDateString('id-ID', {
              day: 'numeric',
              month: 'long',
              year: 'numeric',
          })
        : '-';
const closeMenu = () => {
    mobileOpen.value = false;
};
const navItems = [
    { key: 'beranda', label: 'Beranda', href: '#beranda' },
    { key: 'profil', label: 'Profil Sekolah', href: '#profil' },
    { key: 'berita', label: 'Berita', href: '#berita' },
    { key: 'event', label: 'Event', href: '#event' },
    { key: 'galeri', label: 'Galeri', href: '#galeri' },
    { key: 'pengumuman', label: 'Pengumuman', href: '#pengumuman' },
];
const isNavActive = (key: string) => {
    if (currentPath.value === '/') {
        return activeSection.value === key;
    }

    return key !== 'beranda' && currentPath.value.startsWith(`/${key}`);
};
const setActiveSection = (key: string) => {
    activeSection.value = key;
    closeMenu();
};
const toggleTheme = () => {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
};

onMounted(() => {
    isThemeReady.value = true;

    const sections = navItems
        .map((item) => document.getElementById(item.key))
        .filter((section): section is HTMLElement => section !== null);

    sectionObserver = new IntersectionObserver(
        (entries) => {
            const visible = entries
                .filter((entry) => entry.isIntersecting)
                .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

            if (visible?.target.id) {
                activeSection.value = visible.target.id;
            }
        },
        { rootMargin: '-25% 0px -60% 0px', threshold: [0.1, 0.4, 0.7] },
    );

    sections.forEach((section) => sectionObserver?.observe(section));
});

onBeforeUnmount(() => sectionObserver?.disconnect());
</script>

<template>
    <Head title="SIAKDTK - Sistem Informasi Akademik TK"
        ><meta
            name="description"
            content="Website resmi SIAKDTK untuk informasi sekolah, berita, kegiatan, pendaftaran siswa, dan layanan orang tua." /><meta
            property="og:title"
            content="SIAKDTK - Sistem Informasi Akademik TK" /><meta
            property="og:description"
            content="Informasi sekolah, kegiatan, dan pendaftaran siswa baru."
    /></Head>
    <div class="min-h-svh bg-background text-foreground">
        <header
            class="sticky top-0 z-40 border-b border-border bg-background/95 backdrop-blur"
        >
            <nav
                class="mx-auto flex h-18 max-w-7xl items-center justify-between px-4 sm:px-6"
            >
                <Link
                    :href="'#beranda'"
                    class="flex items-center gap-3"
                    @click="setActiveSection('beranda')"
                    ><span
                        class="flex size-10 items-center justify-center rounded-xl bg-primary text-primary-foreground"
                        ><img
                            v-if="school.logo_url"
                            :src="school.logo_url"
                            :alt="school.nama_sekolah"
                            class="size-7 rounded-lg object-contain" /><AppLogoIcon
                            v-else
                            class="size-6 fill-current" /></span
                    ><span
                        ><strong class="block leading-tight">{{
                            school.nama_sekolah
                        }}</strong
                        ><span class="text-xs text-muted-foreground">{{
                            school.tagline ?? 'Sistem Informasi Akademik TK'
                        }}</span></span
                    ></Link
                >
                <div class="hidden items-center gap-5 lg:flex">
                    <a
                        v-for="item in navItems"
                        :key="item.label"
                        :href="item.href"
                        class="relative rounded-lg px-1 py-2 text-sm font-medium transition-colors duration-200"
                        :class="
                            isNavActive(item.key)
                                ? 'font-semibold text-primary after:absolute after:inset-x-1 after:-bottom-0.5 after:h-0.5 after:rounded-full after:bg-primary'
                                : 'text-muted-foreground hover:text-primary'
                        "
                        :aria-current="
                            isNavActive(item.key) ? 'page' : undefined
                        "
                        @click="setActiveSection(item.key)"
                        >{{ item.label }}</a
                    >
                </div>
                <div class="hidden items-center gap-2 sm:flex">
                    <button
                        type="button"
                        class="inline-flex size-9 items-center justify-center rounded-lg border border-border bg-card text-foreground transition-colors hover:bg-muted focus-visible:ring-3 focus-visible:ring-ring/50 focus-visible:outline-none"
                        :aria-label="
                            displayedAppearance === 'dark'
                                ? 'Gunakan mode terang'
                                : 'Gunakan mode gelap'
                        "
                        :title="
                            displayedAppearance === 'dark'
                                ? 'Gunakan mode terang'
                                : 'Gunakan mode gelap'
                        "
                        @click="toggleTheme"
                    >
                        <Sun
                            v-if="displayedAppearance === 'light'"
                            class="size-4"
                        />
                        <Moon v-else class="size-4" />
                    </button>
                    <Button as-child variant="outline"
                        ><Link :href="authUser ? dashboard() : login()">{{
                            authUser ? 'Dashboard' : 'Login'
                        }}</Link></Button
                    ><Button as-child
                        ><Link :href="register()">Daftar Siswa</Link></Button
                    >
                </div>
                <button
                    class="rounded-lg p-2 text-foreground hover:bg-muted sm:hidden"
                    type="button"
                    :aria-expanded="mobileOpen"
                    aria-label="Buka navigasi"
                    @click="mobileOpen = !mobileOpen"
                >
                    <X v-if="mobileOpen" class="size-5" /><Menu
                        v-else
                        class="size-5"
                    />
                </button>
            </nav>
            <div
                v-if="mobileOpen"
                class="border-t border-border bg-card px-4 py-4 sm:hidden"
            >
                <div class="flex flex-col gap-3">
                    <a
                        v-for="item in navItems"
                        :key="item.label"
                        :href="item.href"
                        class="rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                        :class="
                            isNavActive(item.key)
                                ? 'bg-secondary font-semibold text-secondary-foreground'
                                : 'text-foreground hover:bg-muted'
                        "
                        :aria-current="
                            isNavActive(item.key) ? 'page' : undefined
                        "
                        @click="setActiveSection(item.key)"
                        >{{ item.label }}</a
                    ><button
                        type="button"
                        class="inline-flex h-10 items-center justify-between rounded-lg border border-border bg-card px-3 text-sm font-semibold text-foreground focus-visible:ring-3 focus-visible:ring-ring/50 focus-visible:outline-none"
                        :aria-label="
                            displayedAppearance === 'dark'
                                ? 'Gunakan mode terang'
                                : 'Gunakan mode gelap'
                        "
                        :title="
                            displayedAppearance === 'dark'
                                ? 'Gunakan mode terang'
                                : 'Gunakan mode gelap'
                        "
                        @click="toggleTheme"
                    >
                        <span>{{
                            displayedAppearance === 'dark'
                                ? 'Mode Gelap'
                                : 'Mode Terang'
                        }}</span>
                        <Sun
                            v-if="displayedAppearance === 'light'"
                            class="size-4"
                        />
                        <Moon v-else class="size-4" /></button
                    ><Button as-child variant="outline" @click="closeMenu"
                        ><Link :href="authUser ? dashboard() : login()">{{
                            authUser ? 'Dashboard' : 'Login'
                        }}</Link></Button
                    ><Button as-child class="mt-2"
                        ><Link :href="register()">Daftar Siswa</Link></Button
                    >
                </div>
            </div>
        </header>
        <main>
            <section id="beranda" class="scroll-mt-20">
                <div
                    class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-[1fr_.9fr] lg:items-center lg:py-24"
                >
                    <div>
                        <p
                            class="mb-4 inline-flex items-center gap-2 rounded-full bg-secondary px-3 py-1 text-sm font-semibold text-secondary-foreground"
                        >
                            <School class="size-4" /> Website Resmi Sekolah
                        </p>
                        <h1
                            class="max-w-3xl text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl"
                        >
                            Membangun generasi cerdas, kreatif, dan berkarakter.
                        </h1>
                        <p
                            class="mt-6 max-w-2xl text-lg leading-8 text-muted-foreground"
                        >
                            SIAKDTK hadir untuk mendampingi perjalanan belajar
                            anak melalui lingkungan sekolah yang hangat, aman,
                            dan menyenangkan.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <Button as-child size="lg"
                                ><Link :href="register()"
                                    >Daftar Sekarang
                                    <ArrowRight class="size-4" /></Link></Button
                            ><Button as-child size="lg" variant="outline"
                                ><a href="#pengumuman"
                                    >Lihat Informasi</a
                                ></Button
                            >
                        </div>
                        <div
                            class="mt-10 flex flex-wrap gap-x-6 gap-y-3 text-sm text-muted-foreground"
                        >
                            <span class="flex items-center gap-2"
                                ><CheckCircle2 class="size-4 text-primary" />
                                Pendaftaran online</span
                            ><span class="flex items-center gap-2"
                                ><CheckCircle2 class="size-4 text-primary" />
                                Informasi kegiatan terkini</span
                            >
                        </div>
                    </div>
                    <div
                        class="relative overflow-hidden rounded-3xl border border-border bg-muted shadow-xl shadow-primary/10"
                    >
                        <img
                            v-if="hero?.thumbnail"
                            :src="storageUrl(hero.thumbnail) ?? ''"
                            :alt="hero.judul"
                            class="aspect-4/3 w-full object-cover"
                            fetchpriority="high"
                        />
                        <div
                            v-else
                            class="flex aspect-4/3 flex-col items-center justify-center bg-primary p-8 text-center text-primary-foreground"
                        >
                            <School class="size-16" />
                            <p class="mt-5 text-xl font-semibold">
                                Belajar, Bermain, dan Bertumbuh Bersama
                            </p>
                            <p class="mt-2 text-sm text-primary-foreground/75">
                                Setiap hari adalah kesempatan baru untuk
                                berkembang.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            <section class="border-y border-border bg-muted/50">
                <div
                    class="mx-auto grid max-w-7xl grid-cols-2 gap-6 px-4 py-10 sm:px-6 md:grid-cols-4"
                >
                    <div
                        v-for="stat in [
                            {
                                label: 'Siswa Aktif',
                                value: statistics.siswa,
                                icon: Users,
                            },
                            {
                                label: 'Guru Aktif',
                                value: statistics.guru,
                                icon: School,
                            },
                            {
                                label: 'Kelas Aktif',
                                value: statistics.kelas,
                                icon: GraduationCap,
                            },
                            {
                                label: 'Tema Berjalan',
                                value: statistics.tema,
                                icon: Images,
                            },
                        ]"
                        :key="stat.label"
                        class="text-center"
                    >
                        <component
                            :is="stat.icon"
                            class="mx-auto mb-3 size-5 text-primary"
                        />
                        <p class="text-3xl font-bold">{{ stat.value }}</p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ stat.label }}
                        </p>
                    </div>
                </div>
            </section>
            <section id="profil" class="scroll-mt-20">
                <div
                    class="mx-auto grid max-w-7xl gap-8 px-4 py-18 sm:px-6 lg:grid-cols-[.8fr_1.2fr]"
                >
                    <div
                        class="rounded-3xl bg-primary p-8 text-primary-foreground"
                    >
                        <p
                            class="text-sm font-semibold tracking-widest text-primary-foreground/70 uppercase"
                        >
                            Tentang Sekolah
                        </p>
                        <h2 class="mt-4 text-3xl font-bold">
                            Tempat terbaik untuk memulai langkah kecil yang
                            bermakna.
                        </h2>
                        <p class="mt-5 leading-7 text-primary-foreground/80">
                            Kami menciptakan pengalaman belajar yang mendukung
                            rasa ingin tahu, kepercayaan diri, serta karakter
                            positif anak.
                        </p>
                    </div>
                    <article
                        class="rounded-3xl border border-border bg-card p-8 text-card-foreground shadow-sm"
                    >
                        <h2 class="text-2xl font-bold">
                            {{ profile?.judul ?? 'Profil Sekolah' }}
                        </h2>
                        <p
                            v-if="profile?.ringkasan"
                            class="mt-4 leading-7 text-muted-foreground"
                        >
                            {{ profile.ringkasan }}
                        </p>
                        <div
                            v-if="profile?.konten"
                            class="prose mt-5 max-w-none text-foreground"
                            v-html="profile.konten"
                        />
                        <div
                            v-else
                            class="mt-5 space-y-4 text-muted-foreground"
                        >
                            <p>
                                <strong class="text-foreground">Visi:</strong>
                                Menjadi sekolah yang menumbuhkan anak cerdas,
                                mandiri, kreatif, dan berkarakter baik.
                            </p>
                            <p>
                                <strong class="text-foreground">Misi:</strong>
                                Menyediakan pembelajaran yang aman,
                                menyenangkan, dan berpusat pada perkembangan
                                anak.
                            </p>
                        </div>
                    </article>
                </div>
            </section>
            <section id="pengumuman" class="scroll-mt-20 bg-muted/40">
                <div class="mx-auto max-w-7xl px-4 py-18 sm:px-6">
                    <div
                        class="mb-8 flex flex-wrap items-end justify-between gap-3"
                    >
                        <div>
                            <p class="text-sm font-semibold text-primary">
                                INFORMASI SEKOLAH
                            </p>
                            <h2 class="mt-2 text-3xl font-bold">
                                Pengumuman Terbaru
                            </h2>
                        </div>
                    </div>
                    <div
                        v-if="announcements.length"
                        class="grid gap-4 lg:grid-cols-5"
                    >
                        <article
                            v-for="announcement in announcements"
                            :key="announcement.id"
                            class="rounded-2xl border border-border bg-card p-5 text-card-foreground shadow-sm"
                        >
                            <p class="text-sm text-muted-foreground">
                                {{ date(announcement.tanggal_publish) }}
                            </p>
                            <h3 class="mt-3 leading-6 font-semibold">
                                {{ announcement.judul }}
                            </h3>
                            <p
                                class="mt-3 line-clamp-3 text-sm leading-6 text-muted-foreground"
                            >
                                {{
                                    announcement.ringkasan ??
                                    'Informasi terbaru dari sekolah.'
                                }}
                            </p>
                            <a
                                href="#kontak"
                                class="mt-4 inline-flex text-sm font-semibold text-primary underline underline-offset-4"
                                >Baca Selengkapnya</a
                            >
                        </article>
                    </div>
                    <div
                        v-else
                        class="rounded-2xl border border-border bg-card p-8 text-center text-muted-foreground"
                    >
                        <Newspaper class="mx-auto mb-3 size-7" />Belum ada
                        pengumuman terbaru dari sekolah.
                    </div>
                </div>
            </section>
            <section id="berita" class="scroll-mt-20">
                <div class="mx-auto max-w-7xl px-4 py-18 sm:px-6">
                    <div class="mb-8">
                        <p class="text-sm font-semibold text-primary">
                            CERITA SEKOLAH
                        </p>
                        <h2 class="mt-2 text-3xl font-bold">Berita Terbaru</h2>
                    </div>
                    <div
                        v-if="news.length"
                        class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"
                    >
                        <article
                            v-for="item in news"
                            :key="item.id"
                            class="overflow-hidden rounded-3xl border border-border bg-card text-card-foreground shadow-sm transition-transform hover:-translate-y-1"
                        >
                            <img
                                v-if="item.thumbnail"
                                :src="storageUrl(item.thumbnail) ?? ''"
                                :alt="item.judul"
                                class="aspect-video w-full object-cover"
                                loading="lazy"
                            />
                            <div
                                v-else
                                class="flex aspect-video items-center justify-center bg-secondary text-secondary-foreground"
                            >
                                <Newspaper class="size-9" />
                            </div>
                            <div class="p-6">
                                <p class="text-sm text-muted-foreground">
                                    {{ date(item.tanggal_publish) }}
                                </p>
                                <h3 class="mt-2 text-xl font-semibold">
                                    {{ item.judul }}
                                </h3>
                                <p
                                    class="mt-3 line-clamp-3 text-sm leading-6 text-muted-foreground"
                                >
                                    {{
                                        item.ringkasan ??
                                        'Baca informasi terbaru dari kegiatan sekolah.'
                                    }}
                                </p>
                                <Link
                                    :href="`/berita/${item.slug}`"
                                    class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-primary"
                                    >Baca Selengkapnya
                                    <ArrowRight class="size-4"
                                /></Link>
                            </div>
                        </article>
                    </div>
                    <div
                        v-else
                        class="rounded-2xl border border-border bg-card p-8 text-center text-muted-foreground"
                    >
                        <Newspaper class="mx-auto mb-3 size-7" />Belum ada
                        berita yang dipublikasikan.
                    </div>
                </div>
            </section>
            <section id="event" class="scroll-mt-20 bg-muted/40">
                <div class="mx-auto max-w-7xl px-4 py-18 sm:px-6">
                    <div class="mb-8">
                        <p class="text-sm font-semibold text-primary">
                            AGENDA SEKOLAH
                        </p>
                        <h2 class="mt-2 text-3xl font-bold">Event Mendatang</h2>
                    </div>
                    <div
                        v-if="events.length"
                        class="grid gap-5 md:grid-cols-2 lg:grid-cols-3"
                    >
                        <article
                            v-for="event in events"
                            :key="event.id"
                            class="rounded-3xl border border-border bg-card p-6 text-card-foreground shadow-sm"
                        >
                            <div
                                class="flex size-11 items-center justify-center rounded-xl bg-secondary text-secondary-foreground"
                            >
                                <CalendarDays class="size-5" />
                            </div>
                            <h3 class="mt-5 text-xl font-semibold">
                                {{ event.judul }}
                            </h3>
                            <p
                                class="mt-3 text-sm leading-6 text-muted-foreground"
                            >
                                {{
                                    event.ringkasan ??
                                    'Kegiatan sekolah untuk siswa dan keluarga.'
                                }}
                            </p>
                            <dl class="mt-5 space-y-2 text-sm">
                                <div
                                    class="flex items-center gap-2 text-muted-foreground"
                                >
                                    <CalendarDays class="size-4" />{{
                                        date(event.tanggal_event)
                                    }}<span v-if="event.jam_mulai"
                                        >·
                                        {{ event.jam_mulai.slice(0, 5) }}</span
                                    >
                                </div>
                                <div
                                    v-if="event.lokasi"
                                    class="flex items-center gap-2 text-muted-foreground"
                                >
                                    <MapPin class="size-4" />{{ event.lokasi }}
                                </div>
                            </dl>
                        </article>
                    </div>
                    <div
                        v-else
                        class="rounded-2xl border border-border bg-card p-8 text-center text-muted-foreground"
                    >
                        <CalendarDays class="mx-auto mb-3 size-7" />Belum ada
                        event mendatang.
                    </div>
                </div>
            </section>
            <section id="galeri" class="scroll-mt-20">
                <div class="mx-auto max-w-7xl px-4 py-18 sm:px-6">
                    <div class="mb-8">
                        <p class="text-sm font-semibold text-primary">
                            MOMEN KEBERSAMAAN
                        </p>
                        <h2 class="mt-2 text-3xl font-bold">Galeri Sekolah</h2>
                    </div>
                    <div
                        v-if="galleryItems.length"
                        class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"
                    >
                        <button
                            v-for="image in galleryItems"
                            :key="image.id"
                            type="button"
                            class="group overflow-hidden rounded-2xl border border-border bg-muted text-left"
                            @click="selectedImage = image"
                        >
                            <img
                                :src="storageUrl(image.gambar) ?? ''"
                                :alt="
                                    image.caption ?? 'Galeri kegiatan sekolah'
                                "
                                class="aspect-square w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                loading="lazy"
                            />
                        </button>
                    </div>
                    <div
                        v-else
                        class="rounded-2xl border border-border bg-card p-8 text-center text-muted-foreground"
                    >
                        <Images class="mx-auto mb-3 size-7" />Belum ada foto
                        kegiatan yang dipublikasikan.
                    </div>
                </div>
            </section>
            <section class="mx-auto max-w-7xl px-4 py-18 sm:px-6">
                <div
                    class="rounded-3xl bg-primary px-6 py-12 text-center text-primary-foreground sm:px-12"
                >
                    <h2 class="text-3xl font-bold">
                        {{
                            school.pendaftaran_dibuka
                                ? 'Pendaftaran Siswa Baru Telah Dibuka'
                                : 'Pendaftaran Siswa Baru Sedang Ditutup'
                        }}
                    </h2>
                    <p
                        class="mx-auto mt-4 max-w-2xl text-primary-foreground/80"
                    >
                        Mari bergabung dan tumbuh bersama dalam lingkungan
                        belajar yang hangat serta menyenangkan.
                    </p>
                    <Button
                        v-if="school.pendaftaran_dibuka"
                        as-child
                        size="lg"
                        class="mt-7 bg-card text-card-foreground hover:bg-card/90"
                        ><Link :href="register()"
                            >Daftar Sekarang <ArrowRight class="size-4" /></Link
                    ></Button>
                    <p v-else class="mt-5 text-sm text-primary-foreground/80">
                        Silakan hubungi pihak sekolah untuk informasi
                        pendaftaran berikutnya.
                    </p>
                </div>
            </section>
        </main>
        <footer id="kontak" class="border-t border-border bg-card">
            <div
                class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-2"
            >
                <div class="flex gap-3">
                    <span
                        class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary text-primary-foreground"
                        ><img
                            v-if="school.logo_url"
                            :src="school.logo_url"
                            :alt="school.nama_sekolah"
                            class="size-7 rounded-lg object-contain" /><AppLogoIcon
                            v-else
                            class="size-6 fill-current"
                    /></span>
                    <div>
                        <h2 class="font-semibold">{{ school.nama_sekolah }}</h2>
                        <p
                            class="mt-2 max-w-md text-sm leading-6 text-muted-foreground"
                        >
                            {{
                                school.tagline ??
                                'Sistem Informasi Akademik TK untuk mendukung'
                            }}
                            komunikasi sekolah dan keluarga.
                        </p>
                    </div>
                </div>
                <div class="text-sm text-muted-foreground md:text-right">
                    <p>
                        Alamat dan kontak sekolah dapat dikelola melalui
                        informasi resmi sekolah.
                    </p>
                    <p class="mt-3">
                        © {{ new Date().getFullYear() }} SIAKDTK. Seluruh hak
                        cipta dilindungi.
                    </p>
                </div>
            </div>
        </footer>
        <div
            v-if="selectedImage"
            class="fixed inset-0 z-50 flex items-center justify-center bg-foreground/70 p-4"
            role="dialog"
            aria-modal="true"
            @click.self="selectedImage = null"
        >
            <div
                class="max-h-full max-w-4xl overflow-hidden rounded-2xl border border-border bg-card p-3"
            >
                <img
                    :src="storageUrl(selectedImage.gambar) ?? ''"
                    :alt="selectedImage.caption ?? 'Galeri sekolah'"
                    class="max-h-[75vh] w-full rounded-xl object-contain"
                />
                <p
                    v-if="selectedImage.caption"
                    class="p-3 text-sm text-card-foreground"
                >
                    {{ selectedImage.caption }}
                </p>
            </div>
        </div>
    </div>
</template>
