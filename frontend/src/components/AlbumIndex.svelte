<script lang="ts">
    import { onDestroy } from "svelte";
    import { Router, Route, navigate } from "svelte-routing";

    import { currentAlbumStore, isLoggedInStore } from "../stores";
    import { RunApi } from "../api";
    import LazyLoad from "./LazyLoad.svelte";

    export let albumSlug: string;

    onDestroy(() => {
        $currentAlbumStore = null;
    });

    $: {
        if ($currentAlbumStore && $currentAlbumStore.isPrivate && !$isLoggedInStore) {
            navigate("/");
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
                navigate("/");
            }
            else {
                console.error(res);
            }
        }
    }
    $: getAlbum(albumSlug)
</script>

{#if $currentAlbumStore}
    <Router>
        <Route>
            <LazyLoad loader={"AlbumPage"}
                on:uploaded={() => getAlbum(albumSlug) }
                on:structuralChange={() => getAlbum(albumSlug)}
            />
        </Route>

        <Route path="/:photoID" let:params>
            <LazyLoad loader={"PhotoPage"} photoIdentifier={params.photoID} />
        </Route>
    </Router>
{/if}

