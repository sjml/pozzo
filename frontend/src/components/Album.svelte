<script lang="ts">
    import { onMount } from "svelte";

    import justifiedLayout from "justified-layout";

    import { RunApi } from "../api";
    import type{ Album } from "../pozzo.type";
    import AlbumPhoto from "./AlbumPhoto.svelte";


    export let identifier: number|string;

    async function getAlbum(): Promise<Album> {
        const res = await RunApi(`/album/view/${identifier}`, {
            params: {previews: 1},
            method: "POST"
        });
        if (res.success) {
            album = res.data;
            return album;
        }
        else {
            console.error(res);
        }
    }

    function calculateLayout(width: number) {
        if (!width || !album) {
            return; // initial loads; don't worry yet
        }
        const aspects = album.photos.map(p => p.aspect);
        layout = justifiedLayout(aspects, {
            targetRowHeight: 300,
            containerWidth: width,
            containerPadding: 10,
            widowLayoutStyle: "center",
        });
    }

    let containerWidth: number;
    let album: Album = null;
    let layout = null;
    $: calculateLayout(containerWidth);
    $: if (album) {calculateLayout(containerWidth);}


    onMount(getAlbum);
</script>

{#if album}
    <h2>{album.title}</h2>

    <div class="albumPhotos"
        bind:clientWidth={containerWidth}
        style={`height: ${layout?.containerHeight || 0}px;`}
    >
        {#if layout}
            {#each album.photos as photo, pi}
                <AlbumPhoto photo={photo} size="medium" dims={layout.boxes[pi]} />
            {/each}
        {/if}
    </div>
{/if}


<style>
    .albumPhotos {
        position: relative;
        max-width: 95%;
        height: 50px;
        margin-left: auto;
        margin-right: auto;
    }

    h2 {
        font-size: 3em;
        padding-left: 20px;
    }
</style>
