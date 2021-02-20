<script lang="ts">
    import { onMount } from "svelte";

    import { GetImgPath } from "../util";
    import { currentPhotoStore } from "../stores";

    let vidSrc: string;
    let posterSrc: string;

    onMount(() => {
        posterSrc = GetImgPath("large", $currentPhotoStore.hash, $currentPhotoStore.uniq);

        const ext = $currentPhotoStore.originalFilename.split(".").pop();
        vidSrc = GetImgPath("orig", $currentPhotoStore.hash, $currentPhotoStore.uniq, ext);
    });

</script>

<!-- svelte-ignore a11y-media-has-caption -->
<video class="main" src={vidSrc} controls poster={posterSrc} />

<style>
    video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    video.main {
        z-index: 20;

        object-fit: contain;
    }
</style>
