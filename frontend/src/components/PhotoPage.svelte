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
    import VideoLoader from "./VideoLoader.svelte";

    const size = "large";

    const metaMapping = [
        {key: "creationDate", value: "Taken", filter: TimestampToDateString},
        {key: "mime", value: "Format"},
        {key: "make", value: "Camera Make"},
        {key: "model", value: "Camera Model"},
        {key: "lens", value: "Lens"},
        {key: "aperture", value: "Aperture", filter: (val) => `f/${val}`},
        {key: "iso", value: "ISO"},
        {key: "shutterSpeed", value: "Shutter Speed"},
    ];

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
        {#if !$currentPhotoStore.isVideo }
            <DoubleLoader
                stub={$currentPhotoStore}
                size={size}
                objectFit="contain"
            />
        {:else}
            <VideoLoader
            />
        {/if}
    </div>
    {#if $metadataVisible}
        <div class="metadata">
            <!-- this could be made more data-driven, but for now, sticking with hand-crafted -->
            <div class="title"><span class="label">Title:</span> {$currentPhotoStore.title}</div>
            <table class="photoMeta">
            <tr><td class="label">Original: </td><td>{$currentPhotoStore.width}×{$currentPhotoStore.height} ({HumanBytes($currentPhotoStore.size)})</td></tr>
            <tr><td class="label">Uploaded: </td><td>{$currentPhotoStore.uploadTimeStamp}</td></tr>
            </table>
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
                    <PhotoMap photoIDs={[$currentPhotoStore.id]} exploreIconOnly={true} popups={false} />
                </div>
                <div class="mapLinks">
                    {"{"} <a target="_" href="https://www.openstreetmap.org/?mlat={$currentPhotoStore.gpsLat}&mlon={$currentPhotoStore.gpsLon}#map=18/{$currentPhotoStore.gpsLat}/{$currentPhotoStore.gpsLon}">OpenStreetMap</a>
                    | <a target="_" href="https://www.google.com/maps/search/?api=1&query={$currentPhotoStore.gpsLat},{$currentPhotoStore.gpsLon}">Google Maps</a> {"}"}
                </div>
            {/if}

            <table class="photoMeta">
                <tr>
                    <td class="label">Tags</td>
                    <td class="value">{$currentPhotoStore.tags.length > 0 ? $currentPhotoStore.tags.join(", ") : "(None)"}</td>
                </tr>
                {#each metaMapping as meta}
                    {#if $currentPhotoStore[meta.key] != null}
                        <tr>
                            <td class="label">{meta.value}</td>
                            <td class="value">{meta.filter == undefined ? $currentPhotoStore[meta.key] : meta.filter($currentPhotoStore[meta.key])}</td>
                        </tr>
                    {/if}
                {/each}
            </table>
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
        overflow-y: scroll;
        border-left: 1px solid var(--separator-color);
    }

    @media only screen and (max-device-width: 480px) {
        .metadata {
            display: none;
        }
    }

    table .label {
        white-space: nowrap;
        padding-right: 5px;
    }

    table {
        border-spacing: 0 10px;
        border-collapse: separate;
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
        width: var(--button-size);
        height: var(--button-size);

        margin-right: 15px;
    }

    .photoMap {
        height: 250px;
        margin: 10px 0 0 0;
    }

    .mapLinks {
        text-align: right;
        margin: 5px 0;
    }
</style>
