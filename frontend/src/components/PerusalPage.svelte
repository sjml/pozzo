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
        $currentPerusalStore.currentPhoto = null;
        $currentPerusalStore.currentGroup = null;
    })

    function getItem(ident: string) {
        if (ident.startsWith("g")) {
            $currentPerusalStore.currentPhoto = null;
            const gid = parseInt(ident.slice(1));
            if (isNaN(gid)) {
                $currentPerusalStore.currentGroup == null;
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
                    $currentPerusalStore.currentGroup = null;
                }
                else {
                    $currentPerusalStore.currentIdx = likelyGroupIdx;
                    $currentPerusalStore.currentGroup = ($currentPerusalStore.nodes[likelyGroupIdx] as PhotoGroup);
                }
            }
        }
        else {
            $currentPerusalStore.currentGroup = null;
            const pid = parseInt(ident);
            if (isNaN(pid)) {
                $currentPerusalStore.currentPhoto = null;
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
                    $currentPerusalStore.currentPhoto = null;
                }
                else {
                    $currentPerusalStore.currentIdx = likelyPhotoIdx;
                    $currentPerusalStore.currentPhoto = ($currentPerusalStore.nodes[likelyPhotoIdx] as Photo);
                }
            }
        }

        if ($currentPerusalStore.currentPhoto == null && $currentPerusalStore.currentGroup == null) {
            navigate(`/${$currentAlbumStore.type}/${$currentAlbumStore.slug}`);
        }
    }
    $: getItem(perusalIdentifier)
</script>

<div class="fullPerusal">
    {#if $currentPerusalStore.currentPhoto}
        {#if !$currentPerusalStore.currentPhoto.isVideo }
            <StagedLoader
                photo={$currentPerusalStore.currentPhoto}
                lazy={false}
                size={photoSize}
                objectFit="contain"
            />
        {:else}
            <VideoLoader
                photoWhichIsReallyAVideo={$currentPerusalStore.currentPhoto}
            />
        {/if}
    {:else if $currentPerusalStore.currentGroup}
        <div class="interstitial">
            <Markdown markdown={$currentPerusalStore.currentGroup.description} />
        </div>
    {/if}
</div>
{#if $currentPerusalStore.currentPhoto && $metadataVisible}
    <div class="metadata">
        <!-- this could be made more data-driven, but for now, sticking with hand-crafted -->
        <div class="title"><span class="label">Title:</span> {$currentPerusalStore.currentPhoto.title}</div>
        <table class="photoMeta">
        <tr><td class="label">Original: </td><td>{$currentPerusalStore.currentPhoto.width}×{$currentPerusalStore.currentPhoto.height} ({HumanBytes($currentPerusalStore.currentPhoto.size)})</td></tr>
        <tr><td class="label">Uploaded: </td><td>{$currentPerusalStore.currentPhoto.uploadTimeStamp}</td></tr>
        </table>
        <div class="dlOrig">
            <a href="/api/photo/orig/{$currentPerusalStore.currentPhoto.id}">
                <Button margin="0 10px 0 0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="86 110 128 152 170 110" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="128" y1="39.97056" x2="128" y2="151.97056" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><path d="M224,136v72a8,8,0,0,1-8,8H40a8,8,0,0,1-8-8V136" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path></svg>
                    <div>Download Original</div>
                </Button>
            </a>
        </div>
        {#if $currentPerusalStore.currentPhoto.gpsLat && $currentPerusalStore.currentPhoto.gpsLon}
            <div class="photoMap">
                <LazyLoad loader={"PhotoMap"}
                    photos={[$currentPerusalStore.currentPhoto]}
                    exploreIconOnly={true}
                    popups={false}
                />
            </div>
            <div class="mapLinks">
                {"{"} <a target="_" href="https://www.openstreetmap.org/?mlat={$currentPerusalStore.currentPhoto.gpsLat}&mlon={$currentPerusalStore.currentPhoto.gpsLon}#map=18/{$currentPerusalStore.currentPhoto.gpsLat}/{$currentPerusalStore.currentPhoto.gpsLon}">OpenStreetMap</a>
                | <a target="_" href="https://www.google.com/maps/search/?api=1&query={$currentPerusalStore.currentPhoto.gpsLat},{$currentPerusalStore.currentPhoto.gpsLon}">Google Maps</a> {"}"}
            </div>
        {/if}

        <table class="photoMeta">
            <tr>
                <td class="label">Tags</td>
                <td class="value">{$currentPerusalStore.currentPhoto.tags.length > 0 ? $currentPerusalStore.currentPhoto.tags.join(", ") : "(None)"}</td>
            </tr>
            {#each metaDataMapping as meta}
                {#if $currentPerusalStore.currentPhoto[meta.key] != null}
                    <tr>
                        <td class="label">{meta.value}</td>
                        <td class="value">{meta.filter == undefined ? $currentPerusalStore.currentPhoto[meta.key] : meta.filter($currentPerusalStore.currentPhoto[meta.key])}</td>
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
