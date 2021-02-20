<script lang="ts">
    import { onDestroy } from "svelte";
    import { Route, router } from "tinro";

    import { currentAlbumStore, isLoggedInStore } from "../stores";
    import { RunApi } from "../api";
    import AlbumPage from "./AlbumPage.svelte";
    import PhotoPage from "./PhotoPage.svelte";

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
        <Route path="/:photoID" let:meta>
            <PhotoPage photoIdentifier={meta.params.photoID} />
        </Route>
        <Route path="/">
            <AlbumPage on:uploaded={() => getAlbum(albumSlug) } />
        </Route>
    </Route>
{/if}

<style>

</style>
