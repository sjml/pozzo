<script lang="ts">
    import { onDestroy } from "svelte";
    import { router } from "tinro";

    //// LOL the irony of this page of all pages not needing the Photo type
    // import type { Photo } from "../pozzo.type";
    import { RunApi } from "../api";
    import { currentAlbumStore, currentPhotoStore, metadataVisible } from "../stores";
    import { HumanBytes, TimestampToDateString } from "../util";
    import Button from "./Button.svelte";
    import PhotoMap from "./PhotoMap.svelte";
    import DoubleLoader from "./DoubleLoader.svelte";

    const size = "large";

    export let photoIdentifier: string;
    let photoID: number = null;

    onDestroy(() => {
        $currentPhotoStore = null;
    });

    $: {
        photoID = parseInt(photoIdentifier);
        if (isNaN(photoID)) {
            photoID = null;
            router.goto(`/album/${$currentAlbumStore.slug}`)
        }
        else {
            getPhoto(photoID);
        }
    }

    async function getPhoto(pid: number) {
        const res = await RunApi(`/photo/view/${pid}`, {
            authorize: true
        });

        if (res.success) {
            $currentPhotoStore = res.data;
        }
        else {
            console.error(res);
        }
    }
</script>

{#if !$currentPhotoStore}
    Loading…
{:else}
    <div class="fullPhoto">
        <DoubleLoader
            stub={$currentPhotoStore}
            size={size}
            canvasFit="contain"
        />
    </div>
    {#if $metadataVisible}
        <div class="metadata">
            <!-- this could be made more data-driven, but for now, sticking with hand-crafted -->
            <div class="title"><span class="label">Title:</span> {$currentPhotoStore.title}</div>
            <div><span class="label">Original: </span>{$currentPhotoStore.width}×{$currentPhotoStore.height} ({HumanBytes($currentPhotoStore.size)})</div>
            <div><span class="label">Uploaded: </span>{$currentPhotoStore.uploadTimeStamp}</div>
            <div class="dlOrig">
                <a href="/api/photo/orig/{$currentPhotoStore.id}" tinro-ignore>
                    <Button margin="0 10px 0 0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="86 110 128 152 170 110" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="128" y1="39.97056" x2="128" y2="151.97056" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><path d="M224,136v72a8,8,0,0,1-8,8H40a8,8,0,0,1-8-8V136" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path></svg>
                        <div>Download Original</div>
                    </Button>
                </a>
            </div>
            {#if $currentPhotoStore.gpsLat && $currentPhotoStore.gpsLon}
                <div class="photoMap">
                    <PhotoMap photoIDs={[$currentPhotoStore.id]} />
                </div>
            {/if}
            <!-- {#if photoMeta}
                {#if photoMeta.IFD0_Make}
                    <div><span class="label">Make: </span>{photoMeta.IFD0_Make}</div>
                {/if}
                {#if photoMeta.IFD0_Model}
                    <div><span class="label">Model: </span>{photoMeta.IFD0_Model}</div>
                {/if}
                {#if photoMeta.IFD0_DateTime}
                    <div><span class="label">Taken: </span>{TimestampToDateString(photoMeta.IFD0_DateTime)}</div>
                {/if}
            {/if} -->
        </div>
    {/if}
{/if}

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
</style>
