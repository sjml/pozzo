<script lang="ts">
    async function getAlbum() {
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
    <h2>Album: {album.title}</h2>
    {#each album.photos as photo}
        <img src="{`${location.origin}/img/medium/${photo.hash}.jpg`}" alt="{photo.title}">
    {/each}
{/await}
