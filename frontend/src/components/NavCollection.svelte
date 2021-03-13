<script lang="ts">
    import { tick, createEventDispatcher } from "svelte";

    import type { Photo } from "../pozzo.type";
    import { currentAlbumStore, navSelection, isLoggedInStore, modalUp } from "../stores";
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

    function handleContextMenuDelete(_: CustomEvent) {
        dispatch("deleted", {deleted: photos.filter(s => $navSelection.indexOf(s) >= 0)});
        contextMenuVisible = false;
        $navSelection = [];
    }

    function handleContextMenuMove(evt: CustomEvent) {
        dispatch("moved", {
            moved: photos.filter(s => $navSelection.indexOf(s) >= 0),
            targetAlbumID: evt.detail.targetAlbumID
        });
        contextMenuVisible = false;
        $navSelection = [];
    }

    function handleContextMenuCoverPhoto(evt: CustomEvent) {
        if ($currentAlbumStore.coverPhoto?.id === $navSelection[0].id) {
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
        if ($modalUp) {
            return;
        }
        if (evt.key == "a" && IsMetaKeyDownForEvent(evt)) {
            $navSelection = $navSelection.concat(photos);
            evt.preventDefault();
        }
        if (evt.key == "d" && IsMetaKeyDownForEvent(evt)) {
            $navSelection = $navSelection.filter(p => photos.indexOf(p) < 0);
            evt.preventDefault();
        }
    }
</script>

<svelte:body
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

        on:splitGroup={(evt) => {contextMenuVisible = false; dispatch("splitGroup", evt.detail)}}
        on:makeNewGroup={(evt) => {contextMenuVisible = false; dispatch("makeNewGroup", evt.detail)}}
    />
{/if}

<div class="navCollection"
    on:contextmenu={handleRightClick}
>
    <slot/>
</div>
