import { writable, derived } from 'svelte/store';
import type { FrontendState, SiteConfig, Album, PhotoStub } from './pozzo.type';

export const siteData = writable<SiteConfig>(
    {
        // defaults
        apiUri: `${location.origin}/api`,
        formats: [],
        siteTitle: false,
        sizes: [],
        promo: false,
        maxUploadBytes: 0,
    }
);


// glorified global variables? :-/
export const frontendStateStore = writable<FrontendState>({
    fullScreen: false,
    photoToolsVisible: false,
    nextPhotoLink: "",
    prevPhotoLink: "",
    isMetadataOn: false,
    userStoppedUploadScroll: false,
});

export const currentAlbumStore = writable<Album>(null);

export const navSelection = writable<PhotoStub[]>([]);

export const loginCredentialStore = writable<string>(localStorage.getItem("pozzoLoginJWT") || "");
loginCredentialStore.subscribe(value => {
    localStorage.setItem("pozzoLoginJWT", value);
});

export const isLoggedInStore = derived(loginCredentialStore,
    $loginCredentialStore => $loginCredentialStore.length > 0
);
