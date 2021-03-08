<script lang="ts">
    import { onDestroy } from "svelte";
    import { navigate } from "svelte-routing";

    import type { Photo, PhotoGroup } from "../pozzo.type";
    import { currentAlbumStore, currentPerusalStore, metadataVisible } from "../stores";
    import { HumanBytes, TimestampToDateString, Fractionalize } from "../util";
    import LazyLoad from "./LazyLoad.svelte";
    import Button from "./Button.svelte";
    import StagedLoader from "./StagedLoader.svelte";
    import VideoLoader from "./VideoLoader.svelte";
    import Markdown from "./Markdown.svelte";

    const photoSize = "large";

    const metaDataMapping = [
        {key: "creationDate", value: "Taken", filter: TimestampToDateString},
        {key: "mime", value: "Format"},
        {key: "make", value: "Camera Make"},
        {key: "model", value: "Camera Model"},
        {key: "lens", value: "Lens"},
        {key: "aperture", value: "Aperture", filter: (val) => `f/${val}`},
        {key: "iso", value: "ISO"},
        {key: "shutterSpeed", value: "Shutter Speed", filter: Fractionalize},
    ];

    export let perusalIdentifier: string;

    onDestroy(() => {
        $currentPerusalStore.currentIdx = -1;
    })

    let photo: Photo;
    let photoGroup: PhotoGroup;
    function getItem(ident: string) {
        if (ident.startsWith("g")) {
            photo = null;
            const gid = parseInt(ident.slice(1));
            if (isNaN(gid)) {
                photoGroup == null;
            }
            else {
                const likelyGroupIdx = $currentPerusalStore.nodes.findIndex(pn => {
                    if (pn.hasOwnProperty("hash")) {
                        return false;
                    }
                    if (pn.id == gid) {
                        return true;
                    }
                });
                if (likelyGroupIdx == -1) {
                    photoGroup = null;
                }
                else {
                    $currentPerusalStore.currentIdx = likelyGroupIdx;
                    photoGroup = ($currentPerusalStore.nodes[likelyGroupIdx] as PhotoGroup);
                }
            }
        }
        else {
            photoGroup = null;
            const pid = parseInt(ident);
            if (isNaN(pid)) {
                photo = null;
            }
            else {
                const likelyPhotoIdx = $currentPerusalStore.nodes.findIndex(pn => {
                    if (!pn.hasOwnProperty("hash")) {
                        return false;
                    }
                    if (pn.id == pid) {
                        return true;
                    }
                })
                if (likelyPhotoIdx == -1) {
                    photo = null;
                }
                else {
                    $currentPerusalStore.currentIdx = likelyPhotoIdx;
                    photo = ($currentPerusalStore.nodes[likelyPhotoIdx] as Photo);
                }
            }
        }

        if (photo == null && photoGroup == null) {
            navigate(`/album/${$currentAlbumStore.slug}`);
        }
    }
    $: getItem(perusalIdentifier)
</script>

<div class="fullPerusal">
    {#if photo}
        {#if !photo.isVideo }
            <StagedLoader
                photo={photo}
                lazy={false}
                size={photoSize}
                objectFit="contain"
            />
        {:else}
            <VideoLoader
                photoWhichIsReallyAVideo={photo}
            />
        {/if}
    {:else if photoGroup}
        <div class="interstitial">
            <Markdown markdown={photoGroup.description} />
        </div>
    {/if}
</div>
{#if photo && $metadataVisible}
    <div class="metadata">
        <!-- this could be made more data-driven, but for now, sticking with hand-crafted -->
        <div class="title"><span class="label">Title:</span> {photo.title}</div>
        <table class="photoMeta">
        <tr><td class="label">Original: </td><td>{photo.width}×{photo.height} ({HumanBytes(photo.size)})</td></tr>
        <tr><td class="label">Uploaded: </td><td>{photo.uploadTimeStamp}</td></tr>
        </table>
        <div class="dlOrig">
            <a href="/api/photo/orig/{photo.id}">
                <Button margin="0 10px 0 0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="86 110 128 152 170 110" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="128" y1="39.97056" x2="128" y2="151.97056" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><path d="M224,136v72a8,8,0,0,1-8,8H40a8,8,0,0,1-8-8V136" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path></svg>
                    <div>Download Original</div>
                </Button>
            </a>
        </div>
        {#if photo.gpsLat && photo.gpsLon}
            <div class="photoMap">
                <LazyLoad loader={"PhotoMap"}
                    photos={[photo]}
                    exploreIconOnly={true}
                    popups={false}
                />
            </div>
            <div class="mapLinks">
                {"{"} <a target="_" href="https://www.openstreetmap.org/?mlat={photo.gpsLat}&mlon={photo.gpsLon}#map=18/{photo.gpsLat}/{photo.gpsLon}">OpenStreetMap</a>
                | <a target="_" href="https://www.google.com/maps/search/?api=1&query={photo.gpsLat},{photo.gpsLon}">Google Maps</a> {"}"}
            </div>
        {/if}

        <table class="photoMeta">
            <tr>
                <td class="label">Tags</td>
                <td class="value">{photo.tags.length > 0 ? photo.tags.join(", ") : "(None)"}</td>
            </tr>
            {#each metaDataMapping as meta}
                {#if photo[meta.key] != null}
                    <tr>
                        <td class="label">{meta.value}</td>
                        <td class="value">{meta.filter == undefined ? photo[meta.key] : meta.filter(photo[meta.key])}</td>
                    </tr>
                {/if}
            {/each}
        </table>
    </div>
{/if}


<style>
    .fullPerusal {
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

    .mapLinks a {
        text-decoration: underline;
    }

    .interstitial {
        max-width: 900px;
        margin: 0 auto;
        font-size: xx-large;
    }
</style>
