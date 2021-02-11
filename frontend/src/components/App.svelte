<script lang="ts">
    import { Router, Route, Link } from "svelte-routing";

    import NavBar from "./NavBar.svelte";
    import Album from "./Album.svelte";
    import AlbumList from "./AlbumList.svelte";
    import PhotoPage from "./PhotoPage.svelte";
    import { frontendStateStore } from "../stores";

    export let url = "";

    let mainContainer: HTMLDivElement;

    function setFullscreen(on: boolean) {
        if (on) {
            mainContainer.requestFullscreen();
            $frontendStateStore.fullScreen = true;
        }
        else {
            document.exitFullscreen();
            $frontendStateStore.fullScreen = false;
        }
    }
</script>

<div class="container" bind:this={mainContainer}>
    <Router url={url}>
        <NavBar on:fullScreenOn={() => setFullscreen(true)} on:fullScreenOff={() => setFullscreen(false)} />

        <Route path="album/:identifier" component={Album} />
        <Route path="album/:albumSlug/:photoID" component={PhotoPage} />
        <Route path="photo/:photoID" component={PhotoPage} />

        <!-- default page shows list of albums -->
        <Route component={AlbumList} />
    </Router>
</div>

<style>
    .container {
        height: 100vh;

        display: grid;
        grid-template-columns: 1fr auto;
        grid-template-rows: auto 1fr;
    }
</style>
