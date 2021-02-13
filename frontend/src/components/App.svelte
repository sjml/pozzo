<script lang="ts">
    import { onMount } from "svelte";
    import { Router, Route, navigate } from "svelte-routing";

    import { frontendStateStore, siteData } from "../stores";
    import { RunApi } from "../api";
    import SetupPage from "./SetupPage.svelte";
    import NavBar from "./NavBar.svelte";
    import Album from "./Album.svelte";
    import AlbumList from "./AlbumList.svelte";
    import PhotoPage from "./PhotoPage.svelte";

    export let url = "";

    onMount(async () => {
        const res = await RunApi("/info");
        if (res.success) {
            $siteData.siteTitle = res.data.siteTitle;
            if ($siteData.siteTitle == false) {
                navigate("/setup");
            }
            else {
                $siteData.formats = res.data.formats;
                $siteData.sizes = res.data.sizes;
            }
        }
        else {
            console.error(res);
        }
    });

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

        <Route path="/setup" component={SetupPage} />
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
