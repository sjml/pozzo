<script lang="ts">
    import { Router, Route } from "svelte-routing";

    import Login from "./Login.svelte";
    import UploadZone from "./UploadZone.svelte";
    import Album from "./Album.svelte";
    import AlbumList from "./AlbumList.svelte";

    export let url = "";
</script>

<main>
    <h1>Pozzo</h1>
    <Login />
    <UploadZone />

    <Router url={url}>
        <Route path="album/:identifier" component={Album} />
        <Route component={AlbumList} />
    </Router>

</main>

<style>
    main {
        padding-bottom: 30px;
    }

    h1 {
        padding: 20px;
        font-size: 4em;
    }
</style>