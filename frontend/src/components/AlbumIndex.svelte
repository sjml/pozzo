<script lang="ts">
    import { onDestroy } from "svelte";
    import { Route, navigateTo, router } from "yrv";

    import { currentAlbumStore, isLoggedInStore } from "../stores";
    import { RunApi } from "../api";

    export let albumSlug: string;

    onDestroy(() => {
        $currentAlbumStore = null;
    });

    $: {
        if ($currentAlbumStore && $currentAlbumStore.isPrivate && !$isLoggedInStore) {
            navigateTo("/");
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
                navigateTo("/");
            }
            else {
                console.error(res);
            }
        }
    }
    $: getAlbum(albumSlug)
</script>

{#if $currentAlbumStore}
    <Route exact
        component={() => import("./AlbumPage.svelte")}
        on:uploaded={() => getAlbum(albumSlug) }
    />

    <Route path="/:photoID"
        component={() => import("./PhotoPage.svelte")}
        photoIdentifier={$router.params.photoID}
    >
    </Route>
{/if}

