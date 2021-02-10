<script lang="ts">
    let collapsed = false;

    import { Link } from "svelte-routing";
    import { frontendStateStore } from "../stores";
    import Login from "./Login.svelte";
</script>


<nav
    on:dblclick|self={() => { collapsed = !collapsed; return false; }}
    class:collapsed
>
    <div class="homeLink">
        <Link to="/">
            Pozzo
        </Link>
    </div>

    {#if $frontendStateStore.backLinkText.length > 0}
        <div class="backLink">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <Link to={$frontendStateStore.backLink}>
                {$frontendStateStore.backLinkText}
            </Link>
        </div>
    {/if}

    <div class="spacer" />

    {#if $frontendStateStore.photoToolsVisible}
        <div class="photoTools left">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
        </div>
        <div class="photoTools right">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="photoTools metadata"
            class:toggled={$frontendStateStore.isMetadataOn}
            on:click={() => $frontendStateStore.isMetadataOn = !$frontendStateStore.isMetadataOn}
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
        </div>
    {/if}
    <div class="photoTools fullscreen">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
    </div>

    <div class="login">
        <Login />
    </div>
</nav>


<style>
    nav {
        width: 100%;

        background-color: rgb(32, 32, 32);
        font-size: large;

        display: flex;
        flex-direction: row;
        align-items: center;

        -webkit-touch-callout: none;
          -webkit-user-select: none;
              -ms-user-select: none;
                  user-select: none;

    }

    nav.collapsed {
        height: 5px;
        background-color: rgb(71, 71, 71);
    }

    nav.collapsed * {
        display: none;
    }

    nav div {
        display: flex;
    }

    .homeLink {
        margin: 5px;
        font-weight: bolder;
        font-size: 1.4em;
    }
    .backLink {
        margin: auto;
        height: 100%;
        font-size: 1.1em;
        display: flex;
        align-items: center;
    }

    .photoTools {
        cursor: pointer;
    }

    .photoTools.toggled {
        color: rgb(62, 62, 218);
    }

    .photoTools svg {
        margin-left: 5px;
    }

    .login {
        margin-left: 45px;
        margin-right: 5px;
    }

    svg {
        width: 30px;
        height: 30px;
        margin: auto;
    }

    .spacer {
        flex-grow: 2;
    }
</style>
