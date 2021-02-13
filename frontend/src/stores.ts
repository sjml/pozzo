import { writable, readable } from 'svelte/store';
import type { FrontendState, SiteConfig } from './pozzo.type';

export const siteData = writable<SiteConfig>(
    {
        // defaults
        apiUri: `${location.origin}/api`,
        formats: [],
        siteTitle: false,
        sizes: []
    }
);


// glorified global variables? :-/
export const frontendStateStore = writable<FrontendState>({
    fullScreen: false,
    photoToolsVisible: false,
    backLinkText: "",
    backLink: "",
    nextPhotoLink: "",
    prevPhotoLink: "",
    isMetadataOn: false,
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

