<script lang="ts">
    import { onDestroy, onMount } from "svelte";
    import { navigate } from "svelte-routing";

    import type { Photo, Album } from "../pozzo.type";
    import { RunApi } from "../api";
    import { frontendStateStore } from "../stores";
    import { GetImgPath, HumanBytes, TimestampToDateString } from "../util";
    import Button from "./Button.svelte";
    import PhotoMap from "./PhotoMap.svelte";

    const size = "large";

    export let photoID: number;
    export let photo: Photo;
    export let albumSlug: string;
    export let album: Album;
    export let nextPhotoIdx: number = -1;
    export let prevPhotoIdx: number = -1;


    onMount(() => {
        $frontendStateStore.photoToolsVisible = true;
    });

    onDestroy(() => {
        $frontendStateStore.backLink = "";
        $frontendStateStore.backLinkText = "";
        $frontendStateStore.photoToolsVisible = false;
    });


    let photoMeta: any = null;

    async function getPhoto(pid: number): Promise<Photo> {
        const pResPromise = RunApi(`/photo/view/${pid}`, {
            method: "POST",
            params: {
                preview: 1,
            },
            authorize: true
        });
        const aResPromise = RunApi(`/album/view/${albumSlug}`, {
            authorize: true
        });
        const pRes = await pResPromise;
        const aRes = await aResPromise;

        if (aRes.success) {
            album = aRes.data;
        }
        else {
            console.error("Couldn't load album.", aRes);
        }

        if (pRes.success) {
            photoMeta = null;
            photo = pRes.data;
            return photo;
        }
        else {
            console.error("Couldn't load photo.", pRes);
            return null;
        }
    }

    async function getMetadata() {
        const res = await RunApi(`/photo/meta/${photo.id}`, {
            authorize: true
        });
        if (res.success) {
            photoMeta = res.data;
        }
        else {
            console.error("Couldn't load metadata.", res);
        }
    }

    $: {
        if (photo && $frontendStateStore.isMetadataOn) {
            if (photoMeta == null || photoMeta.id != photo.id) {
                getMetadata();
            }
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
            $frontendStateStore.prevPhotoLink = "";
        }
        else {
            prevPhotoIdx = currIdx - 1;
            $frontendStateStore.prevPhotoLink = `/${a.slug}/${a.photos[prevPhotoIdx].id}`;
        }
        if (currIdx == a.photos.length-1) {
            nextPhotoIdx = -1;
            $frontendStateStore.nextPhotoLink = "";
        }
        else {
            nextPhotoIdx = currIdx + 1;
            $frontendStateStore.nextPhotoLink = `/${a.slug}/${a.photos[nextPhotoIdx].id}`;
        }
    }

    function handleKeyDown(evt: KeyboardEvent) {
        if (evt.key == "ArrowLeft" && prevPhotoIdx != -1) {
            navigate(`/${album.slug}/${album.photos[prevPhotoIdx].id}`);
        }
        else if (evt.key == "ArrowRight" && nextPhotoIdx != -1) {
            navigate(`/${album.slug}/${album.photos[nextPhotoIdx].id}`);
        }
    }

    $: {
        if (album) {
            $frontendStateStore.backLink = `/${album.slug}`;
            $frontendStateStore.backLinkText = album.title;
        }
    }

    $: if (photo && album) findNeighbors(photo, album)
</script>

<svelte:window
    on:keydown={handleKeyDown}
/>

{#await getPhoto(photoID)}
    Loading…
{:then photo}
    <div class="fullPhoto">
        <img
            alt="{photo.title}"
            srcset="{GetImgPath(size, photo.hash, photo.uniq)}, {`${GetImgPath(size + "2x", photo.hash, photo.uniq)} 2x`}"
            src="{GetImgPath(size, photo.hash, photo.uniq)}"
        />
    </div>
    {#if $frontendStateStore.isMetadataOn}
        <div class="metadata">
            <!-- this could be made more data-driven, but for now, sticking with hand-crafted -->
            <div class="title"><span class="label">Title:</span> {photo.title}</div>
            <div><span class="label">Original: </span>{photo.width}×{photo.height} ({HumanBytes(photo.size)})</div>
            <div><span class="label">Uploaded: </span>{photo.uploadTimeStamp}</div>
            <div class="dlOrig">
                <a href="/api/photo/orig/{photo.id}">
                    <Button margin="0 10px 0 0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="86 110 128 152 170 110" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="128" y1="39.97056" x2="128" y2="151.97056" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><path d="M224,136v72a8,8,0,0,1-8,8H40a8,8,0,0,1-8-8V136" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path></svg>
                        <div>Download Original</div>
                    </Button>
                </a>
            </div>
            {#if photo.latitude && photo.longitude}
                <div class="photoMap">
                    <PhotoMap photos={[photo]} />
                </div>
            {/if}
            {#if photoMeta}
                {#if photoMeta.IFD0_Make}
                    <div><span class="label">Make: </span>{photoMeta.IFD0_Make}</div>
                {/if}
                {#if photoMeta.IFD0_Model}
                    <div><span class="label">Model: </span>{photoMeta.IFD0_Model}</div>
                {/if}
                {#if photoMeta.IFD0_DateTime}
                    <div><span class="label">Taken: </span>{TimestampToDateString(photoMeta.IFD0_DateTime)}</div>
                {/if}
            {/if}
        </div>
    {/if}
{/await}

<style>
    .fullPhoto {
        position: relative;
    }

    .metadata {
        width: 300px;

        padding: 10px;
        border-left: 1px solid rgb(58, 58, 58);
    }

    .title {
        margin-bottom: 10px;

        font-size: 120%;
    }

    .label {
        margin-right: 10px;

        font-weight: bold;
    }

    .dlOrig {
        margin-top: 10px;
    }

    .dlOrig a {
        display: flex;
        align-items: center;
    }

    svg {
        width: 30px;
        height: 30px;

        margin-right: 15px;
    }

    .photoMap {
        height: 250px;
        margin: 10px 0px;
    }

    img {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 100%;

        object-fit: contain;
    }
</style>
