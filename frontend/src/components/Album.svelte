<script lang="ts">
    import { onMount, onDestroy, setContext, tick } from "svelte";
    import { Link, navigate } from "svelte-routing";

    import justifiedLayout from "justified-layout";

    import type { Album, Photo } from "../pozzo.type";
    import { isLoggedInStore, frontendStateStore } from "../stores";
    import { albumContextMenuKey } from "../keys";
    import { RunApi } from "../api";
    import AlbumPhoto from "./AlbumPhoto.svelte";
    import PhotoContextMenu from "./PhotoContextMenu.svelte";
    import UploadZone from "./UploadZone.svelte";
    import Button from "./Button.svelte";
    import PhotoMap from "./PhotoMap.svelte";
    import EditableLayout from "./EditableLayout.svelte";
    import Markdown from "./Markdown.svelte";

    export let albumSlug: number|string;


    onDestroy(() => {
        $frontendStateStore.currentAlbum = null;
    });
    $: $frontendStateStore.currentAlbum = album;


    let album: Album = null;

    async function getAlbum(): Promise<Album> {
        const res = await RunApi(`/album/view/${albumSlug}`, {
            params: {previews: 1},
            method: "POST",
            authorize: true,
        });
        if (res.success) {
            album = res.data;
            return album;
        }
        else {
            if (res.code == 404) {
                navigate("/", {replace: true});
            }
            else {
                console.error(res);
            }
            return null;
        }
    }

    async function updateMetaData() {
        if (album.showMap == null || album.isPrivate == null) {
            // probably just initial load
            return;
        }
        const res = await RunApi(`/album/edit/${albumSlug}`, {
            params: {
                showMap: album.showMap,
                isPrivate: album.isPrivate,
                description: album.description,
                title: album.title,
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
        const res = await RunApi(`/album/reorder/${albumSlug}`, {
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

    $: {
        if (album && album.isPrivate && !$isLoggedInStore) {
            navigate("/", {replace: true});
        }
    }


    let containerWidth: number;
    let layout = null;

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

    $: if (album) calculateLayout(containerWidth)


    let albumSelectedIndices: number[] = [];
    let selectedPhotos: Photo[] = [];
    let isMetaKeyDown = false;

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
            if (forcedSingleSelection) {
                forcedSingleSelection = false;
                albumSelectedIndices = [];
            }
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

    $: {
        selectedPhotos = albumSelectedIndices.map(pi => album.photos[pi]);
    }


    let contextMenuVisible = false;
    let clickLocation: number[] = [0, 0];
    let forcedSingleSelection = false;

    setContext(albumContextMenuKey, {
        clickLocation: clickLocation,
        getSelectedPhotos: () => selectedPhotos
    });

    async function handlePhotoContextMenu(evt: MouseEvent, pi: number) {
        // so if nothing is selected we auto-select the thing under the mouse
        //    (does not preventDefault so the event still bubbles to the album's handler)
        if (!$isLoggedInStore) {
            return;
        }
        if (albumSelectedIndices.length == 0) {
            albumSelectedIndices = [pi];
            forcedSingleSelection = true;
        }
    }

    function handleContextMenu(evt: MouseEvent) {
        if (!$isLoggedInStore) {
            return;
        }
        evt.preventDefault();
        if (contextMenuVisible) {
            contextMenuVisible = false;
            if (forcedSingleSelection) {
                forcedSingleSelection = false;
                albumSelectedIndices = [];
            }
        }
        else {
            clickLocation[0] = evt.clientX;
            clickLocation[1] = evt.clientY;
            contextMenuVisible = true;
        }
    }

    function contextMenuExecuted(_: CustomEvent) {
        contextMenuVisible = false;
        if (forcedSingleSelection) {
            forcedSingleSelection = false;
            albumSelectedIndices = [];
        }
        albumSelectedIndices = [];
        getAlbum();
    }


    let reordering: boolean = false;
    let editing: boolean = false;
    let rawDesc: string;
    let rawTitle: string;

    function handleAlbumReorder(evt: CustomEvent) {
        album.photos = evt.detail.newOrder;
        reorderAlbum(album.photos.map(p => p.id));
    }

    async function toggleEditing() {
        if (!editing) {
            rawTitle = album.title;
            rawDesc = album.description;
            editing = true;
        }
        else {
            album.title = rawTitle;
            album.description = rawDesc;
            await updateMetaData();
            editing = false;
        }
    }

</script>


<svelte:window
    on:keydown={handleKeyDown}
    on:keyup={handleKeyUp}
/>

<div class="album">
{#await getAlbum()}
    Loading…
{:then album}
    {#if $isLoggedInStore && !reordering}
        <UploadZone on:done={() => getAlbum()} />
    {/if}

    <div class="titleRow">
        {#if editing}
            <h2 contenteditable="true" class="editing" bind:innerHTML={rawTitle}></h2>
        {:else}
            <h2>{album.title}</h2>
        {/if}
        {#if $isLoggedInStore}
            <Button
                margin="0 0 0 10px"
                title={editing ? "Commit Changes" : "Edit Title and Description"}
                isToggled={editing}
                on:click={toggleEditing}
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M92.68629,216H48a8,8,0,0,1-8-8V163.31371a8,8,0,0,1,2.34315-5.65686l120-120a8,8,0,0,1,11.3137,0l44.6863,44.6863a8,8,0,0,1,0,11.3137l-120,120A8,8,0,0,1,92.68629,216Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path><line x1="136" y1="64" x2="192" y2="120" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="44" y1="156" x2="100" y2="212" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
            </Button>
            <div class="spacer"></div>
            <Button
                margin="0 0 0 10px"
                title={album.isPrivate ? "Make Public" : "Make Private"}
                on:click={() => {album.isPrivate = !album.isPrivate; updateMetaData();}}
            >
                {#if !album.isPrivate}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M128,55.99219C48,55.99219,16,128,16,128s32,71.99219,112,71.99219S240,128,240,128,208,55.99219,128,55.99219Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path><circle cx="128" cy="128" r="32" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></circle></svg>
                {:else}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><line x1="201.14971" y1="127.30467" x2="223.95961" y2="166.81257" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="154.18201" y1="149.26298" x2="161.29573" y2="189.60689" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="101.72972" y1="149.24366" x2="94.61483" y2="189.59423" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="54.80859" y1="127.27241" x2="31.88882" y2="166.97062" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><path d="M31.99943,104.87509C48.81193,125.68556,79.63353,152,128,152c48.36629,0,79.18784-26.31424,96.00039-47.12468" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path></svg>
                {/if}
            </Button>
            {#if album.photos.length > 0}
                <Button
                    margin="0 0 0 10px"
                    isToggled={album.showMap}
                    title={`${album.showMap ? "Hide" : "Show"} Map`}
                    on:click={() => {album.showMap = !album.showMap; updateMetaData();}}
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="96 184 32 200 32 56 96 40" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><polygon points="160 216 96 184 96 40 160 72 160 216" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polygon><polyline points="160 72 224 56 224 200 160 216" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline></svg>
                </Button>
            {/if}
        {/if}
    </div>

    {#if editing}
        <textarea cols="500" rows="10" class="description editing" bind:value={rawDesc}></textarea>
    {:else}
        <div class="description"
        >
            <Markdown markdown={album.description} />
        </div>
    {/if}

    {#if album.showMap && album.photos.length > 0}
        <div class="albumMap">
            <PhotoMap photos={album.photos} />
        </div>
    {/if}

    {#if $isLoggedInStore && album.photos.length > 1}
        <div class="reorderButton" class:toggled={reordering}>
            <Button
                margin="0 0 0 10px"
                on:click={() => {reordering = !reordering}}
                title={`${reordering ? "Exit" : "Enter"} Edit Mode`}
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="192 144 224 176 192 208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="32" y1="176" x2="224" y2="176" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="64 112 32 80 64 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="224.00006" y1="80" x2="32.00006" y2="80" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
            </Button>
        </div>
    {/if}

    {#if reordering}
        <EditableLayout
            photoList={album.photos}
            on:reordered={handleAlbumReorder}
        />
    {:else}
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

            {#if album.photos.length == 0}
                <div>(No photos in this album… yet.)</div>
            {/if}

            {#if layout}
                {#each album.photos as photo, pi}
                    <Link to={`/${album.slug}/${album.photos[pi].id}`} getProps={() => ({draggable: false})} >
                        <div class="albumSlot"
                            style={`top: ${layout.boxes[pi].top}px; left: ${layout.boxes[pi].left}px; width: ${layout.boxes[pi].width}px; height: ${layout.boxes[pi].height}px;`}
                            on:click={(evt) => handlePhotoClick(evt, pi)}
                            on:contextmenu={(evt) => handlePhotoContextMenu(evt, pi)}
                            class:selected={albumSelectedIndices.indexOf(pi) >= 0}
                        >
                            <AlbumPhoto
                                photo={photo}
                                size="medium"
                                dims={layout.boxes[pi]}
                            />
                        </div>
                    </Link>
                {/each}
            {/if}

        </div>
    {/if}
{/await}
</div>

<style>
    .album {
        width: 100%;
    }

    .albumPhotos {
        position: relative;
        height: 50px;
        margin-left: auto;
        margin-right: auto;
    }

    .albumSlot {
        position: absolute;

        cursor: pointer;
        overflow: hidden;

        outline: 0px solid white;
        transition-property: outline;
        transition-duration: 150ms;
    }

    .albumSlot.selected {
        outline: 3px solid white;
    }

    .albumPhotos.selectionMode .albumSlot {
        cursor: default;
    }

    .titleRow {
        margin-right: 30px;

        display: flex;
        align-items: baseline;
    }

    .spacer {
        flex-grow: 1;
    }

    h2 {
        margin-left: 20px;

        font-size: 3em;
        padding: 5px;
    }

    h2.editing {
        background-color: white;
        color: black;
    }

    svg {
        width: 30px;
        height: 30px;
    }

    .albumMap {
        height: 400px;
        width: 100%;
        margin: 10px 0px;
    }

    .reorderButton {
        margin-left: 30px;
        max-width: 50px;

        padding: 10px 0 5px 0;
    }

    .reorderButton.toggled {
        background-color: rgb(101, 101, 252);
    }

    .description {
        max-width: 900px;
        margin: 0 auto 40px auto;

        font-size: 1.15em;
        line-height: 1.5;

        padding: 5px 20px;
    }

    .description :global(a) {
        text-decoration: underline;
    }

    .description.editing {
        background-color: white;
        color: black;
    }
</style>
