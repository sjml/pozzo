<script lang="ts">
    import { onMount } from "svelte";
    import { Route } from "tinro";
    import { router } from "tinro";

    import { currentAlbumStore, currentPhotoStore, fullScreen, siteData } from "../stores";
    import { RunApi } from "../api";
    import NavBar from "./NavBar.svelte";
    import SetupPage from "./SetupPage.svelte";
    import AlbumList from "./AlbumList.svelte";
    import NotFound from "./NotFound.svelte";

    onMount(setup);

    async function setup() {
        const res = await RunApi("/info");
        if (res.success) {
            Object.assign($siteData, res.data);
            $siteData.siteTitle = $siteData.siteTitle;
            if ($siteData.siteTitle == false) {
                router.goto("/setup");
            }
        }
        else {
            console.error(res);
        }

        document.addEventListener("fullscreenchange", () => {
            $fullScreen = (document.fullscreenElement != null);
        });
    }

    function setFullscreen(on: boolean) {
        if (on) {
            document.documentElement.requestFullscreen();
        }
        else {
            document.exitFullscreen();
        }
    }

    let title = "Pozzo";
    $: {
        const st = $siteData.siteTitle
        if (typeof st === "string") {
            title = st;

            if ($currentAlbumStore) {
                title += ` | ${$currentAlbumStore.title}`;

                if ($currentPhotoStore) {
                    title += ` | ${$currentPhotoStore.title}`;
                }
            }
        }
    }
</script>

<svelte:head>
    <title>
        {title}
    </title>
</svelte:head>

<div class="container">
    <NavBar on:fullScreenOn={() => setFullscreen(true)} on:fullScreenOff={() => setFullscreen(false)} />

    <Route path="/*" firstmatch>
        <Route path="/setup">
            <SetupPage />
        </Route>

        <Route path="/album/:albumSlug/*" let:meta>
            {#await import("./AlbumIndex.svelte")}
                Loading…
            {:then {default: component}}
                <svelte:component this={component} albumSlug={meta.params.albumSlug} />
            {/await}
        </Route>

        <Route path="/">
            <AlbumList />
        </Route>

        <Route fallback>
            <NotFound />
        </Route>
    </Route>

    {#if $siteData.promo && $currentPhotoStore == null}
        <div class="promo">
            <a href="https://github.com/sjml/pozzo" target="_">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="20" height="20" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                Pozzo Gallery
            </a>
        </div>
    {/if}
</div>

<style>
    .container {
        height: 100vh;
        padding-right: 5px;

        display: grid;
        grid-template-columns: 1fr auto;
        grid-template-rows: auto 1fr;
    }

    .promo {
        grid-row: 3;
        grid-column: 1 / span 2;
        padding: 10px;

        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .promo a {
        opacity: 0.2;
        transition-property: opacity;
        transition-duration: 200ms;

        display: flex;
        align-items: center;
    }

    .promo a:hover {
        opacity: 0.8;
    }

    .promo svg {
        margin-right: 5px;
    }
</style>
