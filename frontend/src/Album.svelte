<script lang="ts">
    import type{ Album } from "./pozzo.type";

    import AlbumPhoto from "./AlbumPhoto.svelte";

    async function getAlbum(): Promise<Album> {
        const res = await fetch(`${location.origin}/api/album/view/1`);
        if (res.ok) {
            const album = await res.json();
            return album;
        }
        else {
            const err = await res.json();
            throw new Error(err);
        }
    }

    $: albumPromise = getAlbum();
</script>

{#await albumPromise then album}
    <h2>{album.title}</h2>
    <div class="albumPhotos">
        {#each album.photos as photo}
            <AlbumPhoto photo={photo} size="medium"/>
        {/each}
    </div>
{/await}

<style>
    .albumPhotos {
        display: flex;
        flex-wrap: wrap;
        margin-left: auto;
        margin-right: auto;
    }
</style>
