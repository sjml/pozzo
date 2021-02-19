<script lang="ts">
    import { text } from "svelte/internal";
import type { PhotoStub } from "../pozzo.type";
    import { isLoggedInStore, navSelection } from "../stores";
    import { GetImgPath, IsMetaKeyDownForEvent } from "../util";

    export let stub: PhotoStub = null;
    export let layoutDims: any = null;
    export let size: string = "medium";
    export let textOverlay: string = null;

    let selected = false;
    $: selected = $navSelection.indexOf(stub) >= 0;


    function handleClick(evt: MouseEvent) {
        if (!$isLoggedInStore) {
            return;
        }
        if (!IsMetaKeyDownForEvent(evt)) {
            return;
        }
        evt.preventDefault();
        if (!selected) {
            $navSelection = [...$navSelection, stub];
        }
        else {
            $navSelection = $navSelection.filter(ps => ps.id != stub.id);
        }
    }

    function handleRightClick() {
        if ($navSelection.length == 0) {
            $navSelection = [...$navSelection, stub];
        }
    }
</script>


{#if layoutDims}
<div class="navSlot"
    style={`top: ${layoutDims.top}px; left: ${layoutDims.left}px; width: ${layoutDims.width}px; height: ${layoutDims.height}px;`}
    class:selected
    on:click={handleClick}
    on:contextmenu={handleRightClick}
>
    {#if textOverlay}
        <div class="textOverlay">{textOverlay}</div>
    {/if}
    {#if stub.hash && stub.uniq}
    <img
        alt={stub.title ?? textOverlay ?? null}
        class="main"
        srcset="{GetImgPath(size, stub.hash, stub.uniq)}, {`${GetImgPath(size + "2x", stub.hash, stub.uniq)} 2x`}"
        src="{GetImgPath(size, stub.hash, stub.uniq)}"
    />
    {/if}
</div>
{/if}

<style>
    .navSlot {
        position: absolute;
        width: 100%;
        height: 100%;

        cursor: pointer;
        overflow: hidden;

        background-color: black;
        outline: 0px solid white;
        transition-property: outline;
        transition-duration: 150ms;

        -webkit-touch-callout: none;
          -webkit-user-select: none;
              -ms-user-select: none;
                  user-select: none;
    }

    .navSlot.selected {
        outline: 3px solid white;
    }

    img {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;

        object-fit: cover;
    }

    .textOverlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);

        text-align: center;
        font-size: 3.5em;
        font-weight: bold;
        text-shadow: 0px 0px 10px black;
        z-index: 100;
    }
</style>
