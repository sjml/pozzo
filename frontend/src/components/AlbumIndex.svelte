<script lang="ts">
    import { onDestroy } from "svelte";
    import { Router, Route, navigate } from "svelte-routing";

    import type { Album } from "../pozzo.type";
    import { currentAlbumStore, currentPerusalStore, isLoggedInStore } from "../stores";
    import { RunApi } from "../api";
    import LazyLoad from "./LazyLoad.svelte";

    export let albumSlug: string;

    onDestroy(() => {
        $currentPerusalStore = null;
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

    // TODO: should this be done with a derived store instead of here?
    async function updatePerusals(album: Album) {
        if (album == null) {
            $currentPerusalStore = null;
            return;
        }
        $currentPerusalStore = {
            currentIdx: -1,
            currentPhoto: null,
            currentGroup: null,
            photoSet: [],
            nodes: [],
        };
        album.photoGroups.forEach(pg => {
            if (pg.description.length > 0) {
                $currentPerusalStore.nodes = [...$currentPerusalStore.nodes, pg];
            }
            pg.photos.forEach(p => {
                $currentPerusalStore.nodes = [...$currentPerusalStore.nodes, p];
                $currentPerusalStore.photoSet = [...$currentPerusalStore.photoSet, p];
            })
        });
    }
    $: updatePerusals($currentAlbumStore)
</script>

{#if $currentAlbumStore}
    <Router>
        <Route>
            <LazyLoad loader={"AlbumPage"}
                on:uploaded={() => getAlbum(albumSlug) }
                on:structuralChange={() => getAlbum(albumSlug)}
                on:perusalChangeNeeded={() => updatePerusals($currentAlbumStore)}
            />
        </Route>

        <Route path="/:perusalIdentifier" let:params>
            <LazyLoad loader={"PerusalPage"} perusalIdentifier={params.perusalIdentifier} />
        </Route>
    </Router>
{/if}

