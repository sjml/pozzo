<script lang="ts">
    import type{ Photo } from "./pozzo.type";

    export let photo: Photo;
    export let size: string = "medium";
</script>

{#if photo}
    <div class="albumPhoto">
        <img
            alt="{photo.title}"
            src="{`/img/${size}/${photo.hash}.jpg`}"
            srcset="{`/img/${size}/${photo.hash}.jpg`}, {`/img/${size}2x/${photo.hash}.jpg 2x`}"
        />
    </div>
{/if}

<style>
    .albumPhoto {
        margin: 5px 10px;

        cursor: pointer;
    }
</style>
