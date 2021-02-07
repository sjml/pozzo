<script lang="ts">
    import { fade } from "svelte/transition";

    import type { Photo } from "../pozzo.type";
    import { albumSelectionStore, loginCredentialStore } from "../stores";
    import { getImgPath } from "../util";

    export let photo: Photo;
    export let photoID: number;
    export let size: string = "medium";
    export let dims: any;

    let loaded = false;

    let isSelected = false;
    function handleClick(evt: MouseEvent) {
        if ($loginCredentialStore.length == 0) {
            return;
        }
        if (!evt.metaKey) {
            return;
        }
        const selIdx = $albumSelectionStore.indexOf(photoID);
        if (selIdx != -1) {
            isSelected = false;
            $albumSelectionStore = $albumSelectionStore.filter(si => si != photoID);
        }
        else {
            isSelected = true;
            $albumSelectionStore = [...$albumSelectionStore, photoID];
        }
    }
    function handleContextMenu(_: MouseEvent) {
        if ($loginCredentialStore.length == 0) {
            return;
        }
        if ($albumSelectionStore.length == 0) {
            $albumSelectionStore = [photoID];
        }
    }

    $: isSelected = $albumSelectionStore.indexOf(photoID) >= 0
</script>

{#if photo && dims}
    <div
        class="albumPhoto"
        class:selected={isSelected}
        style={`width: ${dims.width}px; height: ${dims.height}px; top: ${dims.top}px; left: ${dims.left}px;`}
        on:click={handleClick}
        on:contextmenu={handleContextMenu}
    >
        {#if !loaded}
            <img class="preload"
                out:fade="{{duration: 200}}"
                alt="{photo.title}"
                src="data:image/jpeg;base64,{photo.tinyJPEG}"
                width="{dims.width}px" height="{dims.height}px"
            />
        {/if}
        <img on:load={() => loaded = true}
            alt="{photo.title}"
            srcset="{getImgPath(size, photo.hash)}, {`${getImgPath(size + "2x", photo.hash)} 2x`}"
            src="{getImgPath(size, photo.hash)}"
        />
    </div>
{/if}

<style>
    .albumPhoto {
        position: absolute;
        cursor: pointer;
        overflow: hidden;
    }

    img {
        position: absolute;
        top: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .selected {
        outline: 3px solid white;

    }

    .preload {
        filter: blur(0.8em);
        transform: scale(1.2);
        z-index: 100;
    }
</style>
