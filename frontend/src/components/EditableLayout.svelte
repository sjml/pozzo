<script lang="ts">
    import { createEventDispatcher } from "svelte";
    import { flip } from "svelte/animate";

    import { dndzone } from "svelte-dnd-action";

    import type { Photo } from "../pozzo.type";
    import { GetImgPath } from "../util";


    const dispatch = createEventDispatcher();

    const animationDuration = 200;
    export let photoList: Photo[] = [];
    export let size: string = "medium";

    function dragAndDropConsider(e) {
        photoList = e.detail.items;
    }

    function dragAndDropFinalize(e) {
        photoList = e.detail.items;
        dispatch("reordered", {newOrder: photoList});
    }
</script>

<div class="editableLayout"
    use:dndzone={{
        items: photoList,
        flipDurationMs: animationDuration,
    }}
    on:consider={dragAndDropConsider}
    on:finalize={dragAndDropFinalize}
>
    {#each photoList as photo (photo.id)}
        <div class="editablePhoto"
            animate:flip={{duration: animationDuration}}
        >
            <img
                alt="{photo.title}"
                draggable="false"
                on:dragstart|preventDefault={() => {}}
                srcset="{GetImgPath(size, photo.hash, photo.uniq)}, {`${GetImgPath(size + "2x", photo.hash, photo.uniq)} 2x`}"
                src="{GetImgPath(size, photo.hash, photo.uniq)}"
            />
        </div>
    {/each}
</div>

<style>
    .editableLayout {
        background-color: rgb(101, 101, 252);
        -webkit-touch-callout: none;
          -webkit-user-select: none;
              -ms-user-select: none;
                  user-select: none;

        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
    }

    .editablePhoto {
        width: 200px;
        height: 200px;
        margin: 10px;

        display: flex;
    }

    .editablePhoto img {
        max-width: 200px;
        max-height: 200px;
        margin: auto;
    }
</style>
