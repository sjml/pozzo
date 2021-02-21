import { get } from "svelte/store";

import { siteData, loginCredentialStore } from "./stores";
import type { ApiResult, ApiOptions, FileUploadStatus } from "./pozzo.type";


export async function RunApi(url: string, opts?: ApiOptions): Promise<ApiResult> {
    const fetchParams: RequestInit = {};
    opts = opts ?? {};
    fetchParams.method = opts.method ?? "GET";
    fetchParams.cache = "no-cache";
    if (opts.params) {
        fetchParams.body = JSON.stringify(opts.params);
    }
    if (opts.authorize && get(loginCredentialStore).length > 0) {
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
            code: 400,
            data: {error: error, res: res}
        }
    }
}

// doin' it old-school with XHR so we can get progress reports
export async function UploadFile(fileStatus: FileUploadStatus, progressCallback: Function = null, finishedUploadCallback: Function = null): Promise<ApiResult> {
    return new Promise((resolve, reject) => {
        if (get(loginCredentialStore).length == 0) {
            resolve({
                success: false,
                code: 403,
                data: {error: "Unauthorized"}
            });
        }

        const file = fileStatus.file;
        const url = `${get(siteData).apiUri}/upload`;
        const xhr = new XMLHttpRequest();
        const fd = new FormData();

        xhr.upload.onprogress = (ev) => {
            if (ev.lengthComputable) {
                const progress = ev.loaded / ev.total;
                if (progressCallback) {
                    progressCallback(progress);
                }
                if (finishedUploadCallback && progress >= 1.0) {
                    finishedUploadCallback(true);
                }
            }
        };

        xhr.upload.onload = (_) => {
            if (finishedUploadCallback) {
                finishedUploadCallback(true);
            }
        };

        xhr.upload.onerror = (_) => {
            if (finishedUploadCallback) {
                finishedUploadCallback(false);
            }
        };

        xhr.upload.onabort = (_) => {
            if (finishedUploadCallback) {
                finishedUploadCallback(false);
            }
        };

        xhr.onload = (_) => {
            if (xhr.status >= 200 && xhr.status < 300) {
                resolve({
                    success: true,
                    code: xhr.status,
                    data: JSON.parse(xhr.responseText)
                });
            }
            else {
                // not rejecting so data pack always
                //   makes its way back to caller
                resolve({
                    success: false,
                    code: xhr.status,
                    data: JSON.parse(xhr.responseText)
                });
            }
        }

        xhr.onerror = (_) => {
            resolve({
                success: false,
                code: 500,
                data: {error: xhr.responseText}
            });
        }

        xhr.open("POST", url, true);
        xhr.setRequestHeader("Authorization", `Bearer ${get(loginCredentialStore)}`);

        const params = {
            order: fileStatus.index
        }
        if (fileStatus.targetAlbum != null) {
            params["albumID"] = fileStatus.targetAlbum;
        }
        fd.append("data", JSON.stringify(params));

        fd.append("mediaUp", file);
        xhr.send(fd);
    });
}
