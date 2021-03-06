<script lang="ts">
    import { onDestroy } from "svelte";
    import { Router, Route, navigate } from "svelte-routing";

    import type { Album, Photo } from "../pozzo.type";
    import { AlbumType } from "../pozzo.type";
    import { currentAlbumStore, currentPerusalStore, isLoggedInStore, siteData } from "../stores";
    import { RunApi } from "../api";
    import LazyLoad from "./LazyLoad.svelte";

    export let albumSlug: string;
    export let albumType: AlbumType = AlbumType.Album;

    onDestroy(() => {
        $currentPerusalStore = null;
        $currentAlbumStore = null;
    });

    $: {
        if ($currentAlbumStore && $currentAlbumStore.isPrivate && !$isLoggedInStore) {
            navigate("/");
        }
    }

    function makePseudoAlbum(photoList: Photo[], type: AlbumType): Album {
        return {
            id: -1,
            title: "",
            slug: "",
            type: type,
            isPrivate: false,
            hasMap: false,
            coverPhoto: null,
            photoGroups: [{
                id: -1,
                description: "",
                hasMap: false,
                ordering: 1,
                photos: photoList
            }],
            highestIndex: -1,
            description: "",
        };
    }

    async function getAlbum(slug: string) {
        $currentPerusalStore = null;
        if (albumType == AlbumType.Album) {
            const res = await RunApi(`/album/view/${slug}`, {
                authorize: true
            });
            if (res.success) {
                if (res.data.coverPhoto === -1) {
                    res.data.coverPhoto = null;
                }
                $currentAlbumStore = res.data;
                $currentAlbumStore.type = AlbumType.Album;
            }
            else {
                if (res.code == 404) {
                    navigate("/");
                }
                else {
                    console.error(res);
                }
            }
        }
        else if (albumType == AlbumType.Tag) {
            const res = await RunApi(`/photo/tagSet`, {
                authorize: true,
                method: "POST",
                params: {
                    tags: [slug]
                }
            });
            if (res.success) {
                $currentAlbumStore = makePseudoAlbum(res.data, AlbumType.Tag);
                $currentAlbumStore.isPrivate = !$siteData.dynamicPublic;
                $currentAlbumStore.title = `#${slug}`;
                $currentAlbumStore.slug = slug;
                $currentAlbumStore.description = "";
                $currentAlbumStore.hasMap = true;
            }
            else {
                if (res.code == 404) {
                    navigate("/");
                }
                else {
                    console.error(res);
                }
            }
        }
        else if (albumType == AlbumType.Dynamic) {
            const res = await RunApi(`/dynamic/${slug}`, {
                authorize: true
            });
            if (res.success) {
                $currentAlbumStore = makePseudoAlbum(res.data, AlbumType.Dynamic);
                $currentAlbumStore.isPrivate = !$siteData.dynamicPublic;
                if (slug == "all") {
                    $currentAlbumStore.title = "[all]";
                    $currentAlbumStore.slug = "all";
                    $currentAlbumStore.description = "";
                }
                else if (slug == "unsorted") {
                    $currentAlbumStore.title = "[unsorted]";
                    $currentAlbumStore.slug = "unsorted";
                    $currentAlbumStore.description = "";
                }
            }
            else {
                if (res.code == 404) {
                    navigate("/");
                }
                else {
                    console.error(res);
                }
            }
        }
    }
    $: getAlbum(albumSlug)

    // TODO: should this be done with a derived store instead of here?
    async function updatePerusals(album: Album) {
        if (album == null) {
            $currentPerusalStore = null;
            return;
        }
        $currentPerusalStore = {
            currentIdx: -1,
            currentPhoto: null,
            currentGroup: null,
            photoSet: [],
            nodes: [],
        };
        album.photoGroups.forEach(pg => {
            if (pg.description.length > 0) {
                $currentPerusalStore.nodes = [...$currentPerusalStore.nodes, pg];
            }
            pg.photos.forEach(p => {
                $currentPerusalStore.nodes = [...$currentPerusalStore.nodes, p];
                $currentPerusalStore.photoSet = [...$currentPerusalStore.photoSet, p];
            })
        });
    }
    $: updatePerusals($currentAlbumStore)
</script>

{#if $currentAlbumStore}
    <Router>
        <Route>
            <LazyLoad loader={"AlbumPage"}
                on:uploaded={() => getAlbum(albumSlug) }
                on:structuralChange={() => getAlbum(albumSlug)}
                on:perusalChangeNeeded={() => updatePerusals($currentAlbumStore)}
            />
        </Route>

        <Route path="/:perusalIdentifier" let:params>
            <LazyLoad loader={"PerusalPage"} perusalIdentifier={params.perusalIdentifier} />
        </Route>
    </Router>
{/if}

