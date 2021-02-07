<script lang="ts">
    import { Router, Route, Link } from "svelte-routing";

    import Login from "./Login.svelte";
    import UploadZone from "./UploadZone.svelte";
    import Album from "./Album.svelte";
    import AlbumList from "./AlbumList.svelte";
    import PhotoPage from "./PhotoPage.svelte";

    export let url = "";
</script>

<Router url={url}>
    <nav>
        <Link to={"/"}>
            <h1>Pozzo</h1>
        </Link>
        <Login />
    </nav>
    <main>
        <UploadZone />

        <Route path="album/:identifier" component={Album} />
        <!-- <Route path="album/:albumIdentifier/:photoID" component={PhotoPage} /> -->
        <Route path="photo/:photoID" component={PhotoPage} />
        <Route component={AlbumList} />
    </main>
</Router>

<style>
    nav {
        position: absolute;
        top: 0;
        width: 100%;
        background-color: rgb(32, 32, 32);
        font-size: large;
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 300;
    }

    nav h1 {
        padding: 5px 10px;
        font-weight: bolder;
        font-size: 1.4em;
        margin: 0;
    }
</style>
