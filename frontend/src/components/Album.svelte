<script lang="ts">
    import { onMount, onDestroy, setContext } from "svelte";

    import L from "leaflet";
    import "leaflet/dist/leaflet.css";

    import justifiedLayout from "justified-layout";
    import { Link, navigate } from "svelte-routing";

    import { RunApi } from "../api";
    import type { Album, Photo } from "../pozzo.type";
    import { GetImgPath } from "../util";
    import AlbumPhoto from "./AlbumPhoto.svelte";
    import PhotoContextMenu from "./PhotoContextMenu.svelte";
    import UploadZone from "./UploadZone.svelte";
    import { isLoggedInStore, frontendStateStore } from "../stores";
    import { albumContextMenuKey } from "../keys";

    export let identifier: number|string;

    async function getAlbum(_) {
        const res = await RunApi(`/album/view/${identifier}`, {
            params: {previews: 1},
            method: "POST",
            authorize: true,
        });
        if (res.success) {
            album = res.data;
        }
        else {
            if (res.code == 404) {
                navigate("/", {replace: true});
            }
            else {
                console.error(res);
            }
        }
    }

    async function updateMetaData(showMap: boolean) {
        const res = await RunApi(`/album/edit/${identifier}`, {
            params: {
                showMap: showMap
            },
            method: "POST",
            authorize: true
        });
        if (res.success) {
            // no-op; frontend already shows backend's reality
        }
        else {
            console.error(res);
        }
    }

    async function reorderAlbum(newOrder: number[]) {
        const res = await RunApi(`/album/reorder/${identifier}`, {
            params: {newOrdering: newOrder},
            method: "POST",
            authorize: true
        });
        if (res.success) {
            // no-op; frontend already shows backend's reality
        }
        else {
            console.error(res);
        }
    }

    function calculateLayout(width: number) {
        if (!width || !album) {
            return; // initial loads; don't worry yet
        }
        const aspects = album.photos.map(p => p.aspect);
        layout = justifiedLayout(aspects, {
            targetRowHeight: 300,
            containerWidth: width,
            containerPadding: 10,
            widowLayoutStyle: "center",
        });
    }

    // this is some ugly stuff, but don't anticipate needing to generalize further
    function isEventMetaKeyPress(evt: KeyboardEvent) {
        if (window.navigator.platform.startsWith("Mac")) {
            return evt.key == "Meta";
        }
        else {
            return evt.key == "Control";
        }
    }

    function isMetaKeyDownForEvent(evt: (KeyboardEvent|MouseEvent)) {
        if (window.navigator.platform.startsWith("Mac")) {
            return evt.metaKey;
        }
        else {
            return evt.ctrlKey;
        }
    }

    function handleKeyUp(evt: KeyboardEvent) {
        if (!$isLoggedInStore) {
            return;
        }
        if (isEventMetaKeyPress(evt)) {
            isMetaKeyDown = false;
        }
    }

    function handleKeyDown(evt: KeyboardEvent) {
        if (!$isLoggedInStore) {
            return;
        }
        if (isEventMetaKeyPress(evt)) {
            isMetaKeyDown = true;
        }
        if (contextMenuVisible) {
            return;
        }
        if (evt.key == "a" && isMetaKeyDownForEvent(evt)) {
            albumSelectedIndices = [...Array(album.photos.length).keys()];
            evt.preventDefault();
        }
        if (evt.key == "d" && isMetaKeyDownForEvent(evt)) {
            albumSelectedIndices = [];
            evt.preventDefault();
        }
    }

    function handlePhotoClick(evt: MouseEvent, pi: number) {
        if (!$isLoggedInStore) {
            return;
        }
        if (contextMenuVisible) {
            contextMenuVisible = false;
            evt.preventDefault();
            return;
        }
        if (!isMetaKeyDownForEvent(evt)) {
            return;
        }
        const selIdx = albumSelectedIndices.indexOf(pi);
        if (selIdx != -1) {
            albumSelectedIndices = albumSelectedIndices.filter(si => si != pi);
        }
        else {
            albumSelectedIndices = [...albumSelectedIndices, pi];
        }
        evt.preventDefault();
    }

    let albumSelectedIndices: number[] = [];
    let selectedPhotos: Photo[] = [];
    $: {
        selectedPhotos = albumSelectedIndices.map(pi => album.photos[pi]);
    }

    let contextMenuVisible = false;
    let clickLocation: number[] = [0, 0];
    setContext(albumContextMenuKey, {
        clickLocation: clickLocation,
        getSelectedPhotos: () => selectedPhotos
    });


    function handlePhotoContextMenu(evt: MouseEvent, pi: number) {
        // so if nothing is selected we auto-select the thing under the mouse
        //    (does not preventDefault so the event still bubbles to the album's handler)
        if (!$isLoggedInStore) {
            return;
        }
        if (albumSelectedIndices.length == 0) {
            albumSelectedIndices = [pi];
        }
    }
    function handleContextMenu(evt: MouseEvent) {
        if (!$isLoggedInStore) {
            return;
        }
        evt.preventDefault();
        if (contextMenuVisible) {
            contextMenuVisible = false;
        }
        else {
            clickLocation[0] = evt.clientX;
            clickLocation[1] = evt.clientY;
            contextMenuVisible = true;
        }
    }
    function contextMenuExecuted(_: CustomEvent) {
        contextMenuVisible = false;
        albumSelectedIndices = [];
        getAlbum(null);
    }

    let containerWidth: number;
    let album: Album = null;
    let layout = null;
    $: if (album) {calculateLayout(containerWidth);}

    onMount(async () => {
        await getAlbum(null);
        $frontendStateStore.currentAlbum = album;
    });

    onDestroy(() => {
        $frontendStateStore.currentAlbum = null;
    });

    let isMetaKeyDown = false;

    // this drag-and-drop stuff is the least svelte-y of this whole project
    //   I assume that could be fixed somehow, but it's not 100% obvious
    //   started off using native drag-and-drop, but it's too inconsistent
    //   across browsers :-/
    let draggedPhoto: Photo = null;
    let draggedPhotoSlot: HTMLDivElement = null;
    let dragShiftX = 0;
    let dragShiftY = 0;
    function startDragPhoto(evt: DragEvent, pi: number) {
        albumSelectedIndices = [];

        draggedPhoto = album.photos[pi];

        draggedPhotoSlot = document.createElement("div") as HTMLDivElement;
        draggedPhotoSlot.dataset.pidx = (evt.target as HTMLDivElement).dataset.pidx;
        draggedPhotoSlot.style.position = "fixed";
        draggedPhotoSlot.style.width  = "200px";
        draggedPhotoSlot.style.height = "200px";
        draggedPhotoSlot.style.display = "flex";
        draggedPhotoSlot.style.zIndex = "101";
        const img = new Image();
        img.style.maxWidth  = "200px";
        img.style.maxHeight = "200px";
        img.style.margin = "auto";
        img.src = GetImgPath("medium", draggedPhoto.hash, draggedPhoto.uniq);
        draggedPhotoSlot.append(img);

        dragShiftX = 100;
        dragShiftY = 100;

        document.body.append(draggedPhotoSlot);
        draggedPhotoSlot.style.left = (evt.pageX - dragShiftX) + "px";
        draggedPhotoSlot.style.top  = (evt.pageY - dragShiftY) + "px";
    }

    function handleWindowMouseMove(evt: MouseEvent) {
        if (draggedPhotoSlot != null) {
            draggedPhotoSlot.style.left = (evt.pageX - dragShiftX) + "px";
            draggedPhotoSlot.style.top  = (evt.pageY - dragShiftY) + "px";

            draggedPhotoSlot.style.display = "none";

            const el = document.elementFromPoint(evt.clientX, evt.clientY);
            const slot: HTMLDivElement = el.closest(".albumSlot");
            if (slot != null && slot.dataset.pidx != draggedPhotoSlot.dataset.pidx) {
                album.photos.splice(
                    Number(slot.dataset.pidx), 0,
                    album.photos.splice(Number(draggedPhotoSlot.dataset.pidx), 1)[0]
                );
                draggedPhotoSlot.dataset.pidx = slot.dataset.pidx;
                calculateLayout(containerWidth);
                album.photos = album.photos;
            }
            draggedPhotoSlot.style.display = "flex";
        }
    }

    function handleWindowMouseUp(evt: MouseEvent) {
        if (draggedPhotoSlot != null) {
            // put it back in its place
            draggedPhoto = null;
            draggedPhotoSlot.remove();
            draggedPhotoSlot = null;

            const newOrder = album.photos.map((p) => p.id);
            reorderAlbum(newOrder);
        }
    }

    $: {
        if (album && album.isPrivate && !$isLoggedInStore) {
            navigate("/", {replace: true});
        }
    }

    let mapDiv: HTMLDivElement;
    let map: L.Map;
    let markers: L.Marker[] = [];
    $: {
        if (mapDiv) {
            if (map != null) {
                markers.forEach((marker) => {
                    map.removeLayer(marker);
                })

                let coords = [];
                album.photos.forEach((p) => {
                    if (p.latitude != null && p.longitude != null) {
                        const marker = L.marker([p.latitude, p.longitude]);
                        marker.addTo(map);
                        markers = [...markers, marker];
                        coords = [...coords, [p.latitude, p.longitude]];
                    }
                });
                const bounds = L.latLngBounds(coords);
                map.fitBounds(bounds, {padding: [15, 15]});
            }
            else {
                L.Marker.prototype.options.icon = L.icon({
                    iconUrl: "/img/marker-icon.png",
                    iconRetinaUrl: "/img/marker-icon-2x.png",
                    shadowUrl: "/img/marker-shadow.png",
                    iconSize: [24,36],
                    iconAnchor: [12,36]
                });
                map = L.map(mapDiv, {
                    zoomControl: false
                });
                map.dragging.disable();
                map.touchZoom.disable();
                map.doubleClickZoom.disable();
                map.scrollWheelZoom.disable();
                map.boxZoom.disable();
                map.keyboard.disable();
                if (map.tap) map.tap.disable();
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                let coords = [];
                album.photos.forEach((p) => {
                    if (p.latitude != null && p.longitude != null) {
                        const marker = L.marker([p.latitude, p.longitude]);
                        marker.addTo(map);
                        markers = [...markers, marker];
                        coords = [...coords, [p.latitude, p.longitude]];
                    }
                });
                const bounds = L.latLngBounds(coords);
                map.fitBounds(bounds, {padding: [15, 15]});
            }
        }
        else {
            map = null;
            markers = [];
        }
    }

    function enableMapInteractions() {
        map.dragging.enable();
        map.touchZoom.enable();
        map.doubleClickZoom.enable();
        map.scrollWheelZoom.enable();
        map.boxZoom.enable();
        map.keyboard.enable();
        if (map.tap) map.tap.enable();
    }

    $: {
        // this is not how reactive functions should be working :-/
        if (album && album.showMap) {
            updateMetaData(album.showMap);
        }
        else if (album) {
            updateMetaData(album.showMap);
        }
    }

</script>


<svelte:window
    on:keydown={handleKeyDown}
    on:keyup={handleKeyUp}
    on:mousemove={handleWindowMouseMove}
    on:mouseup={handleWindowMouseUp}
/>

<div class="album">
{#if album}
    {#if $isLoggedInStore && draggedPhoto == null}
        <UploadZone on:done={() => getAlbum(null)} />
    {/if}

    <div class="titleRow">
        <h2>{album.title}</h2>
        <div class="spacer"></div>
        {#if $isLoggedInStore}
            <div class="button map"
                class:toggled={album.showMap}
                on:click={() => album.showMap = !album.showMap}
            >
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="96 184 32 200 32 56 96 40" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><polygon points="160 216 96 184 96 40 160 72 160 216" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polygon><polyline points="160 72 224 56 224 200 160 216" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>            </div>
        {/if}
    </div>
    {#if album.showMap}
        <div class="albumMap" bind:this={mapDiv} on:click={enableMapInteractions}></div>
    {/if}
    <div class="albumPhotos"
            bind:clientWidth={containerWidth}
            on:contextmenu={handleContextMenu}
            class:selectionMode={isMetaKeyDown}
            style={`height: ${layout?.containerHeight || 0}px;`}
        >
        {#if contextMenuVisible}
            <PhotoContextMenu
                currentAlbum={album}
                on:done={contextMenuExecuted}
            />
        {/if}
        {#if layout}
            {#each album.photos as photo, pi}
                <div class="albumSlot"
                    style={`top: ${layout.boxes[pi].top}px; left: ${layout.boxes[pi].left}px; width: ${layout.boxes[pi].width}px; height: ${layout.boxes[pi].height}px;`}
                    on:click={(evt) => handlePhotoClick(evt, pi)}
                    on:contextmenu={(evt) => handlePhotoContextMenu(evt, pi)}
                    data-pidx={pi}
                    draggable="true"
                    on:dragstart|preventDefault={(evt) => startDragPhoto(evt, pi)}
                    class:dragged={draggedPhoto === photo}
                    class:selected={albumSelectedIndices.indexOf(pi) >= 0}
                >
                    <Link to={`/album/${album.slug}/${album.photos[pi].id}`} getProps={() => ({draggable: false})}>
                        <AlbumPhoto
                            photo={photo}
                            size="medium"
                            dims={layout.boxes[pi]}
                        />
                    </Link>
                </div>
            {/each}
        {/if}
    </div>
{/if}
</div>

<style>
    .album {
        width: 100%;
    }
    .albumPhotos {
        position: relative;
        max-width: 95%;
        height: 50px;
        margin-left: auto;
        margin-right: auto;
    }

    .albumSlot {
        cursor: pointer;
        position: absolute;
        overflow: hidden;
    }

    .albumSlot.selected {
        outline: 3px solid white;
    }

    .albumSlot.dragged {
        opacity: 0.4;
    }

    .albumPhotos.selectionMode .albumSlot {
        cursor: default;
    }

    .titleRow {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        margin-right: 30px;
    }

    h2 {
        font-size: 3em;
        padding-left: 20px;
    }

    .spacer {
        flex-grow: 1;
    }

    .button {
        cursor: pointer;
    }

    .button.toggled {
        color: rgb(62, 62, 218);
    }

    svg {
        width: 30px;
        height: 30px;
    }

    .albumMap {
        height: 400px;
        margin: 10px 0px;
        width: 100%;
        background-color: rgb(85, 85, 85);
    }

</style>
