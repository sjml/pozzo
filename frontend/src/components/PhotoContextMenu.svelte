<script lang="ts">
    import { getContext, onMount, createEventDispatcher } from "svelte";

    import type { Photo, Album } from "../pozzo.type";
    import { RunApi } from "../api";
    import { albumContextMenuKey } from "../keys";
    import NewAlbumPrompt from "./NewAlbumPrompt.svelte";
    import Button from "./Button.svelte";


    const context = getContext(albumContextMenuKey);
    const dispatch = createEventDispatcher();

    export let currentAlbum: Album;


    onMount(() => {
        getAlbumList();
        posX = (context as any).clickLocation[0];
        posY = (context as any).clickLocation[1];
        photos = (context as any).getSelectedPhotos();
    });


    let posX: number = 0;
    let posY: number = 0;
    let photos: Photo[] = [];

    let albumList: Album[] = [];
    let albumListShown = false;
    let newAlbumPromptShowing = false;

    async function getAlbumList() {
        const res = await RunApi("/album/list", {authorize: true});
        if (res.success) {
            albumList = res.data;
        }
        else {
            console.error(res);
        }
    }


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
        const copyRes = await RunApi("/photo/copy", {
            authorize: true,
            method: "POST",
            params: {
                copies: photos.map(p => ({photoID: p.id, albumID: targetID}))
            }
        });
        if (!copyRes.success) {
            console.error("Couldn't copy all photos to target albums.", copyRes);
            return;
        }
        const removeRes = await RunApi("/album/remove", {
            authorize: true,
            method: "POST",
            params: {
                removals: photos.map(p => ({photoID: p.id, albumID: currentAlbum.id}))
            }
        });
        if (!removeRes.success) {
            console.error("Couldn't remove photos from target albums.", removeRes);
            return;
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

    async function handleCoverPhoto() {
        let target = photos[0].id;
        if (target == currentAlbum.coverPhoto) {
            target = -1;
        }
        const res = await RunApi(`/album/edit/${currentAlbum.id}`, {
            authorize: true,
            method: "POST",
            params: {
                coverPhoto: target
            }
        });
        if (res.success) {
            currentAlbum.coverPhoto = target;
        }
        else {
            console.error(res);
        }
        dispatch("done");
    }
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
    <div class="header menuItem">{photos.length} photo{photos.length == 1 ? "" : "s"} selected</div>
    {#if photos.length == 1}
        <Button on:click={handleCoverPhoto}>
            <div class="cover menuItem">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="200 176 127.993 136 56 176" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><path d="M200,224l-72.0074-40L56,224V40a8,8,0,0,1,8-8H192a8,8,0,0,1,8,8Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path></svg>
                <div>{currentAlbum.coverPhoto == photos[0].id ? "Unset" : "Set"} as Cover Photo</div>
            </div>
        </Button>
    {/if}
    <Button on:click={() => albumListShown = !albumListShown}>
        <div class="move menuItem">
            {#if !albumListShown}
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="96 48 176 128 96 208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
            {:else}
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="208 96 128 176 48 96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
            {/if}
            <div class="label">Move To</div>
        </div>
    </Button>
    {#if albumListShown}
        <div class="album menuItem"
            on:click={() => newAlbumPromptShowing = true}
        >
            <Button>
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></circle><line x1="88" y1="128" x2="168" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="128" y1="88" x2="128" y2="168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
                <div class="label">New Album…</div>
            </Button>
        </div>
        {#each albumList as album}
            {#if album.id != currentAlbum.id}
                <Button on:click={() => handleAlbumMove(album)}>
                    <div class="album menuItem">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><rect x="32" y="48" width="192" height="160" rx="8" stroke-width="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" fill="none"></rect><path d="M32,167.99982l50.343-50.343a8,8,0,0,1,11.31371,0l44.68629,44.6863a8,8,0,0,0,11.31371,0l20.68629-20.6863a8,8,0,0,1,11.31371,0L223.99982,184" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path><circle cx="156" cy="100" r="16"></circle></svg>
                        <div class="label">{album.title}</div>
                    </div>
                </Button>
            {/if}
        {/each}
    {/if}
    <Button on:click={() => handleDelete()}>
        <div class="delete menuItem">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><line x1="215.99609" y1="60" x2="39.99609" y2="60.00005" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="104" y1="104" x2="104" y2="168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="152" y1="104" x2="152" y2="168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><path d="M199.99609,60.00005V208a8,8,0,0,1-8,8h-128a8,8,0,0,1-8-8v-148" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path><path d="M168,60V36a16,16,0,0,0-16-16H104A16,16,0,0,0,88,36V60" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path></svg>
            <div class="label">Delete</div>
        </div>
    </Button>
</div>


<style>
    .contextMenu {
        position: fixed;
        min-width: 150px;

        background-color: rgb(71, 71, 71);
        border: 1px solid black;
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

    .menuItem {
        width: 100%;

        padding: 0 10px 0 5px;
        border-bottom: 1px solid black;

        display: flex;
        align-items: center;
    }

    .menuItem .label {
        flex-grow: 2;
    }

    .menuItem:hover {
        background-color: rgb(110, 110, 110);
    }

    .menuItem.header {
        padding-top: 5px;
        padding-bottom: 5px;
        font-weight: bold;
        border-bottom: 3px solid black;
        background-color: rgb(49, 49, 49);

        cursor: default;
    }

    .menuItem.delete {
        background-color: rgb(172, 75, 75);
    }

    .menuItem.delete:hover {
        background-color: rgb(170, 17, 17);
    }

    .menuItem.album {
        background-color: rgb(49, 49, 49);;
        padding-left: 20px;
    }
    .menuItem.album:hover {
        background-color: rgb(110, 110, 110);;
    }
</style>
