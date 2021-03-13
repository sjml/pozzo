<script lang="ts">
    import { navigate } from "svelte-routing";

    import { AlbumType } from "../pozzo.type";
    import { isLoggedInStore } from "../stores";
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
    <AlbumList typeOfAlbum={AlbumType.Dynamic} />
    <AlbumList typeOfAlbum={AlbumType.Album} />
</div>
