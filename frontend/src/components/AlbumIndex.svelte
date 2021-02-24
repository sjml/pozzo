<script lang="ts">
    import { onDestroy } from "svelte";
    import { Route, router } from "tinro";

    import { currentAlbumStore, isLoggedInStore } from "../stores";
    import { RunApi } from "../api";

    export let albumSlug: string;

    onDestroy(() => {
        $currentAlbumStore = null;
    });

    $: {
        if ($currentAlbumStore && $currentAlbumStore.isPrivate && !$isLoggedInStore) {
            router.goto("/");
        }
    }

    async function getAlbum(slug: string) {
        const res = await RunApi(`/album/view/${slug}`, {
            authorize: true
        });
        if (res.success) {
            $currentAlbumStore = res.data;
        }
        else {
            if (res.code == 404) {
                router.goto("/");
            }
            else {
                console.error(res);
            }
        }
    }
    $: getAlbum(albumSlug)
</script>

{#if $currentAlbumStore}
    <Route path="/*" firstmatch>
        <Route path="/">
            {#await import("./AlbumPage.svelte")}
                Loading…
            {:then {default: component}}
                <svelte:component this={component} on:uploaded={() => getAlbum(albumSlug) } />
            {/await}
        </Route>
        <Route path="/:photoID" let:meta>
            {#await import("./PhotoPage.svelte")}
                Loading…
            {:then {default: component}}
                <svelte:component this={component} photoIdentifier={meta.params.photoID} />
            {/await}
        </Route>
    </Route>
{/if}

