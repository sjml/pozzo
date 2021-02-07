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
        if (evt.key == "a" && evt.metaKey) {
            $albumSelectionStore = [...Array(album.photos.length).keys()];
            evt.preventDefault();
        }
        if (evt.key == "d" && evt.metaKey) {
            $albumSelectionStore = [];
            evt.preventDefault();
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

    $: getAlbum($loginCredentialStore)
</script>


<svelte:window
    on:keydown={handleKeydown}
/>


{#if album}
    {#if $loginCredentialStore.length > 0}
        <UploadZone on:done={() => getAlbum(null)} />
    {/if}

    <h2>{album.title}</h2>

    <div class="albumPhotos"
            bind:clientWidth={containerWidth}
            on:contextmenu={handleContextMenu}
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
                <!-- <Link to={`/album/${identifier}/${photo.id}`} > -->
                    <AlbumPhoto
                        photo={photo}
                        photoID={pi}
                        size="medium"
                        dims={layout.boxes[pi]}
                    />
                <!-- </Link> -->
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

    h2 {
        font-size: 3em;
        padding-left: 20px;
    }
</style>
