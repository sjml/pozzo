<script lang="ts">
    import { createEventDispatcher } from "svelte";
    import { flip } from "svelte/animate";

    import { dndzone } from "svelte-dnd-action";

    import type { PhotoStub } from "../pozzo.type";
    import { GetImgPath } from "../util";


    const dispatch = createEventDispatcher();

    const animationDuration = 200;
    export let stubList: PhotoStub[] = [];
    export let size: string = "medium";

    function dragAndDropConsider(e) {
        stubList = e.detail.items;
    }

    function dragAndDropFinalize(e) {
        stubList = e.detail.items;
        dispatch("reordered", {newStubs: stubList});
    }
</script>

<div class="editableLayout"
    use:dndzone={{
        items: stubList,
        flipDurationMs: animationDuration,
    }}
    on:consider={dragAndDropConsider}
    on:finalize={dragAndDropFinalize}
>
    {#each stubList as stub (stub.id)}
        <div class="editablePhoto"
            animate:flip={{duration: animationDuration}}
        >
            {#if stub.hash && stub.uniq}
            <img
                alt={stub.title ?? ""}
                draggable="false"
                on:dragstart|preventDefault={() => {}}
                srcset="{GetImgPath(size, stub.hash, stub.uniq)}, {`${GetImgPath(size + "2x", stub.hash, stub.uniq)} 2x`}"
                src="{GetImgPath(size, stub.hash, stub.uniq)}"
            />
            {:else}
                <div class="placeholder"></div>
            {/if}
            <div>{stub.title ?? ""}</div>
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
        flex-direction: column;
        align-content: center;
        text-align: center;
    }

    .editablePhoto img, .placeholder {
        max-width: 200px;
        max-height: 200px;
        margin: auto;
    }

    .placeholder {
        width: 200px;
        height: 130px;
        margin: auto;

        background-color: black;
    }
</style>
