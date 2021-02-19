<script lang="ts">
    import { createEventDispatcher } from "svelte";
    import { Link } from "svelte-routing";

    import { currentAlbumStore, frontendStateStore, siteData } from "../stores";
    import Login from "./Login.svelte";
    import Button from "./Button.svelte";

    const dispatch = createEventDispatcher();
    let collapsed = false;
</script>


<nav
    on:dblclick|self={() => { collapsed = !collapsed; return false; }}
    class:collapsed
>
    <div class="homeLink">
        <Link to="/">
            {$siteData.siteTitle || "Pozzo"}
        </Link>
    </div>

    {#if $currentAlbumStore}
        <div class="backLink">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <Link to={`/album/${$currentAlbumStore.slug}`}>
                {$currentAlbumStore.title}
            </Link>
        </div>
    {/if}

    <div class="spacer" />

    {#if $frontendStateStore.photoToolsVisible}
        <Button
            title={$frontendStateStore.prevPhotoLink.length == 0 ? "(No Previous Photo)" : "Previous Photo"}
            margin="0 5px 0 0"
            isDisabled={$frontendStateStore.prevPhotoLink.length == 0}
        >
            <Link to={$frontendStateStore.prevPhotoLink}>
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M114.34277,229.65723l-96-96a8.003,8.003,0,0,1,0-11.31446l96-96A8.00065,8.00065,0,0,1,128,32V72h80a16.01833,16.01833,0,0,1,16,16v80a16.01833,16.01833,0,0,1-16,16H128v40a8.00066,8.00066,0,0,1-13.65723,5.65723Z"></path></svg>
            </Link>
        </Button>
        <Button
            title={$frontendStateStore.nextPhotoLink.length == 0 ? "(No Next Photo)" : "Next Photo"}
            margin="0 5px 0 0"
            isDisabled={$frontendStateStore.nextPhotoLink.length == 0}
        >
            <Link to={$frontendStateStore.nextPhotoLink}>
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M132.93848,231.39062A8,8,0,0,1,128,224V184H48a16.01833,16.01833,0,0,1-16-16V88A16.01833,16.01833,0,0,1,48,72h80V32a8.00065,8.00065,0,0,1,13.65723-5.65723l96,96a8.003,8.003,0,0,1,0,11.31446l-96,96A8.002,8.002,0,0,1,132.93848,231.39062Z"></path></svg>
            </Link>
        </Button>
        <Button margin="0 5px 0 0"
            title={`${$frontendStateStore.isMetadataOn ? "Hide" : "View"} Metadata`}
            isToggled={$frontendStateStore.isMetadataOn}
            on:click={() => $frontendStateStore.isMetadataOn = !$frontendStateStore.isMetadataOn}
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M122.66459,25.8792,42.0101,42.0101,25.8792,122.66459a8,8,0,0,0,2.1878,7.22578L132.51977,234.34315a8,8,0,0,0,11.31371,0l90.50967-90.50967a8,8,0,0,0,0-11.31371L129.89037,28.067A8,8,0,0,0,122.66459,25.8792Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path><circle cx="84" cy="84" r="16"></circle></svg>
        </Button>
    {/if}
    {#if $frontendStateStore.fullScreen}
        <Button margin="0 5px 0 0" on:click={() => dispatch("fullScreenOff")} title="Exit Fullscreen">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="208 96 160 96 160 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><polyline points="48 160 96 160 96 208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><polyline points="160 208 160 160 208 160" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><polyline points="96 48 96 96 48 96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
        </Button>
    {:else}
        <Button margin="0 5px 0 0" on:click={() => dispatch("fullScreenOn")} title="Fullscreen">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="168 48 208 48 208 88" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="152" y1="104" x2="208" y2="48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="88 208 48 208 48 168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="104" y1="152" x2="48" y2="208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="208 168 208 208 168 208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="152" y1="152" x2="208" y2="208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="48 88 48 48 88 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="104" y1="104" x2="48" y2="48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
        </Button>
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
        -webkit-touch-callout: none;
          -webkit-user-select: none;
              -ms-user-select: none;
                  user-select: none;

        display: flex;
        flex-direction: row;
        align-items: center;
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
        padding: 5px;
        font-weight: bolder;
        font-size: 1.4em;

        display: flex;
    }

    .backLink {
        margin: auto;
        height: 100%;

        font-size: 1.1em;

        display: flex;
        align-items: center;
    }

    .login {
        margin-left: 45px;
        margin-right: 5px;
    }

    .backLink svg {
        width: 30px;
        height: 30px;
        margin: auto;
    }

    .spacer {
        flex-grow: 2;
    }
</style>
