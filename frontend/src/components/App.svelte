<script lang="ts">
    import { Router, Route, Link } from "svelte-routing";

    import { navBarBackLinkStore, navBarBackTextStore } from "../stores";
    import Login from "./Login.svelte";
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
            {#if $navBarBackLinkStore.length > 0 && $navBarBackTextStore.length > 0}
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <!-- yeah, this is lazy -->
                <Link to={$navBarBackLinkStore}>{$navBarBackTextStore}</Link>
            {/if}
        <div class="endcap">
            <Login />
        </div>
    </nav>
    <main>
        <Route path="album/:identifier" component={Album} />
        <Route path="album/:albumSlug/:photoID" component={PhotoPage} />
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
        flex-direction: row;
        align-items: center;
        justify-content: flex-start;
        z-index: 300;
    }

    nav h1 {
        padding: 5px 10px;
        font-weight: bolder;
        font-size: 1.4em;
        margin: 0;
    }

    nav svg {
        width: 30px;
    }

    nav .endcap {
        padding-top: 5px;
        flex-grow: 2;
    }
</style>
