<script lang="ts">
    import { createEventDispatcher } from "svelte";
    import { Link, navigate } from "svelte-routing";

    import type { Photo, Album, PerusalList } from "../pozzo.type";
    import {
        currentAlbumStore,
        currentPerusalStore,
        fullScreen,
        siteData,
        metadataVisible
    } from "../stores";
    import Login from "./Login.svelte";
    import Button from "./Button.svelte";

    const dispatch = createEventDispatcher();

    let collapsed = false;
    let prevLink: string = null;
    let nextLink: string = null;

    function findNeighbors(pl: PerusalList, a: Album) {
        if (a == null || pl == null || pl.currentIdx < 0) {
            prevLink = null;
            nextLink = null;
            return;
        }
        if (pl.currentIdx == 0) {
            prevLink = null;
        }
        else {
            const prevNode = pl.nodes[pl.currentIdx - 1];
            prevLink = `/album/${a.slug}/${prevNode.hasOwnProperty("hash") ? "" : "g"}${prevNode.id}`;
        }
        if (pl.currentIdx == pl.nodes.length-1) {
            nextLink = null;
        }
        else {
            const nextNode = pl.nodes[pl.currentIdx + 1];
            nextLink = `/album/${a.slug}/${nextNode.hasOwnProperty("hash") ? "" : "g"}${nextNode.id}`;
        }
    }
    $: findNeighbors($currentPerusalStore, $currentAlbumStore)

    function handleKeyDown(evt: KeyboardEvent) {
        if (evt.key == "ArrowLeft" && prevLink != null) {
            navigate(prevLink);
        }
        else if (evt.key == "ArrowRight" && nextLink != null) {
            navigate(nextLink);
        }
    }
</script>

<svelte:window
    on:keydown={handleKeyDown}
/>

<nav
    on:dblclick|self={() => { collapsed = !collapsed; return false; }}
    class:collapsed
>
    <div class="fullBreadcrumbs">
        <div class="homeLink">
            <Link to="/">
                {$siteData.siteTitle || "Pozzo"}
            </Link>
        </div>

        {#if $currentAlbumStore}
            <div class="backLink">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                {#if $currentPerusalStore?.currentIdx >= 0}
                    <Link to={`/album/${$currentAlbumStore.slug}`}>
                        {$currentAlbumStore.title}
                    </Link>
                {:else}
                    {$currentAlbumStore.title}
                {/if}
            </div>
        {/if}

        {#if $currentPerusalStore?.currentIdx >= 0}
            <div class="backLink">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <!-- {$currentPhotoStore.title} -->
            </div>
        {/if}
    </div>

    <div class="backButton">
        <div class="backLink">
            {#if $currentPerusalStore?.currentIdx >= 0}
                <Link to={`/album/${$currentAlbumStore.slug}`}>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="160 208 80 128 160 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
                    {$currentAlbumStore.title}
                </Link>
            {:else if $currentAlbumStore}
                <Link to="/">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="160 208 80 128 160 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
                    {$siteData.siteTitle || "Pozzo"}
                </Link>
            {:else}
                <h2>{$siteData.siteTitle || "Pozzo"}</h2>
            {/if}
        </div>
    </div>

    <div class="spacer" />

    {#if $currentPerusalStore?.currentIdx >= 0}
        {#if prevLink == null}
            <Button
                title="(No Previous Photo)"
                margin="0 5px 0 0"
                isDisabled={true}
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M114.34277,229.65723l-96-96a8.003,8.003,0,0,1,0-11.31446l96-96A8.00065,8.00065,0,0,1,128,32V72h80a16.01833,16.01833,0,0,1,16,16v80a16.01833,16.01833,0,0,1-16,16H128v40a8.00066,8.00066,0,0,1-13.65723,5.65723Z"></path></svg>
            </Button>
        {:else}
            <Button
                title="Previous Photo"
                margin="0 5px 0 0"
                isDisabled={false}
            >
                <Link to={prevLink}>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M114.34277,229.65723l-96-96a8.003,8.003,0,0,1,0-11.31446l96-96A8.00065,8.00065,0,0,1,128,32V72h80a16.01833,16.01833,0,0,1,16,16v80a16.01833,16.01833,0,0,1-16,16H128v40a8.00066,8.00066,0,0,1-13.65723,5.65723Z"></path></svg>
                </Link>
            </Button>
        {/if}

        {#if nextLink == null}
            <Button
                title="(No Next Photo)"
                margin="0 5px 0 0"
                isDisabled={true}
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M132.93848,231.39062A8,8,0,0,1,128,224V184H48a16.01833,16.01833,0,0,1-16-16V88A16.01833,16.01833,0,0,1,48,72h80V32a8.00065,8.00065,0,0,1,13.65723-5.65723l96,96a8.003,8.003,0,0,1,0,11.31446l-96,96A8.002,8.002,0,0,1,132.93848,231.39062Z"></path></svg>
            </Button>
        {:else}
            <Button
                title="Next Photo"
                margin="0 5px 0 0"
                isDisabled={false}
            >
                <Link to={nextLink}>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M132.93848,231.39062A8,8,0,0,1,128,224V184H48a16.01833,16.01833,0,0,1-16-16V88A16.01833,16.01833,0,0,1,48,72h80V32a8.00065,8.00065,0,0,1,13.65723-5.65723l96,96a8.003,8.003,0,0,1,0,11.31446l-96,96A8.002,8.002,0,0,1,132.93848,231.39062Z"></path></svg>
                </Link>
            </Button>
        {/if}

        <div class="metaDataToggle">
            <Button margin="0 5px 0 0"
                title={`${$metadataVisible ? "Hide" : "View"} Metadata`}
                isToggled={$metadataVisible}
                on:click={() => $metadataVisible = !$metadataVisible}
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M122.66459,25.8792,42.0101,42.0101,25.8792,122.66459a8,8,0,0,0,2.1878,7.22578L132.51977,234.34315a8,8,0,0,0,11.31371,0l90.50967-90.50967a8,8,0,0,0,0-11.31371L129.89037,28.067A8,8,0,0,0,122.66459,25.8792Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path><circle cx="84" cy="84" r="16"></circle></svg>
            </Button>
        </div>
    {/if}
    <div class="fullscreenButton">
        {#if $fullScreen}
            <Button margin="0 5px 0 0" on:click={() => dispatch("fullScreenOff")} title="Exit Fullscreen">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="208 96 160 96 160 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><polyline points="48 160 96 160 96 208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><polyline points="160 208 160 160 208 160" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><polyline points="96 48 96 96 48 96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
            </Button>
        {:else}
            <Button margin="0 5px 0 0" on:click={() => dispatch("fullScreenOn")} title="Fullscreen">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="168 48 208 48 208 88" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="152" y1="104" x2="208" y2="48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="88 208 48 208 48 168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="104" y1="152" x2="48" y2="208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="208 168 208 208 168 208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="152" y1="152" x2="208" y2="208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="48 88 48 48 88 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="104" y1="104" x2="48" y2="48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
            </Button>
        {/if}
    </div>

    <div class="login">
        <Login />
    </div>
</nav>


<style>
    nav {
        grid-row: 1;
        grid-column: 1 / span 2;
        height: 40px;
        width: 100vw;
        padding-right: 10px;

        background-color: var(--navbar-color);
        font-size: large;
        -webkit-touch-callout: none;
          -webkit-user-select: none;
              -ms-user-select: none;
                  user-select: none;

        display: flex;
        flex-direction: row;
        align-items: center;

        transition-property: height, background-color;
        transition-duration: 600ms;
    }

    nav.collapsed {
        height: 7px;

        background-color: var(--navbar-collapsed-color);
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

    .backLink svg {
        width: var(--button-size);
        height: var(--button-size);
        margin: auto;
    }

    .backLink :global(a) {
        display: flex;
        align-items: center;
    }

    .login {
        margin-left: 45px;
        margin-right: 5px;
    }


    @media only screen and (max-device-width: 480px) {
        h2 {
            margin: 5px;
            font-size: larger;
        }

        .fullscreenButton {
            display: none;
        }

        .metaDataToggle {
            display: none;
        }

        .fullBreadcrumbs {
            display: none;
        }
    }

    @media only screen and (min-device-width: 480px) {
        .backButton {
            display: none;
        }
    }

    .spacer {
        flex-grow: 2;
    }
</style>
