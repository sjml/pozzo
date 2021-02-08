<script lang="ts">
    import { fade } from "svelte/transition";

    import type { Photo } from "../pozzo.type";
    import { albumSelectionStore, loginCredentialStore } from "../stores";
    import { GetImgPath } from "../util";

    export let photo: Photo;
    export let photoID: number;
    export let size: string = "medium";
    export let dims: any;

    let loaded = false;
    let isSelected = false;
    let isBeingDragged = false;
    let styleString = "";

    $: isSelected = $albumSelectionStore.indexOf(photoID) >= 0
    $: {
        if (dims) {
            styleString = `width: ${dims.width}px; height: ${dims.height}px; top: ${dims.top}px; left: ${dims.left}px;`;
        }
        else {

        }
    }
</script>

{#if photo && dims}
    <div
        class="albumPhoto"
        class:selected={isSelected}
        style={styleString}
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
            srcset="{GetImgPath(size, photo.hash, photo.uniq)}, {`${GetImgPath(size + "2x", photo.hash, photo.uniq)} 2x`}"
            src="{GetImgPath(size, photo.hash, photo.uniq)}"
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
