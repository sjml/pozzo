import { writable, readable } from 'svelte/store';
import type { FrontendState, SiteConfig, Album } from './pozzo.type';

// glorified global variables? :-/
export const frontendStateStore = writable<FrontendState>({
    photoToolsVisible: false,
    backLinkText: "",
    backLink: "",
    isMetadataOn: true,
    currentAlbum: null,
    userStoppedUploadScroll: false,
});


export const isLoggedInStore = writable<boolean>(false);

export const loginCredentialStore = writable<string>(localStorage.getItem("pozzoLoginJWT") || "");
loginCredentialStore.subscribe(value => {
    localStorage.setItem("pozzoLoginJWT", value);
    if (value.length == 0) {
        isLoggedInStore.set(false);
    }
    else {
        isLoggedInStore.set(true);
    }
});


export const siteData = readable<SiteConfig>(
    {
        // defaults
        apiUri: `${location.origin}/api`
    },
    (set) => {
        // do the fetch for remote config here and call
        //   set() with the completed SiteConfig object
    }
);
