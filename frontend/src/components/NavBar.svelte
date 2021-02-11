<script lang="ts">
    let collapsed = false;

    import { createEventDispatcher } from "svelte";

    import { Link } from "svelte-routing";
    import { frontendStateStore } from "../stores";
    import Login from "./Login.svelte";

    const dispatch = createEventDispatcher();
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
        {#if $frontendStateStore.prevPhotoLink.length == 0}
            <div class="photoTools left disabled">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M114.34277,229.65723l-96-96a8.003,8.003,0,0,1,0-11.31446l96-96A8.00065,8.00065,0,0,1,128,32V72h80a16.01833,16.01833,0,0,1,16,16v80a16.01833,16.01833,0,0,1-16,16H128v40a8.00066,8.00066,0,0,1-13.65723,5.65723Z"></path></svg>
            </div>
        {:else}
            <Link to={$frontendStateStore.prevPhotoLink}>
                <div class="photoTools left">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M114.34277,229.65723l-96-96a8.003,8.003,0,0,1,0-11.31446l96-96A8.00065,8.00065,0,0,1,128,32V72h80a16.01833,16.01833,0,0,1,16,16v80a16.01833,16.01833,0,0,1-16,16H128v40a8.00066,8.00066,0,0,1-13.65723,5.65723Z"></path></svg>
                </div>
            </Link>
        {/if}
        {#if $frontendStateStore.nextPhotoLink.length == 0}
            <div class="photoTools right disabled">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M132.93848,231.39062A8,8,0,0,1,128,224V184H48a16.01833,16.01833,0,0,1-16-16V88A16.01833,16.01833,0,0,1,48,72h80V32a8.00065,8.00065,0,0,1,13.65723-5.65723l96,96a8.003,8.003,0,0,1,0,11.31446l-96,96A8.002,8.002,0,0,1,132.93848,231.39062Z"></path></svg>
            </div>
        {:else}
            <Link to={$frontendStateStore.nextPhotoLink}>
                <div class="photoTools right">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M132.93848,231.39062A8,8,0,0,1,128,224V184H48a16.01833,16.01833,0,0,1-16-16V88A16.01833,16.01833,0,0,1,48,72h80V32a8.00065,8.00065,0,0,1,13.65723-5.65723l96,96a8.003,8.003,0,0,1,0,11.31446l-96,96A8.002,8.002,0,0,1,132.93848,231.39062Z"></path></svg>
                </div>
            </Link>
        {/if}
        <div class="photoTools metadata"
            class:toggled={$frontendStateStore.isMetadataOn}
            on:click={() => $frontendStateStore.isMetadataOn = !$frontendStateStore.isMetadataOn}
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M122.66459,25.8792,42.0101,42.0101,25.8792,122.66459a8,8,0,0,0,2.1878,7.22578L132.51977,234.34315a8,8,0,0,0,11.31371,0l90.50967-90.50967a8,8,0,0,0,0-11.31371L129.89037,28.067A8,8,0,0,0,122.66459,25.8792Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path><circle cx="84" cy="84" r="16"></circle></svg>
        </div>
    {/if}
    {#if $frontendStateStore.fullScreen}
        <div class="photoTools fullscreen"
            on:click={() => dispatch("fullScreenOff")}
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="208 96 160 96 160 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><polyline points="48 160 96 160 96 208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><polyline points="160 208 160 160 208 160" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><polyline points="96 48 96 96 48 96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
        </div>
    {:else}
        <div class="photoTools fullscreen"
            on:click={() => dispatch("fullScreenOn")}
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="168 48 208 48 208 88" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="152" y1="104" x2="208" y2="48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="88 208 48 208 48 168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="104" y1="152" x2="48" y2="208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="208 168 208 208 168 208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="152" y1="152" x2="208" y2="208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="48 88 48 48 88 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="104" y1="104" x2="48" y2="48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
        </div>
    {/if}

    <div class="login">
        <Login />
    </div>
</nav>


<style>
    nav {
        grid-row: 1;
        grid-column: 1 / span 2;

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

    .photoTools.disabled {
        color: rgb(73, 73, 73);
        cursor: default;
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
