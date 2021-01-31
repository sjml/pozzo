<script lang="ts">
    import type{ Photo } from "./pozzo.type";

    export let photo: Photo;
    export let size: string = "medium";
    export let dims: any;
</script>

{#if photo && dims}
    <div
        class="albumPhoto"
        style={`width: ${dims.width}px; height: ${dims.height}px; top: ${dims.top}px; left: ${dims.left}px;`}
    >
        <img
            alt="{photo.title}"
            src="{`/img/${size}/${photo.hash}.jpg`}"
            srcset="{`/img/${size}/${photo.hash}.jpg`}, {`/img/${size}2x/${photo.hash}.jpg 2x`}"
            width="{dims.width}px" height="{dims.height}px"
        />
    </div>
{/if}

<style>
    .albumPhoto {
        position: absolute;
        overflow: hidden;

        cursor: pointer;
    }
</style>
