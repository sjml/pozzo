<script lang="ts">
    import { Router, Route, Link } from "svelte-routing";

    import NavBar from "./NavBar.svelte";
    import Album from "./Album.svelte";
    import AlbumList from "./AlbumList.svelte";
    import PhotoPage from "./PhotoPage.svelte";

    export let url = "";
</script>

<div class="container">
    <Router url={url}>
        <NavBar />
        <main>
            <Route path="album/:identifier" component={Album} />
            <Route path="album/:albumSlug/:photoID" component={PhotoPage} />
            <Route path="photo/:photoID" component={PhotoPage} />
            <Route component={AlbumList} />
        </main>
    </Router>
</div>

<style>
    .container {
        display: flex;
        flex-direction: column;
        width: 100%;
        min-height: 100vh;
    }

    main {
        flex-grow: 2;
        display: flex;
    }
</style>
