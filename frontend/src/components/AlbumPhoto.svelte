<script lang="ts">
    import type{ Photo } from "../pozzo.type";
    import { fade } from 'svelte/transition';

    export let photo: Photo;
    export let size: string = "medium";
    export let dims: any;

    let loaded = false;
</script>

{#if photo && dims}
    <div
        class="albumPhoto"
        style={`width: ${dims.width}px; height: ${dims.height}px; top: ${dims.top}px; left: ${dims.left}px;`}
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
            srcset="{`/img/${size}/${photo.hash}.jpg`}, {`/img/${size}2x/${photo.hash}.jpg 2x`}"
            src="{`/img/${size}/${photo.hash}.jpg`}"
            width="{dims.width}px"
        />
    </div>
{/if}

<style>
    .albumPhoto {
        position: absolute;
        cursor: pointer;
        overflow: hidden;
    }

    .preload {
        filter: blur(0.8em);
        transform: scale(1.2);
        z-index: 100;
    }
</style>
