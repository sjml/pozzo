<script lang="ts">
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
        <img on:load={() => loaded = true}
            alt="{photo.title}"
            class="main"
            class:loaded
            draggable="false"
            on:dragstart|preventDefault={() => {}}
            srcset="{GetImgPath(size, photo.hash, photo.uniq)}, {`${GetImgPath(size + "2x", photo.hash, photo.uniq)} 2x`}"
            src="{GetImgPath(size, photo.hash, photo.uniq)}"
        />
        <img class="preload"
            draggable="false"
            on:dragstart|preventDefault={() => {}}
            alt="{photo.title}"
            src="data:image/jpeg;base64,{photo.tinyJPEG}"
            width="{dims.width}px" height="{dims.height}px"
        />
    </div>
{/if}

<style>
    .albumPhoto {
        width: 100%;
        height: 100%;

        overflow: hidden;
    }

    img {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;

        object-fit: cover;
    }

    img.main {
        z-index: 100;

        opacity: 0.0;
        transition-property: opacity;
        transition-duration: 200ms;
    }

    img.main.loaded {
        opacity: 1.0;
    }

    .preload {
        filter: blur(0.8em);
        transform: scale(1.2);
    }
</style>
