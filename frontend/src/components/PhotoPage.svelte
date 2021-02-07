<script lang="ts">
    import { onMount, tick } from "svelte";
    import { fade } from "svelte/transition";

    import type { Photo } from "../pozzo.type";
    import { RunApi } from "../api";
    import { getImgPath } from "../util";

    async function getPhoto() {
        const res = await RunApi(`/photo/view/${photoID}`, {
            method: "POST",
            params: {
                preview: 1
            }
        });
        if (res.success) {
            photo = res.data;
        }
        else {
            console.error("Couldn't load photo.", res);
        }
    }

    onMount(async () => {
        if (photo == null) {
            await getPhoto();
            calculateImageSize();
        }
    });

    function calculateImageSize() {
        const displayAspect = boundsW / boundsH;
        if (displayAspect > 1.0) {
            // landscape display
            photoH = boundsH;
            photoW = boundsH * photo.aspect;
        }
        else {
            // portrait display
            photoW = boundsW;
            photoH = boundsW / photo.aspect;
        }
    }

    export let photoID: number;
    export let photo: Photo;
    const size = "large";
    let loaded = false;
    let boundsW: number = 0;
    let boundsH: number = 0;
    let photoW: number = 0;
    let photoH: number = 0;
</script>

{#if photo}
    <div class="fullPhoto" bind:clientWidth={boundsW} bind:clientHeight={boundsH}>
        <div class="doubleLoader"
            style={`width: ${photoW}px; height: ${photoH}px;`}
        >
            {#if !loaded}
                <img class="preload"
                    out:fade="{{duration: 200}}"
                    alt="{photo.title}"
                    src="data:image/jpeg;base64,{photo.tinyJPEG}"
                    style={`width: ${photoW}px; height: ${photoH}px;`}
                />
            {/if}
            <img on:load={() => loaded = true}
                alt="{photo.title}"
                srcset="{getImgPath(size, photo.hash)}, {`${getImgPath(size + "2x", photo.hash)} 2x`}"
                src="{getImgPath(size, photo.hash)}"
                style={`width: ${photoW}px; height: ${photoH}px;`}
            />
        </div>
    </div>
{/if}

<style>
    .fullPhoto {
        position: absolute;
        top: 3em;
        left: 0;
        bottom: 0;
        right: 0;
        display: flex;
        justify-content: center;
        overflow: hidden;
    }

    .doubleLoader {
        position: relative;
    }

    img {
        position: absolute;
        top: 0;
        left: 0;
    }

    .preload {
        filter: blur(0.8em);
        transform: scale(1.2);
        z-index: 100;
    }
</style>
