<script lang="ts">
    import { onMount } from "svelte";

    import L from "leaflet";
    import "leaflet/dist/leaflet.css";

    import "../../lib/Leaflet.markercluster/dist/leaflet.markercluster";
    import "../../lib/Leaflet.markercluster/dist/MarkerCluster.css";

    import type { Photo, PhotoStub } from "../pozzo.type";
    import { RunApi } from "../api";

    export let photoIDs: number[] = [];
    export let interactEnabled: boolean = false;
    export let boundsPadding: number = 15;

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

        mapMarkers = coords.map((c) => {
            const marker = L.marker(c);
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

<div class="map"
    bind:this={mapElement}
    on:click={() => setInteractEnabled(true) }
/>

<style>
    .map {
        width: 100%;
        height: 100%;

        background-color: rgb(85, 85, 85);
    }

    :global(.leaflet-container *) {
        font-family: 'Lato', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    }

    :global(.marker-cluster) {
        background-clip: padding-box;
        border-radius: 20px;

        background-color: rgb(51, 148, 226, 0.7);
    }

    :global(.marker-cluster div) {
        width: 30px;
        height: 30px;
        margin-left: 5px;
        margin-top: 5px;

        text-align: center;
        border-radius: 15px;
        font-size: 14px;
        background-color: rgba(20, 81, 131, 0.7);
    }

    :global(.marker-cluster span) {
        line-height: 30px;
    }
</style>
