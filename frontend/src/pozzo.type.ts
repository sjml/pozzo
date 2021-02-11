
export type SiteConfig = {
    apiUri: string,
}

export type FrontendState = {
    fullScreen: boolean,
    photoToolsVisible: boolean,
    backLinkText: string,
    backLink: string,
    nextPhotoLink: string,
    prevPhotoLink: string,
    isMetadataOn: boolean,
    currentAlbum?: Album,
    userStoppedUploadScroll: boolean,
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

export type FileUploadStatus = {
    file: File,
    status: number,
    index: number,
    targetAlbum?: number,
    startUploadCallback?: Function,
}

export type Photo = {
    id: number,
    title: string,
    hash: string,
    uniq: string,
    width: number,
    height: number,
    aspect: number,
    size: number,
    uploadTimeStamp: number,
    ordering?: number,
    latitude?: number,
    longitude?: number,
    tinyJPEG?: string,
}

export type Album = {
    id: number,
    title: string,
    description: string,
    slug: string,
    isPrivate: boolean,
    showMap: boolean,
    highestIndex: number,
    photos: Photo[]
}
