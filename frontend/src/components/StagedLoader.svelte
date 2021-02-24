<script lang="ts">
    import { tick } from "svelte";

    import { decode } from "blurhash";

    import type { Photo } from "../pozzo.type";
    import { GetImgPath } from "../util";

    export let photo: Photo = null;
    export let lazy: boolean = false;
    export let size = "large";
    export let objectFit = "contain";
    export let altTitle: string = null;

    let photoID: number = -1;


    let preloadCanvas: HTMLCanvasElement;
    let mainImg: HTMLImageElement;
    async function prepPlaceholder() {
        if (stage > 1) {
            return;
        }
        const decodeX = 32, decodeY = 32;
        const decodePunch = 1;

        const pixels = decode(photo.blurHash, decodeX, decodeY, decodePunch);

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
            dimX = dimY * photo.aspect;
        }
        else {
            dimY = dimX / photo.aspect;
        }

        preloadCanvas.width = dimX + 2;
        preloadCanvas.height = dimY;
        ctx.drawImage(tempCanvas, 0, 0, dimX + 2, dimY);

        setStage(1);
    }


    let srcSetStr = "";
    let srcStr = "";
    let altStr = "";
    async function setSrcs(p: Photo) {
        if (p == null) {
            srcSetStr = "";
            srcStr = "";
        }
        else {
            srcSetStr = `${GetImgPath(size, photo.hash, photo.uniq)}, ${GetImgPath(size + "2x", photo.hash, photo.uniq)} 2x`;
            srcStr = GetImgPath(size, photo.hash, photo.uniq);
        }
    }

    let stage = -1;
    async function setStage(newStage: number) {
        await tick();
        stage = newStage;
        if (newStage == 0) {
            photoID = photo.id;
            setSrcs(null);
            altStr = "";
            prepPlaceholder();
        }
        if (newStage == 1) {
            setSrcs(photo);
        }
        if (newStage == 2) {
            altStr = altTitle ?? photo.title ?? null;
        }
    }

    $: if (photo?.id != photoID) setStage(0)

</script>

<canvas
    class="preload"
    bind:this={preloadCanvas}
    class:removed={stage > 1}
    style={`object-fit: ${objectFit};`}
/>

<img
    bind:this={mainImg}
    loading={lazy ? "lazy" : null}
    on:load={() => setStage(2)}

    class="main"
    class:loading={stage < 2}
    style={`object-fit: ${objectFit};`}

    alt={altStr}
    srcset={srcSetStr}
    src={srcStr}
/>


<style>
    .main {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;

        object-fit: contain;
    }

    .main.loading {
        opacity: 0;
    }

    .preload {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10;

        opacity: 1.0;
    }

    .preload.removed {
        opacity: 0.0;
        transition-property: opacity;
        transition-duration: 200ms;
        transition-delay: 50ms;
    }
</style>
