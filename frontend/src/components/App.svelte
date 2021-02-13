<script lang="ts">
    import { Router, Route } from "svelte-routing";

    import { frontendStateStore } from "../stores";
    import NavBar from "./NavBar.svelte";
    import Album from "./Album.svelte";
    import AlbumList from "./AlbumList.svelte";
    import PhotoPage from "./PhotoPage.svelte";

    export let url = "";

    function setFullscreen(on: boolean) {
        if (on) {
            document.documentElement.requestFullscreen();
            $frontendStateStore.fullScreen = true;
        }
        else {
            document.exitFullscreen();
            $frontendStateStore.fullScreen = false;
        }
    }
</script>

<div class="container">
    <Router url={url}>
        <NavBar on:fullScreenOn={() => setFullscreen(true)} on:fullScreenOff={() => setFullscreen(false)} />

        <Route path="/:albumSlug/:photoID" component={PhotoPage} />
        <Route path="/:albumSlug" component={Album} />

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
