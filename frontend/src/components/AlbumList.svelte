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
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
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
        margin-top: 4em;
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
