<script lang="ts">
    import { onMount } from "svelte";

    import justifiedLayout from "justified-layout";

    import type{ Album } from "./pozzo.type";
    import AlbumPhoto from "./AlbumPhoto.svelte";

    async function getAlbum(): Promise<Album> {
        const res = await fetch(
            `${location.origin}/api/album/view/1`,
            {
                body: JSON.stringify({previews: 1}),
                method: "POST",
            }
        );
        if (res.ok) {
            album = await res.json();
            return album;
        }
        else {
            const err = await res.json();
            console.error(err);
            // throw new Error(err);
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
