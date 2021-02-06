<script lang="ts">
    import { onMount } from "svelte";

    import { Link } from "svelte-routing";

    import { RunApi } from "../api";
    import type { Album } from "../pozzo.type";

    async function getAlbumList() {
        const res = await RunApi("/album/list", {authorize: true});
        if (res.success) {
            albumList = res.data;
        }
        else {
            console.error(res);
        }
    }
    let albumList: Album[];

    onMount(getAlbumList);
</script>

{#if albumList}
    <div class="albumList">
        <h2>Albums</h2>
        {#each albumList as album}
            <div class="navAlbum">
                <Link to={`album/${album.title}`}>
                    {album.title}
                </Link>
            </div>
        {/each}
    </div>
{/if}

<style>
    .albumList {
        padding-left: 30px;
    }
    .navAlbum {
        margin: 10px;
        cursor: pointer;
    }
</style>
