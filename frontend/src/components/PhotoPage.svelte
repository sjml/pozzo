<script lang="ts">
    import { onDestroy, onMount } from "svelte";
    import { navigate } from "svelte-routing";

    import { frontendStateStore } from "../stores";
    import type { Photo, Album } from "../pozzo.type";
    import { RunApi } from "../api";
    import { GetImgPath } from "../util";

    async function getPhoto(pid: number) {
        const pResPromise = RunApi(`/photo/view/${pid}`, {
            method: "POST",
            params: {
                preview: 1,
            },
            authorize: true
        });
        const aResPromise = RunApi(`/album/view/${albumSlug}`, {
            method: "POST",
            params: {
                preview: 1,
            },
            authorize: true
        })
        const pRes = await pResPromise;
        const aRes = await aResPromise;

        if (pRes.success) {
            photo = pRes.data;
            loaded = false;
        }
        else {
            console.error("Couldn't load photo.", pRes);
        }

        if (aRes.success) {
            album = aRes.data;
        }
        else {
            console.error("Couldn't load album.", aRes);
        }
    }

    onMount(async () => {
        if (photo == null) {
            await getPhoto(photoID);
        }
        $frontendStateStore.photoToolsVisible = true;
    });

    onDestroy(() => {
        $frontendStateStore.backLink = "";
        $frontendStateStore.backLinkText = "";
        $frontendStateStore.photoToolsVisible = false;
    })

    function handleKeyDown(evt: KeyboardEvent) {
        if (evt.key == "ArrowLeft" && prevPhotoIdx != -1) {
            navigate(`/album/${album.slug}/${album.photos[prevPhotoIdx].id}`);
        }
        else if (evt.key == "ArrowRight" && nextPhotoIdx != -1) {
            navigate(`/album/${album.slug}/${album.photos[nextPhotoIdx].id}`);
        }
    }

    function findNeighbors(p: Photo, a: Album) {
        const currIdx = a.photos.findIndex((ap) => ap.id == p.id);
        if (currIdx == -1) {
            console.error("photo not in album!");
            return;
        }
        if (currIdx == 0) {
            prevPhotoIdx = -1;
        }
        else {
            prevPhotoIdx = currIdx - 1;
        }
        if (currIdx == a.photos.length-1) {
            nextPhotoIdx = -1;
        }
        else {
            nextPhotoIdx = currIdx + 1;
        }
    }

    $: if (photo && album) findNeighbors(photo, album)
    $: getPhoto(photoID)

    export let photoID: number;
    export let photo: Photo;
    export let albumSlug: string;
    export let album: Album;
    export let nextPhotoIdx: number = -1;
    export let prevPhotoIdx: number = -1;
    const size = "large";
    let loaded = false;
    let boundsW: number = 0;
    let boundsH: number = 0;
    let photoW: number = 0;
    let photoH: number = 0;

    $: {
        if (album) {
            $frontendStateStore.backLink = `/album/${album.slug}`;
            $frontendStateStore.backLinkText = album.title;
        }
    }
</script>

<svelte:window
    on:keydown={handleKeyDown}
/>

{#if photo}
    <div class="fullPhoto" bind:clientWidth={boundsW} bind:clientHeight={boundsH}>
        <div class="doubleLoader">
            <!-- this is where the preview image goes/went -->
            <img on:load={() => loaded = true}
                alt="{photo.title}"
                srcset="{GetImgPath(size, photo.hash, photo.uniq)}, {`${GetImgPath(size + "2x", photo.hash, photo.uniq)} 2x`}"
                src="{GetImgPath(size, photo.hash, photo.uniq)}"
            />
        </div>
    </div>
    {#if $frontendStateStore.isMetadataOn}
        <div class="metadata">
            Metas datum!
        </div>
    {/if}
{/if}

<style>
    .fullPhoto {
        flex-grow: 1;
        overflow: hidden;
    }

    .doubleLoader {
        overflow: hidden;
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
    }

    .metadata {
        width: 300px;
    }

    img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
</style>
