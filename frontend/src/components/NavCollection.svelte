<script lang="ts">
    import { tick, createEventDispatcher } from "svelte";

    import type { Photo } from "../pozzo.type";
    import { currentAlbumStore, navSelection, isLoggedInStore, modalUp } from "../stores";
    import { IsMetaKeyDownForEvent } from "../util";

    const dispatch = createEventDispatcher();

    export let stubs: Photo[] = [];

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
        stubs = stubs.filter(s => $navSelection.indexOf(s) < 0);
        dispatch("deleted", {newStubs: stubs});
        contextMenuVisible = false;
        $navSelection = [];
    }

    function handleContextMenuMove(evt: CustomEvent) {
        stubs = stubs.filter(s => $navSelection.indexOf(s) < 0);
        dispatch("moved", {newStubs: stubs, targetAlbumID: evt.detail.targetAlbumID});
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
        if (contextMenuVisible || $modalUp) {
            return;
        }
        if (evt.key == "a" && IsMetaKeyDownForEvent(evt)) {
            $navSelection = stubs;
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
    {#await import("./PhotoContextMenu.svelte") then {default: component}}
        <svelte:component this={component}
            posX={contextMenuX}
            posY={contextMenuY}

            on:dismissed={() => contextMenuVisible = false}

            on:delete={handleContextMenuDelete}
            on:move={handleContextMenuMove}
            on:coverPhotoClicked={handleContextMenuCoverPhoto}
        />
    {/await}
{/if}

<slot/>
