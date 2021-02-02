<script lang="ts">
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
            const album: Album = await res.json();
            const aspects = album.photos.map(p => p.aspect);
            layout = justifiedLayout(aspects, {
                containerWidth: 1200,
            });
            return album;
        }
        else {
            const err = await res.json();
            throw new Error(err);
        }
    }

    let layout = null;
    $: albumPromise = getAlbum();
</script>

{#await albumPromise then album}
    <h2>{album.title}</h2>
    <div class="albumPhotos" style="height: {layout.containerHeight}px; width: {1200}px;">
        {#each album.photos as photo, pi}
            <AlbumPhoto photo={photo} size="medium" dims={layout.boxes[pi]} />
        {/each}
    </div>
{/await}

<style>
    .albumPhotos {
        position: relative;
        margin-left: auto;
        margin-right: auto;
    }

    h2 {
        font-size: 3em;
        padding-left: 20px;
    }
</style>
