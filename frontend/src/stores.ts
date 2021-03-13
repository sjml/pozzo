import { writable, derived } from 'svelte/store';
import type { SiteConfig, Album, Photo, PerusalData } from './pozzo.type';

export const siteData = writable<SiteConfig>(
    {
        // defaults
        apiUri: `${location.origin}/api`,
        formats: [],
        siteTitle: false,
        sizes: [],
        promo: false,
        dynamicPublic: false,
        contentLicense: "",
        maxUploadBytes: 0,
        simultaneousUploads: 1,
    }
);


// UI twiddlers
export const metadataVisible = writable<boolean>(false);
export const modalUp = writable<boolean>(false);
export const fullScreen = writable<boolean>(false);
export const userStoppedUploadScroll = writable<boolean>(false);

export const currentAlbumStore = writable<Album>(null);
export const currentPerusalStore = writable<PerusalData>(null);

export const navSelection = writable<Photo[]>([]);

export const loginCredentialStore = writable<string>(localStorage.getItem("pozzoLoginJWT") || "");
loginCredentialStore.subscribe(value => {
    localStorage.setItem("pozzoLoginJWT", value);
});

export const isLoggedInStore = derived(loginCredentialStore,
    $loginCredentialStore => $loginCredentialStore.length > 0
);
