<script lang="ts">
    import { onMount } from "svelte";

    import type { Photo } from "../pozzo.type";
    import { GetImgPath } from "../util";

    export let photoWhichIsReallyAVideo: Photo;

    let vidSrc: string;
    let posterSrc: string;

    onMount(() => {
        posterSrc = GetImgPath("large", photoWhichIsReallyAVideo.hash, photoWhichIsReallyAVideo.uniq);

        const ext = photoWhichIsReallyAVideo.originalFilename.split(".").pop();
        vidSrc = GetImgPath("orig", photoWhichIsReallyAVideo.hash, photoWhichIsReallyAVideo.uniq, ext);
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
