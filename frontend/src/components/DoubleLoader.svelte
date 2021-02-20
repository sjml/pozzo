<script lang="ts">
    import { onMount, tick } from "svelte";

    import { decode } from "blurhash";

    import type { PhotoStub } from "../pozzo.type";
    import { GetImgPath } from "../util";


    export let stub: PhotoStub = null;
    export let size: string = null;
    export let altTitle: string = null;
    export let canvasFit: string = "contain";

    let loaded = false;
    let preloaderVisible = true;

    let preloadCanvas: HTMLCanvasElement;
    let mainImg: HTMLImageElement;


    onMount(setupObserver);

    function mainLoaded() {
        loaded = true;
        if (intOb) {
            intOb.disconnect();
            intOb = null;
        }
        setTimeout(() => preloaderVisible = false, 350);
    }

    async function prepPlaceholder() {
        if (loaded) {
            return;
        }
        const decodeX = 32, decodeY = 32;
        const decodePunch = 1;

        const pixels = decode(stub.blurHash, decodeX, decodeY, decodePunch);

        const tempCanvas = document.createElement("canvas");
        tempCanvas.width = decodeX;
        tempCanvas.height = decodeY;
        const tempCtx = tempCanvas.getContext("2d");
        const imgData = tempCtx.createImageData(decodeX, decodeY);
        imgData.data.set(pixels);
        tempCtx.putImageData(imgData, 0, 0);

        const ctx = preloadCanvas.getContext("2d");
        let dimX = mainImg.clientWidth;
        let dimY = mainImg.clientHeight;
        const displayAspect = dimX / dimY;
        if (displayAspect > 1) {
            dimX = dimY * stub.aspect;
        }
        else {
            dimY = dimX / stub.aspect;
        }

        preloadCanvas.width = dimX;
        preloadCanvas.height = dimY;
        ctx.drawImage(tempCanvas, 0, 0, dimX, dimY);
    }

    let intOb: IntersectionObserver = null;
    function setupObserver() {
        if (!preloadCanvas) {
            return;
        }
        intOb = new IntersectionObserver((entries) => {
            if (entries[0].intersectionRatio > 0) {
                prepPlaceholder();
                intOb.disconnect();
                intOb = null;
            }
        });
        intOb.observe(preloadCanvas);
    }
</script>

<img
    on:load={mainLoaded}
    class="main"
    class:loaded
    bind:this={mainImg}
    alt={stub.title ?? altTitle ?? null}
    srcset="{GetImgPath(size, stub.hash, stub.uniq)}, {`${GetImgPath(size + "2x", stub.hash, stub.uniq)} 2x`}"
    src="{GetImgPath(size, stub.hash, stub.uniq)}"
/>

{#if preloaderVisible}
    <canvas
        class="preload"
        bind:this={preloadCanvas}
        style={`object-fit: ${canvasFit};`}
    />
{/if}

<style>
    img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    img.main {
        z-index: 20;

        object-fit: contain;
        opacity: 0.0;
        transition-property: opacity;
        transition-duration: 300ms;
    }

    img.main.loaded {
        opacity: 1.0;
    }

    .preload {
        position: absolute;
        z-index: 10;
        width: 100%;
        height: 100%;
    }
</style>
