<script lang="ts">
    import { fade } from "svelte/transition";

    import type { Photo } from "../pozzo.type";
    import { GetImgPath } from "../util";

    export let photo: Photo;
    export let size: string = "medium";
    export let dims: any;

    let loaded = false;
</script>

{#if photo && dims}
    <div
        class="albumPhoto"
        >
        {#if !loaded}
            <img class="preload"
                draggable="false"
                out:fade="{{duration: 200}}"
                alt="{photo.title}"
                src="data:image/jpeg;base64,{photo.tinyJPEG}"
                width="{dims.width}px" height="{dims.height}px"
            />
        {/if}
        <img on:load={() => loaded = true}
            alt="{photo.title}"
            draggable="false"
            srcset="{GetImgPath(size, photo.hash, photo.uniq)}, {`${GetImgPath(size + "2x", photo.hash, photo.uniq)} 2x`}"
            src="{GetImgPath(size, photo.hash, photo.uniq)}"
        />
    </div>
{/if}

<style>
    .albumPhoto {
        overflow: hidden;
        width: 100%;
        height: 100%;
    }

    img {
        position: absolute;
        top: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .preload {
        filter: blur(0.8em);
        transform: scale(1.2);
        z-index: 100;
    }
</style>
