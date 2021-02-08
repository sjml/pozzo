<script lang="ts">
    import { onMount, onDestroy, setContext } from "svelte";

    import justifiedLayout from "justified-layout";
    import { navigate, Link } from "svelte-routing";

    import { RunApi } from "../api";
    import type { Album, Photo } from "../pozzo.type";
    import AlbumPhoto from "./AlbumPhoto.svelte";
    import PhotoContextMenu from "./PhotoContextMenu.svelte";
    import UploadZone from "./UploadZone.svelte";
    import { loginCredentialStore, albumSelectionStore, currentAlbumStore } from "../stores";
    import { albumContextMenuKey } from "../keys";

    export let identifier: number|string;

    async function getAlbum(_) {
        const res = await RunApi(`/album/view/${identifier}`, {
            params: {previews: 1},
            method: "POST",
            authorize: true,
        });
        if (res.success) {
            $albumSelectionStore = [];
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

    function handleKeydown(evt: KeyboardEvent) {
        if ($loginCredentialStore.length == 0) {
            return;
        }
        if (contextMenuVisible) {
            evt.preventDefault();
            return;
        }
        if (evt.key == "a" && evt.metaKey) {
            $albumSelectionStore = [...Array(album.photos.length).keys()];
            evt.preventDefault();
        }
        if (evt.key == "d" && evt.metaKey) {
            $albumSelectionStore = [];
            evt.preventDefault();
        }
    }

    function handlePhotoClick(evt: MouseEvent, pi: number) {
        if ($loginCredentialStore.length == 0) {
            return;
        }
        if (contextMenuVisible) {
            contextMenuVisible = false;
            return;
        }
        if (!evt.metaKey) {
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
    $: selectedPhotos = $albumSelectionStore.map(pi => album.photos[pi]);

    let contextMenuVisible = false;
    let clickLocation: number[] = [0, 0];
    setContext(albumContextMenuKey, {
        clickLocation: clickLocation,
        getSelectedPhotos: () => selectedPhotos
    });


    function handlePhotoContextMenu(evt: MouseEvent, pi: number) {
        // so if nothing is selected we auto-select the thing under the mouse
        //    (does not preventDefault so the event still bubbles to the album's handler)
        if ($loginCredentialStore.length == 0) {
            return;
        }
        if ($albumSelectionStore.length == 0) {
            $albumSelectionStore = [pi];
        }
    }
    function handleContextMenu(evt: MouseEvent) {
        if ($loginCredentialStore.length == 0) {
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
        getAlbum(null);
    }

    let containerWidth: number;
    let album: Album = null;
    let layout = null;
    $: calculateLayout(containerWidth);
    $: if (album) {calculateLayout(containerWidth);}

    onMount(async () => {
        await getAlbum(null);
        $currentAlbumStore = album;
    });

    onDestroy(() => {
        $currentAlbumStore = null;
    });

    let editMode = false;

    $: getAlbum($loginCredentialStore)
</script>


<svelte:window
    on:keydown={handleKeydown}
/>


{#if album}
    {#if $loginCredentialStore.length > 0 && !editMode}
        <UploadZone on:done={() => getAlbum(null)} />
    {/if}

    <h2>{album.title}</h2>

    {#if $loginCredentialStore.length > 0}
        <div class="controls">
            <div class="button reorder" class:on={editMode} on:click={() => editMode = !editMode}>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            </div>
        </div>
    {/if}

    <div class="albumPhotos"
            bind:clientWidth={containerWidth}
            on:contextmenu={handleContextMenu}
            class:editMode={editMode}
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
                    on:click={(evt) => handlePhotoClick(evt, pi)}
                    on:contextmenu={(evt) => handlePhotoContextMenu(evt, pi)}
                >
                    <AlbumPhoto
                        photo={photo}
                        photoID={pi}
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

    .albumPhotos.editMode {
        background-color: rgb(50, 53, 184);
        outline: 4px solid rgb(50, 53, 184);
    }

    h2 {
        font-size: 3em;
        padding-left: 20px;
    }

    .controls {
        padding-left: 60px;
    }

    .button {
        width: 40px;
        cursor: pointer;
        padding: 5px;
    }

    .button.on {
        background-color: rgb(50, 53, 184);
    }

</style>
