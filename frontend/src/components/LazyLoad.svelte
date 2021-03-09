<script>
    import Loadable, { register } from "svelte-loadable";

    // unfortunately have to manually pass on any dispatched events
    //   from these sub-components
    const registeredLoaders = {
        "AlbumIndex" : register({
            loader: () => import("./AlbumIndex.svelte"),
            resolve: () => "AlbumIndex"
        }),
        "AlbumPage" : register({
            loader: () => import("./AlbumPage.svelte"),
            resolve: () => "AlbumPage"
        }),
        "EditableLayout" : register({
            loader: () => import("./EditableLayout.svelte"),
            resolve: () => "EditableLayout"
        }),
        "NewAlbumPrompt" : register({
            loader: () => import("./NewAlbumPrompt.svelte"),
            resolve: () => "NewAlbumPrompt"
        }),
        "PhotoContextMenu" : register({
            loader: () => import("./PhotoContextMenu.svelte"),
            resolve: () => "PhotoContextMenu"
        }),
        "PhotoMap" : register({
            loader: () => import("./PhotoMap.svelte"),
            resolve: () => "PhotoMap"
        }),
        "PerusalPage" : register({
            loader: () => import("./PerusalPage.svelte"),
            resolve: () => "PerusalPage"
        }),
        "SetupPage" : register({
            loader: () => import("./SetupPage.svelte"),
            resolve: () => "SetupPage"
        }),
        "UploadZone" : register({
            loader: () => import("./UploadZone.svelte"),
            resolve: () => "UploadZone"
        }),
    };

    export let loader;
</script>

<Loadable loader={registeredLoaders[loader]} let:component={loadedComponent} >
    <svelte:component this={loadedComponent} {...$$restProps}
        on:structuralChange
        on:uploaded

        on:reordered

        on:done
        on:dismissed

        on:coverPhotoClicked
        on:move
        on:delete
        on:splitGroup
        on:makeNewGroup
    />
</Loadable>
