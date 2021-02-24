<script lang="ts">
    import { onMount } from "svelte";
    import { fade } from "svelte/transition";

    import L from "leaflet";
    import "leaflet/dist/leaflet.css";

    import "../../lib/Leaflet.markercluster/dist/leaflet.markercluster";
    import "../../lib/Leaflet.markercluster/dist/MarkerCluster.css";

    import type { Photo, PhotoStub } from "../pozzo.type";
    import { RunApi } from "../api";
    import { currentAlbumStore } from "../stores";
    import { GetImgPath } from "../util";

    export let photoIDs: number[] = [];
    export let interactEnabled: boolean = false;
    export let boundsPadding: number = 15;
    export let exploreIconOnly: boolean = false;
    export let popups: boolean = true;

    let photos: Photo[] = [];
    async function getPhotos(ids: number[]) {
        const res = await RunApi("/photo/set", {
            method: "POST",
            authorize: true,
            params: {
                photoIDs: ids
            }
        });
        if (res.success) {
            photos = res.data;
        }
        else {
            console.error(res);
        }
    }
    $: getPhotos(photoIDs)

    let mapElement: HTMLDivElement;
    let map: L.Map;
    let mapMarkers: L.Marker[] = [];
    let markerCluster: L.MarkerClusterGroup = null;

    onMount(() => {
        L.Marker.prototype.options.icon = L.icon({
            iconUrl: "/img/marker-icon.png",
            iconRetinaUrl: "/img/marker-icon-2x.png",
            shadowUrl: "/img/marker-shadow.png",
            iconSize: [24,36],
            iconAnchor: [12,36]
        });

        map = L.map(mapElement, {
            zoomControl: false,
            minZoom: 2,
        });

        map.attributionControl.setPrefix("");

        const accessToken = "pk.eyJ1Ijoic2ptbCIsImEiOiJja2t0ejBjcjMwdDh3Mm5wYmE5NmFzNXlxIn0.pdQ6u95-6A19aTZMBxnnyA";
        L.tileLayer(`https://api.mapbox.com/styles/v1/sjml/ckl4ra6sc072918mucocsgswa/tiles/{z}/{x}/{y}@2x?access_token=${accessToken}`, {
            attribution: "Data &copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> | Tiles &copy; <a href=\"https://mapbox.com\">Mapbox</a>",
            subdomains: "abcd",
            tileSize: 512,
            zoomOffset: -1,
        }).addTo(map);

        setInteractEnabled(interactEnabled);
    });

    function setInteractEnabled(on: boolean) {
        if (map == null) {
            return;
        }

        if (on) {
            map.dragging.enable();
            map.touchZoom.enable();
            map.doubleClickZoom.enable();
            map.scrollWheelZoom.enable();
            map.boxZoom.enable();
            map.keyboard.enable();
            if (map.tap) map.tap.enable();
        }
        else {
            map.dragging.disable();
            map.touchZoom.disable();
            map.doubleClickZoom.disable();
            map.scrollWheelZoom.disable();
            map.boxZoom.disable();
            map.keyboard.disable();
            if (map.tap) map.tap.disable();
        }
    }
    $: setInteractEnabled(interactEnabled)

    function setMarkers(photoList: Photo[]) {
        if (map == null) {
            return;
        }
        if (markerCluster == null) {
            markerCluster = L.markerClusterGroup({
                showCoverageOnHover: false,
                maxClusterRadius: 50,
            });
        }

        map.removeLayer(markerCluster);
        mapMarkers.forEach((m) => {
            markerCluster.removeLayer(m);
        });

        let placedPhotos = photoList.map((p) => {
            if (p.gpsLat != null && p.gpsLon != null) {
                return p;
            }
            return null;
        });
        placedPhotos = placedPhotos.filter(p => p !== null);
        let coords = placedPhotos.map((p) => {
            return L.latLng(p.gpsLat, p.gpsLon);
        });

        mapMarkers = coords.map((c, ci) => {
            const marker = L.marker(c);

            if (popups) {
                const popupImgUrl = GetImgPath("small2x", placedPhotos[ci].hash, placedPhotos[ci].uniq);
                marker.bindPopup(
                    L.popup({
                        closeButton: false,
                        className: "photoMapPopup",
                        minWidth: 150,
                        autoPanPadding: [150, 150],
                        autoClose: true,
                    })
                    .setContent(`<a draggable="false" href="/album/${$currentAlbumStore.slug}/${placedPhotos[ci].id}"><img draggable="false" src="${popupImgUrl}" alt="${placedPhotos[ci].title}"/></a>`)
                );
            }

            markerCluster.addLayer(marker);
            return marker;
        });
        map.addLayer(markerCluster);

        if (coords.length > 0) {
            map.fitBounds(L.latLngBounds(coords), {
                padding: [boundsPadding, boundsPadding]
            });
        }
    }
    $: setMarkers(photos)
</script>

<div class="mapContainer">
    <div class="map"
        bind:this={mapElement}
        on:click={() => interactEnabled = true }
    />
    {#if !interactEnabled}
        <div class="interactionIndicator"
            transition:fade={{duration: 150}}
            on:click={() => interactEnabled = true }
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><circle cx="128" cy="128" r="92" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></circle><line x1="128" y1="36" x2="128" y2="76" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="36" y1="128" x2="76" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="128" y1="220" x2="128" y2="180" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="220" y1="128" x2="180" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
            {#if !exploreIconOnly}
                Click/Tap to Explore
            {/if}
        </div>
    {/if}
</div>

<style>
    .mapContainer {
        position: relative;
        width: 100%;
        height: 100%;

        background-color: var(--ui-light-color);
    }

    .map {
        width: 100%;
        height: 100%;
    }

    .interactionIndicator {
        position: absolute;
        top: 0;
        z-index: 100000;

        background-color: var(--ui-medium-color);
        padding: 5px;
        opacity: 0.5;

        display: flex;
        align-items: center;
    }

    .interactionIndicator svg {
        width: var(--button-size);
        margin: 0;
    }

    :global(.leaflet-container *) {
        font-family: 'Lato', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    }

    :global(.marker-cluster) {
        background-clip: padding-box;
        border-radius: 20px;

        background-color: var(--marker-cluster-background);
    }

    :global(.marker-cluster div) {
        width: 30px;
        height: 30px;
        margin-left: 5px;
        margin-top: 5px;

        text-align: center;
        border-radius: 15px;
        font-size: 14px;
        background-color: var(--marker-cluster-foreground);
    }

    :global(.marker-cluster span) {
        line-height: 30px;
    }

    :global(.photoMapPopup img) {
        max-width: 150px;
        margin-bottom: -3px;
    }

    :global(.leaflet-popup-content) {
        margin: 3px !important;
        overflow: hidden;
    }

    :global(.leaflet-popup-content-wrapper) {
        border-radius: 5px !important;
    }
</style>
