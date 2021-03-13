<script lang="ts">
    import { navigate } from "svelte-routing";

    import { AlbumType } from "../pozzo.type";
    import { isLoggedInStore, siteData } from "../stores";
    import LazyLoad from "./LazyLoad.svelte";
    import AlbumList from "./AlbumList.svelte";

    function onUploadDone(evt: CustomEvent) {
        if (evt.detail.numFiles > 0) {
            navigate("/dynamic/unsorted");
        }
    }
</script>

<!-- {#if $isLoggedInStore && !reordering} -->
{#if $isLoggedInStore}
    <LazyLoad loader={"UploadZone"} on:done={onUploadDone} />
{/if}

<div class="frontPage">
    {#if $isLoggedInStore || $siteData.dynamicPublic}
        <AlbumList typeOfAlbum={AlbumType.Dynamic} />
    {/if}
    <AlbumList typeOfAlbum={AlbumType.Album} />
</div>
