<script lang="ts">
    import { getContext, onMount, createEventDispatcher } from "svelte";

    import type { Photo, Album } from "../pozzo.type";
    import { albumContextMenuKey } from "../keys";
    import { RunApi } from "../api";
    import NewAlbumPrompt from "./NewAlbumPrompt.svelte";

    const context = getContext(albumContextMenuKey);

    async function getAlbumList() {
        const res = await RunApi("/album/list", {authorize: true});
        if (res.success) {
            albumList = res.data;
        }
        else {
            console.error(res);
        }
    }

    const dispatch = createEventDispatcher();

    function newAlbumMove(evt: CustomEvent) {
        newAlbumPromptShowing = false;
        handleAlbumMove(evt.detail.newAlbumID);
    }

    async function handleAlbumMove(targetAlbum: (Album|number)) {
        let targetID: number;
        if (typeof targetAlbum === "number") {
            targetID = targetAlbum;
        }
        else {
            targetID = targetAlbum.id;
        }
        for (let p of photos) {
            const copyRes = await RunApi("/photo/copy", {
                authorize: true,
                method: "POST",
                params: {
                    photoID: p.id,
                    albumID: targetID
                }
            });
            if (!copyRes.success) {
                console.error("Couldn't copy photo to album.", copyRes);
                continue;
            }
            const removeRes = await RunApi("/album/remove", {
                authorize: true,
                method: "POST",
                params: {
                    photoID: p.id,
                    albumID: currentAlbum.id
                }
            });
            if (!removeRes.success) {
                console.error("Couldn't remove photo from album.", removeRes);
                continue;
            }
        }
        dispatch("done");
    }

    async function handleDelete() {
        for (let p of photos) {
            const delRes = await RunApi("/photo/delete", {
                authorize: true,
                method: "POST",
                params: {
                    photoID: p.id
                }
            });
            if (!delRes.success) {
                console.error("Could not delete photo", delRes);
            }
        }
        dispatch("done");
    }

    onMount(() => {
        getAlbumList();
        posX = (context as any).clickLocation[0];
        posY = (context as any).clickLocation[1];
        photos = (context as any).getSelectedPhotos();
    });

    let posX: number = 0;
    let posY: number = 0;
    let photos: Photo[] = [];
    export let currentAlbum: Album;
    let albumList: Album[] = [];
    let albumListShown = false;
    let newAlbumPromptShowing = false;

</script>

{#if newAlbumPromptShowing}
    <NewAlbumPrompt
        on:dismissed={() => newAlbumPromptShowing = false}
        on:done={newAlbumMove}
    />
{/if}

<div
    class="contextMenu"
    style={`left: ${posX}px; top: ${posY}px`}
    >
    <div class="header menu-item">{photos.length} photo{photos.length == 1 ? "" : "s"} selected</div>
    <div class="move menu-item"
        on:click={() => albumListShown = !albumListShown}
    >
        {#if !albumListShown}
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="96 48 176 128 96 208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
        {:else}
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="208 96 128 176 48 96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
        {/if}
        <div class="label">Move To</div>
    </div>
    {#if albumListShown}
        <div class="album menu-item"
            on:click={() => newAlbumPromptShowing = true}
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></circle><line x1="88" y1="128" x2="168" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="128" y1="88" x2="128" y2="168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
            New Album…
        </div>
        {#each albumList as album}
            {#if album.id != currentAlbum.id}
                <div class="album menu-item"
                    on:click={() => handleAlbumMove(album)}
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><rect x="32" y="48" width="192" height="160" rx="8" stroke-width="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" fill="none"></rect><path d="M32,167.99982l50.343-50.343a8,8,0,0,1,11.31371,0l44.68629,44.6863a8,8,0,0,0,11.31371,0l20.68629-20.6863a8,8,0,0,1,11.31371,0L223.99982,184" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path><circle cx="156" cy="100" r="16"></circle></svg>
                    <div class="label">{album.title}</div>
                </div>
            {/if}
        {/each}
    {/if}
    <div class="delete menu-item"
        on:click={() => handleDelete()}
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><line x1="215.99609" y1="60" x2="39.99609" y2="60.00005" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="104" y1="104" x2="104" y2="168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="152" y1="104" x2="152" y2="168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><path d="M199.99609,60.00005V208a8,8,0,0,1-8,8h-128a8,8,0,0,1-8-8v-148" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path><path d="M168,60V36a16,16,0,0,0-16-16H104A16,16,0,0,0,88,36V60" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path></svg>        <div class="label">Delete</div>
    </div>

</div>

<style>
    .contextMenu {
        background-color: rgb(71, 71, 71);
        border: 1px solid black;
        min-width: 150px;
        position: fixed;
        z-index: 200;

        cursor: pointer;
        -webkit-touch-callout: none;
          -webkit-user-select: none;
              -ms-user-select: none;
                  user-select: none;
    }

    .contextMenu svg {
        width: 20px;
        margin-right: 10px
    }

    .menu-item {
        padding: 5px 10px 5px 5px;
        border-bottom: 1px solid black;
        display: flex;
        align-items: center;
    }

    .menu-item .label {
        flex-grow: 2;
    }

    .menu-item:hover {
        background-color: rgb(110, 110, 110);
    }

    .menu-item.header {
        font-weight: bold;
        border-bottom: 3px solid black;
        background-color: rgb(49, 49, 49);
    }

    .menu-item.delete {
        background-color: rgb(172, 75, 75);
    }

    .menu-item.delete:hover {
        background-color: rgb(170, 17, 17);
    }

    .menu-item.album {
        background-color: rgb(49, 49, 49);;
        padding-left: 20px;
    }
    .menu-item.album:hover {
        background-color: rgb(110, 110, 110);;
    }
</style>
