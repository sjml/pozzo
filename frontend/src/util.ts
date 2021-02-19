

export function HumanBytes(byteCount: number): string {
    const order = Math.floor( Math.log(byteCount) / Math.log(1024) );

    const calced = byteCount / Math.pow(1024, order);
    const numDecimals = (order <= 1) ? 0 : 2;
    const pretty = calced.toFixed(numDecimals);
    return `${pretty} ${["B", "kB", "MB", "GB", "TB"][order]}`;
}

export function TimestampToDateString(timestamp: number): string {
    const date = new Date(timestamp * 1000); // JavaScript dates are in milliseconds

    // JavaScript date formatting is annoying
    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, "0");
    const day = date.getDate().toString().padStart(2, "0");
    const hours = date.getHours().toString().padStart(2, "0");
    const minutes = date.getMinutes().toString().padStart(2, "0");
    const seconds = date.getSeconds().toString().padStart(2, "0");

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

// matching the backend logic from image.php's getImagePath function
export function GetImgPath(size:string, hash: string, uniq: string): string {
    const dirs = hash.match(/.{1,2}/g)
                    .slice(0,3)
                    .map((d) => {if (d == "ad") return "a_"; else return d; });
                    // "ad" is special-case censored to avoid triggering ad-blockers

    return `/photos/${dirs.join("/")}/${hash}_${uniq}_${size}.jpg`;
}

export function IsMetaKeyDownForEvent(evt: (KeyboardEvent|MouseEvent)) {
    if (window.navigator.platform.startsWith("Mac")) {
        return evt.metaKey;
    }
    else {
        return evt.ctrlKey;
    }
}
