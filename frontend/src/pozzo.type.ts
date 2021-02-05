
export type SiteConfig = {
    apiUri: string,
}

export type ApiResult = {
    success: boolean,
    code: number,
    data: any,
}

export type ApiOptions = {
    authorize?: boolean,
    params?: object,
    method?: string,
}

export type Photo = {
    id: number,
    title: string,
    hash: string,
    width: number,
    height: number,
    aspect: number,
    size: number,
    tinyJPEG: string,
}

export type Album = {
    title: string,
    description: string,
    photos: Photo[]
}
