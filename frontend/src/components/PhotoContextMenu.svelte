<script lang="ts">
    import { getContext, onMount, createEventDispatcher } from "svelte";

    import type { Photo, Album } from "../pozzo.type";
    import { albumContextMenuKey } from "../keys";
    import { RunApi } from "../api";

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

    async function handleAlbumMove(targetAlbum: Album) {
        for (let p of photos) {
            const copyRes = await RunApi("/photo/copy", {
                authorize: true,
                method: "POST",
                params: {
                    photoID: p.id,
                    albumID: targetAlbum.id
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

</script>

<div
    class="contextMenu"
    style={`left: ${posX}px; top: ${posY}px`}
    >
    <div class="header menu-item">{photos.length} photo{photos.length == 1 ? "" : "s"} selected</div>
    <div class="move menu-item"
        on:click={() => albumListShown = !albumListShown}
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"></path></svg>
        <div class="label">Move To</div>
        {#if !albumListShown}
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        {:else}
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        {/if}
    </div>
    {#if albumListShown}
        <!-- <div class="album menu-item">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Move to New Album…
        </div> -->
        {#each albumList as album}
            {#if album.id != currentAlbum.id}
                <div class="album menu-item"
                    on:click={() => handleAlbumMove(album)}
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    <div class="label">{album.title}</div>
                </div>
            {/if}
        {/each}
    {/if}
    <div class="delete menu-item"
        on:click={() => handleDelete()}
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        <div class="label">Delete</div>
    </div>

</div>

<style>
    .contextMenu {
        background-color: rgb(71, 71, 71);
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
