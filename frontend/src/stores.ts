import { writable, readable } from 'svelte/store';
import type { SiteConfig, Album } from './pozzo.type';

export const loginCredentialStore = writable<string>(localStorage.getItem("pozzoLoginJWT") || "");
loginCredentialStore.subscribe(value => {
    localStorage.setItem("pozzoLoginJWT", value);
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

// TODO: this could maybe be context instead...
export const userStoppedUploadScroll = writable<boolean>(false);

export const albumSelectionStore = writable<number[]>([]);
export const currentAlbumStore = writable<Album>(null);
