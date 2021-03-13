<script lang="ts">
    import { Link } from "svelte-routing";

    import justifiedLayout from "justified-layout";

    import type { Album, Photo } from "../pozzo.type";
    import { AlbumType } from "../pozzo.type";
    import { isLoggedInStore, siteData } from "../stores";
    import { RunApi } from "../api";
    import LazyLoad from "./LazyLoad.svelte";
    import Button from "./Button.svelte";
    import NavCollection from "./NavCollection.svelte";
    import NavPhoto from "./NavPhoto.svelte";

    export let typeOfAlbum: AlbumType = AlbumType.Album;

    let albumList: Album[];
    let albumCovers: Photo[];

    function getBlankPhoto() {
        // annoying to spec out all this nonsense :-/
        return {
            id: -1,
            title: "",
            aspect: 4.0 / 3.0,
            hash: "",
            uniq: "",
            blurHash: "",
            isVideo: false,
            uploadTimeStamp: 0,
            uploadedBy: 0,
            originalFilename: "",
            size: 0,
            width: 0,
            height: 0,
            tags: [],
            make: null,
            model: null,
            lens: null,
            mime: null,
            creationDate: null,
            subjectArea: null,
            aperture: null,
            iso: null,
            shutterSpeed: null,
            gpsLat: null,
            gpsLon: null,
            gpsAlt: null,
        };
    }

    async function getAlbumList(loggedIn: boolean) {
        if (typeOfAlbum == AlbumType.Album) {
            const res = await RunApi("/album/list", {
                authorize: loggedIn
            });
            if (res.success) {
                albumList = res.data;
                albumList.forEach(al => {
                    al.type = AlbumType.Album;
                });
            }
            else {
                console.error(res);
            }
        }
        else if (typeOfAlbum == AlbumType.Dynamic) {
            const all: Album = {
                id: -1,
                title: "[all]",
                slug: "all",
                type: AlbumType.Dynamic,
                isPrivate: !$siteData.dynamicPublic,
                hasMap: false,
                coverPhoto: null,
                photoGroups: [],
                highestIndex: -1,
                description: "Every photo on the site",
            };
            const unsorted: Album = {
                id: -1,
                title: "[unsorted]",
                slug: "unsorted",
                type: AlbumType.Dynamic,
                isPrivate: !$siteData.dynamicPublic,
                hasMap: false,
                coverPhoto: null,
                photoGroups: [],
                highestIndex: -1,
                description: "Any photo that has not been put in an album",
            };
            albumList = [all, unsorted];
        }
    }
    $: getAlbumList($isLoggedInStore)

    function assemblePreviews(alist: Album[]) {
        if (!alist) {
            return;
        }
        albumCovers = albumList.map(a => {
            let pr: Photo = null;
            if (a.coverPhoto) {
                pr = a.coverPhoto;
            }
            else {
                pr = getBlankPhoto();
            }
            pr.id = a.id;
            pr.title = a.title;
            return pr;
        });
    }
    $: assemblePreviews(albumList)


    let containerWidth: number;
    let layout = null;

    function calculateLayout(width: number, photos: Photo[]) {
        if (!width || !photos) {
            return; // initial loads; don't worry yet
        }

        const aspects = photos.map((acs) => {
            if (acs != null) {
                return acs.aspect;
            }
            return 4.0 / 3.0;
        });

        layout = justifiedLayout(aspects, {
            targetRowHeight: 300,
            containerWidth: width,
            containerPadding: 10,
            widowLayoutStyle: "center",
        });
    }
    $: if (albumList) calculateLayout(containerWidth, albumCovers)

    let addingNew = false;
    let reordering = false;

    async function handleAlbumReorder(evt: CustomEvent) {
        const res = await RunApi("/album/reorderList/", {
            params: {newOrdering: evt.detail.newPhotos.map(ps => ps.id)},
            method: "POST",
            authorize: true
        });
        if (res.success) {
            albumCovers = evt.detail.newPhotos;
        }
        else {
            console.error(res);
        }
    }
</script>

{#if addingNew}
    <LazyLoad loader={"NewAlbumPrompt"}
        on:dismissed={() => addingNew = false}
        on:done={() => {addingNew = false; getAlbumList($isLoggedInStore);}}
    />
{/if}

<div class="albumList">
    <div class="header">
        {#if typeOfAlbum == AlbumType.Album}
            <h2>Albums</h2>
            {#if $isLoggedInStore}
                <Button
                    title="Add Album"
                    margin="0 0 0 10px"
                    on:click={() => addingNew = true}
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></circle><line x1="88" y1="128" x2="168" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="128" y1="88" x2="128" y2="168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
                </Button>
                {#if albumList?.length > 1}
                    <div class="reorderButton" class:toggled={reordering}>
                        <Button
                            margin="0 0 0 0"
                            on:click={() => {reordering = !reordering}}
                            title={`${reordering ? "Exit" : "Enter"} Edit Mode`}
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="192 144 224 176 192 208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="32" y1="176" x2="224" y2="176" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><polyline points="64 112 32 80 64 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></polyline><line x1="224.00006" y1="80" x2="32.00006" y2="80" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line></svg>
                        </Button>
                    </div>
                {/if}
            {/if}
        {:else if typeOfAlbum == AlbumType.Dynamic}
            <h2>Dynamic Albums</h2>
            {#if $isLoggedInStore}
                <Button
                    margin="0 0 0 10px"
                    title={$siteData.dynamicPublic ? "Make Private" : "Make Public"}
                    on:click={() => {$siteData.dynamicPublic = !$siteData.dynamicPublic; /*updateMetaData();*/}}
                >
                    {#if $siteData.dynamicPublic}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M128,55.99219C48,55.99219,16,128,16,128s32,71.99219,112,71.99219S240,128,240,128,208,55.99219,128,55.99219Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path><circle cx="128" cy="128" r="32" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></circle></svg>
                    {:else}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><line x1="201.14971" y1="127.30467" x2="223.95961" y2="166.81257" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="154.18201" y1="149.26298" x2="161.29573" y2="189.60689" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="101.72972" y1="149.24366" x2="94.61483" y2="189.59423" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><line x1="54.80859" y1="127.27241" x2="31.88882" y2="166.97062" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></line><path d="M31.99943,104.87509C48.81193,125.68556,79.63353,152,128,152c48.36629,0,79.18784-26.31424,96.00039-47.12468" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="24"></path></svg>
                    {/if}
                </Button>
            {/if}
        {/if}
    </div>


    {#if albumList}
        {#if reordering}
            <LazyLoad loader={"EditableLayout"}
                photoList={albumCovers}
                on:reordered={handleAlbumReorder}
            />
        {:else}
        <div class="albumListDisplay"
            bind:clientWidth={containerWidth}
            style={`height: ${layout?.containerHeight || 0}px;`}
        >
            {#if albumList.length == 0 && typeOfAlbum == AlbumType.Album}
                <div>(No albums on this site… yet.)</div>
            {/if}

            {#if layout}
                <NavCollection photos={albumCovers}>
                {#each albumList as album, ai}
                    <Link to={`/${album.type}/${album.slug}`}>
                        <NavPhoto size="medium"
                            photo={albumCovers[ai]}
                            layoutDims={layout.boxes[ai]}
                            textOverlay={album.title}
                        />
                    </Link>
                {/each}
                </NavCollection>
            {/if}
        </div>
        {/if}
    {/if}
</div>

<style>
    .albumList {
        margin-top: 1em;
    }

    h2 {
        margin: 0 0 0 15px;
    }

    .header {
        display: flex;
        align-items: center;
    }

    .albumListDisplay {
        position: relative;
        max-width: 95%;
        height: 50px;
        margin-left: auto;
        margin-right: auto;
    }

    .reorderButton {
        margin-left: 5px;
        max-width: 50px;

        padding: 5px;
    }

    .reorderButton.toggled {
        background-color: var(--edit-color);
    }
</style>
