<script lang="ts">
    import { tick, createEventDispatcher } from "svelte";

    import type { Photo } from "../pozzo.type";
    import { currentAlbumStore, navSelection, isLoggedInStore } from "../stores";
    import { IsMetaKeyDownForEvent } from "../util";
    import LazyLoad from "./LazyLoad.svelte";

    const dispatch = createEventDispatcher();

    export let photos: Photo[] = [];

    let contextMenuX = -1;
    let contextMenuY = -1;
    let contextMenuVisible = false;
    async function handleRightClick(evt: MouseEvent) {
        if (!$isLoggedInStore) {
            return;
        }
        evt.preventDefault();
        if (contextMenuVisible) {
            contextMenuVisible = false;
            await tick();
        }

        contextMenuX = evt.clientX;
        contextMenuY = evt.clientY;
        contextMenuVisible = true;
    }


    function handleContextMenuDelete(evt: CustomEvent) {
        photos = photos.filter(s => $navSelection.indexOf(s) < 0);
        dispatch("deleted", {newPhotos: photos});
        contextMenuVisible = false;
        $navSelection = [];
    }

    function handleContextMenuMove(evt: CustomEvent) {
        photos = photos.filter(s => $navSelection.indexOf(s) < 0);
        dispatch("moved", {newPhotos: photos, targetAlbumID: evt.detail.targetAlbumID});
        contextMenuVisible = false;
        $navSelection = [];
    }

    function handleContextMenuCoverPhoto(evt: CustomEvent) {
        if ($currentAlbumStore.coverPhoto == $navSelection[0]) {
            $currentAlbumStore.coverPhoto = null;
        }
        else {
            $currentAlbumStore.coverPhoto = $navSelection[0];
        }
        dispatch("coverChanged");
        contextMenuVisible = false;
        $navSelection = [];
    }


    function handleKeyDown(evt: KeyboardEvent) {
        if (!$isLoggedInStore) {
            return;
        }
        if (contextMenuVisible) {
            return;
        }
        if (evt.key == "a" && IsMetaKeyDownForEvent(evt)) {
            $navSelection = photos;
            evt.preventDefault();
        }
        if (evt.key == "d" && IsMetaKeyDownForEvent(evt)) {
            $navSelection = [];
            evt.preventDefault();
        }
    }
</script>

<svelte:body
    on:contextmenu={handleRightClick}
    on:keydown={handleKeyDown}
/>

{#if contextMenuVisible}
    <LazyLoad loader={"PhotoContextMenu"}
        posX={contextMenuX}
        posY={contextMenuY}

        on:dismissed={() => contextMenuVisible = false}

        on:delete={handleContextMenuDelete}
        on:move={handleContextMenuMove}
        on:coverPhotoClicked={handleContextMenuCoverPhoto}
    />
{/if}

<slot/>
