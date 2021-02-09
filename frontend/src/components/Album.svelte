<script lang="ts">
    import { onMount, onDestroy, setContext } from "svelte";

    import justifiedLayout from "justified-layout";
    import { navigate } from "svelte-routing";

    import { RunApi } from "../api";
    import type { Album, Photo } from "../pozzo.type";
    import { GetImgPath } from "../util";
    import AlbumPhoto from "./AlbumPhoto.svelte";
    import PhotoContextMenu from "./PhotoContextMenu.svelte";
    import UploadZone from "./UploadZone.svelte";
    import { isLoggedInStore, albumSelectionStore, currentAlbumStore, albumDragStore } from "../stores";
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
            $albumSelectionStore = [...Array(album.photos.length).keys()];
            evt.preventDefault();
        }
        if (evt.key == "d" && isMetaKeyDownForEvent(evt)) {
            $albumSelectionStore = [];
            evt.preventDefault();
        }
    }

    function handlePhotoClick(evt: MouseEvent, pi: number) {
        if (!$isLoggedInStore) {
            return;
        }
        if (contextMenuVisible) {
            contextMenuVisible = false;
            return;
        }
        if (!isMetaKeyDownForEvent(evt)) {
            return;
        }
        const selIdx = $albumSelectionStore.indexOf(pi);
        if (selIdx != -1) {
            $albumSelectionStore = $albumSelectionStore.filter(si => si != pi);
        }
        else {
            $albumSelectionStore = [...$albumSelectionStore, pi];
        }
    }

    let selectedPhotos: Photo[] = [];
    $: {
        selectedPhotos = $albumSelectionStore.map(pi => album.photos[pi]);
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
        if ($albumSelectionStore.length == 0) {
            $albumSelectionStore = [pi];
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
        $albumSelectionStore = [];
        getAlbum(null);
    }

    let containerWidth: number;
    let album: Album = null;
    let layout = null;
    $: if (album) {calculateLayout(containerWidth);}

    onMount(async () => {
        await getAlbum(null);
        $currentAlbumStore = album;
    });

    onDestroy(() => {
        $currentAlbumStore = null;
        $albumDragStore = null;
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
        $albumSelectionStore = [];

        $albumDragStore = album.photos[pi];

        draggedPhoto = album.photos[pi];

        draggedPhotoSlot = document.createElement("div") as HTMLDivElement;
        draggedPhotoSlot.dataset.pidx = (evt.target as HTMLDivElement).dataset.pidx;
        draggedPhotoSlot.style.position = "fixed";
        draggedPhotoSlot.style.width  = "200px";
        draggedPhotoSlot.style.height = "200px";
        draggedPhotoSlot.style.display = "flex";
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
</script>


<svelte:window
    on:keydown={handleKeyDown}
    on:keyup={handleKeyUp}
    on:mousemove={handleWindowMouseMove}
    on:mouseup={handleWindowMouseUp}
/>

{#if album}
    {#if $isLoggedInStore}
        <UploadZone on:done={() => getAlbum(null)} />
    {/if}

    <h2>{album.title}</h2>

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
                >
                    <AlbumPhoto
                        photo={photo}
                        photoIdxInAlbum={pi}
                        size="medium"
                        dims={layout.boxes[pi]}
                    />
                </div>
            {/each}
        {/if}
    </div>
{/if}


<style>
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
    }

    .albumSlot.dragged {
        opacity: 0.4;
    }

    .albumPhotos.selectionMode .albumSlot {
        cursor: default;
    }

    h2 {
        font-size: 3em;
        padding-left: 20px;
    }

</style>
