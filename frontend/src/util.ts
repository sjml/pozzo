

export function HumanBytes(byteCount: number): string {
    const order = Math.floor( Math.log(byteCount) / Math.log(1024) );

    const calced = byteCount / Math.pow(1024, order);
    const numDecimals = (order <= 1) ? 0 : 2;
    const pretty = calced.toFixed(numDecimals);
    return `${pretty} ${["B", "kB", "MB", "GB", "TB"][order]}`;
}

export function GetImgPath(size:string, hash: string, uniq: string) {
    const dirs = hash.match(/.{1,2}/g).slice(0,3);
    return `/img/${dirs.join("/")}/${hash}_${uniq}_${size}.jpg`;
}
