<script lang="ts">
    import { Router, Route, Link } from "svelte-routing";

    import Login from "./Login.svelte";
    import UploadZone from "./UploadZone.svelte";
    import Album from "./Album.svelte";
    import AlbumList from "./AlbumList.svelte";

    export let url = "";
</script>

<main>
    <Router url={url}>
        <Link to={"/"}>
            <h1 class="homelink">Pozzo</h1>
        </Link>
        <Login />
        <UploadZone />

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
