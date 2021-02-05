import { get } from "svelte/store";

import { siteData, loginCredentialStore } from "./stores";
import type { ApiResult, ApiOptions } from "./pozzo.type";


export async function RunApi(url: string, opts?: ApiOptions): Promise<ApiResult> {
    const fetchParams: RequestInit = {};
    opts = opts ?? {};
    fetchParams.method = opts.method ?? "GET";
    if (opts.params) {
        fetchParams.body = JSON.stringify(opts.params);
    }
    if (opts.authorize) {
        fetchParams.headers = {
            Authorization: `Bearer ${get(loginCredentialStore)}`,
        }
    }

    const res = await fetch(
        `${get(siteData).apiUri}${url}`,
        fetchParams
    );
    try {
        if (res.ok) {
            const retrieved = await res.json();
            return {
                success: true,
                code: res.status,
                data: retrieved
            };
        }
        else {
            const error = await res.json();
            return {
                success: false,
                code: res.status,
                data: error
            }
        }
    }
    catch (error) {
        return {
            success: false,
            code: 500,
            data: {error: error}
        }
    }
}

export async function fileUpload(file: File) {

}
