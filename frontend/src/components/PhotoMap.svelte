<script lang="ts">
    import { onMount } from "svelte";

    import L from "leaflet";
    import "leaflet/dist/leaflet.css";

    import type { Photo } from "../pozzo.type";

    export let photos: Photo[] = [];
    export let interactEnabled: boolean = false;
    export let boundsPadding: number = 15;

    let mapElement: HTMLDivElement;
    let map: L.Map;
    let mapMarkers: L.Marker[] = [];

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

        L.tileLayer("https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png", {
            attribution: "Data &copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors | Tiles &copy; <a href=\"https://carto.com/attributions\">CARTO</a>",
        }).addTo(map);

        setMarkers(photos);
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
        mapMarkers.forEach((m) => {
            map.removeLayer(m);
        });

        let placedPhotos = photoList.map((p) => {
            if (p.latitude != null && p.longitude != null) {
                return p;
            }
        });
        let coords = placedPhotos.map((p) => {
            return L.latLng(p.latitude, p.longitude);
        });
        mapMarkers = coords.map((c) => {
            const marker = L.marker(c);
            marker.addTo(map);
            return marker;
        });

        map.fitBounds(L.latLngBounds(coords), {
            padding: [boundsPadding, boundsPadding]
        });
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
</style>
