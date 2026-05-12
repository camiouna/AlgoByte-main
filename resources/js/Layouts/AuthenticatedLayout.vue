<script setup>
import { ref } from 'vue';
import { App, Link } from '@inertiajs/vue3';
import AuthBackgroundToggle from '@/Components/AuthBackgroundToggle.vue';

const showingNavigationDropdown = ref(false);
const showingAccountDropdown = ref(false);


</script>

<template>
    <div class="app-shell">
        <nav class="header">
            <Link :href="route('home')" class="header-title">
                <h1>AlgoByte</h1>
                <span class="edition">code lab</span>
            </Link>

            <div class="nav-bar">
                <ul>
                    <li>
                        <Link :href="route('home')" :class="{ active: route().current('home') }">
                            Home
                        </Link>
                    </li>
                   
                    <li>
                        <Link :href="route('problem-creation.index')" :class="{ active: route().current('problem-creation.index') }">
                            Problem Creation
                        </Link>
                    </li>
                    <li>
                        <Link :href="route('browse-problems.index')" :class="{ active: route().current('browse-problems.index') }">
                            Solve Problems
                        </Link>
                    </li>   
                    <li>
                        <AuthBackgroundToggle />
                    </li>
                </ul>

                <div class="account-menu">
                    <button
                        type="button"
                        class="account-trigger"
                        @click="showingAccountDropdown = !showingAccountDropdown"
                    >
                        <span class="account-avatar">
                            {{ $page.props.auth.member.username.charAt(0).toUpperCase() }}
                        </span>
                        <span class="account-name">
                            {{ $page.props.auth.member.username }}
                        </span>
                        <span class="account-chevron">v</span>
                    </button>

                    <div v-if="showingAccountDropdown" class="account-dropdown">
                        <Link :href="route('profile.edit')" class="account-dropdown-link">
                            Profile
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="account-dropdown-link logout-link"
                        >
                            Logout
                        </Link>
                        <Link :href="route('history.index')" class="account-dropdown-link">
                        History
                        </Link>
                    </div>
                </div>
            </div>

            <button
                type="button"
                class="menu-button"
                @click="showingNavigationDropdown = !showingNavigationDropdown"
            >
                <span v-if="showingNavigationDropdown">Close</span>
                <span v-else>Menu</span>
            </button>
        </nav>

        <div v-if="showingNavigationDropdown" class="mobile-menu">
            <Link :href="route('home')" :class="{ active: route().current('home') }">
                Home
            </Link>
            <Link :href="route('editor')" :class="{ active: route().current('editor') }">
                Editor
            </Link>
            <Link :href="route('problem-creation.index')" :class="{ active: route().current('problem-creation.index') }">
                Problem Creation
            </Link>
            <Link :href="route('browse-problems.index')" :class="{ active: route().current('browse-problems.index') }">
                Solve Problems
            </Link>
            <a href="#footer">
                About
            </a>
            <div class="mobile-account">
                <p>{{ $page.props.auth.member.username }}</p>
                <Link :href="route('profile.edit')">
                    Profile
                </Link>
                <Link :href="route('logout')" method="post" as="button">
                    Logout
                </Link>
                <Link :href="route('history.index')">
                    History
                </Link>
            </div>
        </div>

        <header class="page-header" v-if="$slots.header">
            <div class="page-header-inner">
                <slot name="header" />
            </div>
        </header>

        <main class="app-main">
            <slot />
        </main>

        <footer id="footer" class="footer">
            <p class="title">Algobyte &copy; 2026</p>
            <p class="motto">"Coding the future, one byte at a time."</p>
        </footer>
    </div>
</template>

<style scoped>
.app-shell {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    /* Use the variable for background and text */
    background-color: var(--site-bg);
    color: var(--site-text);

    /* Update your gradient to use the opacity variable */
    background-image:
        linear-gradient(rgba(56, 217, 255, var(--grid-opacity)) 1px, transparent 1px),
        linear-gradient(90deg, rgba(56, 217, 255, var(--grid-opacity)) 1px, transparent 1px),
        radial-gradient(circle at 88% 8%, rgba(24, 242, 195, 0.1), transparent 24%);
    background-size: 28px 28px, 28px 28px, auto, auto;

    transition: background-color 0.3s ease, color 0.3s ease;
}

/* Also make the header adapt */
.header {
    position: sticky;
    top: 0;
    z-index: 40;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 32px;
    border-bottom: 1px solid var(--ab-border);
    background: rgba(5, 10, 16, 0.94);
    backdrop-filter: blur(18px);
}

.edition {
    display: block;
    margin-top: 4px;
    color: var(--ab-cyan);
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.nav-bar {
    display: flex;
    align-items: center;
    gap: 24px;
    min-width: 0;
}

.nav-bar ul {
    display: flex;
    gap: 25px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.nav-bar a,
.mobile-menu a {
    position: relative;
    color: var(--ab-muted);
    font-size: 15px;
    font-weight: 700;
    text-decoration: none;
    transition: color 0.2s ease;
}

.nav-bar a:hover,
.nav-bar a.active,
.mobile-menu a:hover,
.mobile-menu a.active {
    color: var(--ab-text);
}

.nav-bar ul a::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--ab-teal);
    box-shadow: 0 0 14px rgba(24, 242, 195, 0.7);
    transition: width 0.2s ease;
}

.nav-bar ul a:hover::after,
.nav-bar ul a.active::after {
    width: 100%;
}

.mobile-menu button,
.menu-button {
    padding: 8px 18px;
    border: 1px solid var(--ab-border);
    border-radius: 8px;
    background: rgba(11, 22, 34, 0.82);
    color: var(--ab-text);
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}

.menu-button:hover {
    border-color: var(--ab-border-strong);
    background: rgba(56, 217, 255, 0.12);
    color: var(--ab-cyan);
    transform: translateY(-2px);
}

.account-menu {
    position: relative;
}

.account-trigger {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    max-width: 220px;
    padding: 7px 12px 7px 8px;
    border: 1px solid var(--ab-border);
    border-radius: 8px;
    background: rgba(11, 22, 34, 0.86);
    color: var(--ab-text);
    cursor: pointer;
    transition: all 0.2s ease;
}

.account-trigger:hover {
    border-color: var(--ab-border-strong);
    background: rgba(56, 217, 255, 0.1);
    transform: translateY(-1px);
}

.account-avatar {
    display: inline-grid;
    width: 30px;
    height: 30px;
    place-items: center;
    border-radius: 7px;
    background: linear-gradient(135deg, var(--ab-cyan), var(--ab-teal));
    color: #041016;
    font-size: 13px;
    font-weight: 800;
}

.account-name {
    min-width: 0;
    overflow: hidden;
    color: var(--ab-text);
    font-size: 14px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.account-chevron {
    color: var(--ab-cyan);
    font-size: 12px;
    font-weight: 800;
    line-height: 1;
}

.account-dropdown {
    position: absolute;
    right: 0;
    top: calc(100% + 10px);
    z-index: 30;
    min-width: 180px;
    overflow: hidden;
    border: 1px solid var(--ab-border);
    border-radius: 8px;
    background: #08121d;
    box-shadow: var(--ab-shadow);
}

.account-dropdown-link {
    display: block;
    width: 100%;
    padding: 11px 14px;
    border: 0;
    background: transparent;
    color: var(--ab-text) !important;
    font-size: 14px;
    font-weight: 700;
    text-align: left;
    text-decoration: none;
    cursor: pointer;
}

.account-dropdown-link:hover {
    background: rgba(56, 217, 255, 0.1);
    color: var(--ab-cyan) !important;
}

.logout-link {
    border-top: 1px solid var(--ab-border);
}

.mobile-account {
    display: grid;
    gap: 10px;
    padding-top: 12px;
    border-top: 1px solid var(--ab-border);
}

.mobile-account p {
    margin: 0;
    color: var(--ab-cyan);
    font-size: 14px;
    font-weight: 800;
}

.menu-button,
.mobile-menu {
    display: none;
}

.page-header {
    border-bottom: 1px solid var(--ab-border);
    background: rgba(7, 17, 27, 0.8);
}

.page-header-inner {
    max-width: 1280px;
    margin: 0 auto;
    padding: 22px 24px;
}

.app-main {
    flex: 1;
}

.footer {
    padding: 24px 16px;
    border-top: 1px solid var(--ab-border);
    background: rgba(5, 8, 13, 0.9);
    color: var(--ab-text);
    text-align: center;
}

.footer .title {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
}

.footer .motto {
    margin: 5px 0 0;
    color: var(--ab-muted);
    font-size: 14px;
    font-style: italic;
    opacity: 0.85;
}

@media (max-width: 820px) {
    .header {
        padding: 16px 20px;
    }

    .header-title h1 {
        font-size: 26px;
    }

    .nav-bar {
        display: none;
    }

    .menu-button {
        display: inline-flex;
    }

    .mobile-menu {
        display: grid;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--ab-border);
        background: rgba(5, 8, 13, 0.96);
        color: var(--ab-text);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .mobile-menu a,
    .mobile-menu button {
        width: 100%;
        text-align: left;
    }

    .mobile-menu a.active {
        color: var(--ab-cyan);
    }
}
</style>
