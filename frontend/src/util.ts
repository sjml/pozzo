

export function HumanBytes(byteCount: number): string {
    const order = Math.floor( Math.log(byteCount) / Math.log(1024) );

    const calced = byteCount / Math.pow(1024, order);
    const numDecimals = (order <= 1) ? 0 : 2;
    const pretty = calced.toFixed(numDecimals);
    return `${pretty} ${["B", "kB", "MB", "GB", "TB"][order]}`;
}

// matching the backend logic from image.php's getImagePath function
export function GetImgPath(size:string, hash: string, uniq: string) {
    const dirs = hash.match(/.{1,2}/g)
                    .slice(0,3)
                    .map((d) => {if (d == "ad") return "a_"; else return d; });
                    // "ad" is special-case censored to avoid triggering ad-blockers

    return `/img/${dirs.join("/")}/${hash}_${uniq}_${size}.jpg`;
}
