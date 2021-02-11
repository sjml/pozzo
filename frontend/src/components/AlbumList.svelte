<script lang="ts">
    import { onMount } from "svelte";

    import { Link, navigate } from "svelte-routing";

    import { RunApi } from "../api";
    import type { Album } from "../pozzo.type";
    import { isLoggedInStore } from "../stores";
    import NewAlbumPrompt from "./NewAlbumPrompt.svelte";
    import UploadZone from "./UploadZone.svelte";

    async function getAlbumList(_) {
        const res = await RunApi("/album/list", {authorize: true});
        if (res.success) {
            albumList = res.data;
        }
        else {
            console.error(res);
        }
    }
    let albumList: Album[];
    let addingNew = false;

    onMount(() => {
        getAlbumList(null);
    });

    function onUploadDone(evt: CustomEvent) {
        if (evt.detail.numFiles > 0) {
            navigate("/album/Unsorted");
        }
    }

    $: getAlbumList($isLoggedInStore)
</script>

{#if $isLoggedInStore}
    <UploadZone on:done={onUploadDone} />
{/if}

<div class="albumList">
    {#if addingNew}
        <NewAlbumPrompt
            on:dismissed={() => addingNew = false}
            on:done={() => {addingNew = false; getAlbumList(null);}}
        />
    {/if}

    <div class="header">
        <h2>Albums</h2>
        {#if $isLoggedInStore}
            <div class="addAlbum" title="Add Album" on:click={() => addingNew = true}>
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></circle><line x1="88" y1="128" x2="168" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="128" y1="88" x2="128" y2="168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
            </div>
        {/if}
    </div>
    {#if albumList}
        {#each albumList as album}
            <div class="navAlbum">
                <Link to={`album/${album.slug}`}>
                    {album.title}
                </Link>
            </div>
        {/each}
    {/if}
</div>

<style>
    .albumList {
        padding-left: 30px;
        margin-top: 1em;
    }
    h2 {
        margin: 0;
    }
    .navAlbum {
        margin: 10px;
        cursor: pointer;
    }

    .header {
        display: flex;
        align-items: center;
    }
    .addAlbum {
        width: 30px;
        margin-left: 10px;
        cursor: pointer;
        display: flex;
    }
</style>
